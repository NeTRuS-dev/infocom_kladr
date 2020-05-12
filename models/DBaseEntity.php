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
    private int $database_size;
    private array $headers;
    private array $cached_data;
    private array $search_cache;

    /**
     * DBaseEntity constructor.
     * @param resource $resource
     */
    public function __construct($resource)
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
     * @param int $start_index
     * @param int[]|null $searching_array
     * @return int[]|null
     */
    public function selectIDsByCondition($checker, $start_index = 1, $searching_array = null)
    {
        $results = [];
        $size = $this->database_size;
        $passed_arr_is_empty = empty($searching_array);
        if (!$passed_arr_is_empty) {
            $size = count($searching_array);
            $start_index = 0;
        }
        for ($i = $start_index; $i < $size; $i++) {
            $index = ($passed_arr_is_empty ? $i : $searching_array[$i]);
            $record = $this->getRecord($index);
            if ($checker->check($record)) {
                $results[] = $index;
            }
        }
        return $results;
    }

    public function __destruct()
    {
        dbase_close($this->resource);
    }
}