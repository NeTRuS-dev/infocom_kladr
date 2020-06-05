<?php


namespace app\models;


use app\models\checkers\AbstractChecker;
use Exception;
use Yii;
use yii\caching\CacheInterface;

class DBaseEntity
{
    /**
     * @var resource $resource
     */
    private $resource;
    private int $database_size;
    private array $headers;

    public string $cache_prefix;
    private array $local_cached_data;
    private string $current_chunk;
    private bool $chunk_in_cache_is_correct;

    private int $chunk_size;

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
        $this->resource = $resource;
        $this->setUpHeaders();
        $this->database_size = dbase_numrecords($this->resource);

        $this->current_chunk = '';
        $this->local_cached_data = [];
        $this->cache_prefix = $cache_prefix;
        $this->chunk_in_cache_is_correct = true;
        $this->chunk_size = Yii::$app->params['chunk_size'];
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
        if ($record_number > $this->database_size) {
            return null;
        } else {
//            try {
//                $range_of_current_number = $this->findNumberRange($record_number);
//            } catch (Exception $e) {
//                return null;
//            }
//            if ($this->current_chunk !== $range_of_current_number) {
//                $this->current_chunk = $range_of_current_number;
//                $this->local_cached_data = [];
//                $this->chunk_in_cache_is_correct = false;
//            }
//            if (array_key_exists($record_number, $this->local_cached_data)) {
//                return $this->local_cached_data[$record_number];
//            } else {
//            $record = array_map('rtrim', mb_convert_encoding(dbase_get_record_with_names($this->resource, $record_number), 'UTF-8', 'CP866'));
//            $this->local_cached_data[$record_number] = $record;
//            $this->chunk_in_cache_is_correct = false;
//            return $record;
            return array_map('rtrim', mb_convert_encoding(dbase_get_record_with_names($this->resource, $record_number), 'UTF-8', 'CP866'));
//            }
        }
    }


    /**
     * @param string $header_text
     * @return null|int
     */
    public function getHeaderNumber($header_text)
    {
        foreach ($this->headers as $index => $header) {
            if ($header['name'] === $header_text) {
                return $index;
            }
        }
        return null;
    }

    /**
     * @return int
     */
    public function getDatabaseSize()
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
        for ($i = $start_index; $i < $size; ++$i) {
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
        $this->checkChunkCachingNeed();
        return $results;
    }

    /**
     * @param int $num
     * @param int $min
     * @param int $max
     * @return bool
     */
    private function isNumberInRange($num, $min, $max)
    {
        return $min <= $num && $num <= $max;
    }

    /**
     * @param int $num
     * @return string
     * @throws Exception
     */
    private function findNumberRange($num)
    {
        if ($num < 1) {
            throw new Exception('Number was less than 1');
        }
        $i = 1;
        $max_num = $this->chunk_size;

        while (true) {
            if ($this->isNumberInRange($num, $i, $max_num)) {
                break;
            }
            $i = $max_num + 1;
            $max_num += $this->chunk_size;
        }
        return "${i}-${max_num}";
    }

    public function __destruct()
    {
        dbase_close($this->resource);
    }
}