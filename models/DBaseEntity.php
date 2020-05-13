<?php


namespace app\models;


use app\models\checkers\AbstractChecker;
use Yii;
use yii\caching\CacheInterface;

class DBaseEntity
{
    /**
     * @var resource $resource
     */
    private $resource;
    private string $cache_prefix;
    private int $database_size;
    private array $headers;
    private array $cached_data;
    private array $search_cache;

    /**
     * DBaseEntity constructor.
     * @param resource $resource
     * @param $cache_prefix
     */
    public function __construct($resource, $cache_prefix)
    {
        if (!is_resource($resource)) {
            return;
        }
        $this->headers = [];
        $this->cached_data = [];
        $this->search_cache = [];
        $this->resource = $resource;
        $this->setUpHeaders();
        $this->database_size = dbase_numrecords($this->resource);
        $this->cache_prefix = $cache_prefix;
    }

    /**
     * @param int $header_number
     * @return string
     */
    public function getHeader($header_number)
    {
        if ($header_number <= count($this->headers))
            return $this->headers[$header_number];
        else
            return null;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    private function setUpHeaders()
    {
        $this->headers = mb_convert_encoding(dbase_get_header_info($this->resource), 'UTF-8', 'CP866');
    }

    /**
     * @param int $record_number
     * @return array|null
     */
    public function getRecord($record_number)
    {
        //TODO chunk cache
        if ($record_number > $this->database_size) {
            return null;
        } else {
            if (array_key_exists($record_number, $this->cached_data)) {
                return $this->cached_data[$record_number];
            } else {
                /** @var array $record */
                $record = array_map('rtrim', mb_convert_encoding(dbase_get_record_with_names($this->resource, $record_number), 'UTF-8', 'CP866'));
                $this->cached_data[$record_number] = $record;
                return $record;
            }
        }
    }

    /**
     * @param string $header_text
     * @return null|int
     */
    public function getHeaderNumber(string $header_text): ?int
    {
        foreach ($this->headers as $index => $header) {
            if ($header['name'] === $header_text) {
                return $index;
            }
        }
        return null;
    }

    public function getDatabaseSize(): int
    {
        return $this->database_size;
    }

    /**
     * @param AbstractChecker $checker
     * @param int $start_actual_search_record_index
     * @param int[]|null $searching_array
     * @return int[]|null
     */
    public function selectIDsByCondition($checker, $start_actual_search_record_index, $searching_array = null)
    {
        $results = [];
        $start_index = 1;
        $size = $this->database_size;
        $passed_arr_is_empty = empty($searching_array);
        if (!$passed_arr_is_empty) {
            $size = count($searching_array);
            $start_index = 0;
        }
        for ($i = $start_index; $i < $size; $i++) {
            $index = ($passed_arr_is_empty ? $i : $searching_array[$i]);
            if ($index < $start_actual_search_record_index) {
                continue;
            }
            $record = $this->getRecord($index);
            if ($checker->check($record)) {
                $results[] = $index;
            } else if ($checker->searching_is_pointless) {
                break;
            }
        }
        return $results;
    }

    private function isNumberInRange(int $num, int $min, int $max): bool
    {
        return filter_var(
                $num,
                FILTER_VALIDATE_INT,
                array(
                    'options' => array(
                        'min_range' => $min,
                        'max_range' => $max
                    )
                )
            ) !== false;
    }

    private function findNumberRange(int $num, int $range): ?string
    {
        if ($num < 1) {
            return null;
        }
        $i = 1;
        $max_num = $range;

        while (true) {
            if ($this->isNumberInRange($num, $i, $max_num)) {
                break;
            }
            $i = $max_num + 1;
            $max_num += $range;
        }
        return "${i}-${max_num}";
    }

    public function __destruct()
    {
        dbase_close($this->resource);
    }
}