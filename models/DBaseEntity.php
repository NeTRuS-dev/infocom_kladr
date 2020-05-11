<?php


namespace app\models;


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
    private bool $enable_type_caching;

    /**
     * DBaseEntity constructor.
     * @param resource $resource
     * @param bool $enable_type_caching
     */
    public function __construct($resource, bool $enable_type_caching)
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
        $this->enable_type_caching = $enable_type_caching;
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
            if (mb_convert_encoding($header['name'], 'UTF-8', 'CP866') === $header_text) {
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
     * @param string $header_name
     * @param string $searching_string
     * @param int $searching_mode
     * @param int[]|null $searching_array
     * @return int[]|null
     */
    public function selectIDsByCondition($header_name, $searching_string, $searching_mode, $searching_array = null)
    {
        $results = [];
        $checking_function = '';
        switch ($searching_mode) {
            case DBase::STR_CONTAINS:
                $checking_function = 'in_string';
                break;
            case DBase::STR_STARTS_WITH:
                $checking_function = 'starts_with';
                break;
            case DBase::STR_EQUALS:
                $checking_function = 'strings_are_equal';
                break;
        }
        $size = $this->database_size;
        $start_index = 1;
        $passed_arr_is_empty = empty($searching_array);
        if (!$passed_arr_is_empty) {
            $size = count($searching_array);
            $start_index = 0;
        }
        for ($i = $start_index; $i < $size; $i++) {
            $index = ($passed_arr_is_empty ? $i : $searching_array[$i]);
            $record = $this->getRecord($index);
            if (call_user_func([$this, $checking_function], $record[$header_name], $searching_string)) {
                $results[] = $index;
            }
        }
        return $results;
    }

    /**
     * @param string $header_in_db_name
     * @param string $header_in_values
     * @param array $values_to_compare
     * @param int $comparing_mode
     * @param int[]|null $searching_array
     * @return int[]
     */
    public function SelectIDsWithValueInArray($header_in_db_name, $header_in_values, $values_to_compare, $comparing_mode, $searching_array = null)
    {
        $results = [];
        $size = $this->database_size;
        $start_index = 1;
        $passed_arr_is_empty = empty($searching_array);
        if (!$passed_arr_is_empty) {
            $size = count($searching_array);
            $start_index = 0;
        }
        for ($i = $start_index; $i < $size; $i++) {
            $index = ($passed_arr_is_empty ? $i : $searching_array[$i]);
            $record = $this->getRecord($index);
            if ($this->belongs_to_array($record[$header_in_db_name], $header_in_values, $values_to_compare, $comparing_mode)) {
                $results[] = $index;
            }
        }
        return $results;
    }

    /**
     * @param string $target
     * @param string $header_in_values
     * @param array $searching_assoc_array
     * @param int $checking_type
     * @return bool
     */
    private function belongs_to_array($target, $header_in_values, $searching_assoc_array, $checking_type)
    {
        $checking_function = '';
        switch ($checking_type) {
            case DBase::IN_ARRAY_CONTAINS:
                $checking_function = 'in_string';
                break;
            case DBase::IN_ARRAY_STARTS_WITH:
                $checking_function = 'starts_with';
                break;
            case DBase::IN_ARRAY_EQUALS:
                $checking_function = 'strings_are_equal';
                break;
        }
        foreach ($searching_assoc_array as $item) {
            if (call_user_func([$this, $checking_function], $target, $item[$header_in_values])) {
                return true;
            }
        }
        return false;
    }


    public function __destruct()
    {
        dbase_close($this->resource);
    }
}