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
    private string $cached_data_name;
    private string $search_cache_name;
    private CacheInterface $cache_storage;

    //TODO add memcache
    public function __construct($resource, string $cache_prefix)
    {
        if (!is_resource($resource)) {
            return;
        }
        $this->headers = [];
        $this->cached_data_name = $cache_prefix . '_cached_data';
        $this->search_cache_name = $cache_prefix . '_search_cache';
        $this->cache_storage = Yii::$app->cache;
        $this->resource = $resource;
        $this->setUpHeaders();
        $this->database_size = dbase_numrecords($this->resource);
    }

    /**
     * @param $header_number
     * @return array|null
     */
    public function getHeader($header_number): ?array
    {
        if ($header_number <= count($this->headers))
            return $this->headers[$header_number];
        else
            return null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    private function setUpHeaders(): void
    {
        $this->headers = mb_convert_encoding(dbase_get_header_info($this->resource), 'UTF-8', 'CP866');
    }

    /**
     * @param int $record_number
     * @return array|null
     */
    public function getRecord(int $record_number): ?array
    {
        if ($record_number > $this->database_size) {
            return null;
        } else {
            if ($this->cache_storage->exists("{$this->cached_data_name}.{$record_number}")) {
                return $this->cache_storage->get("{$this->cached_data_name}.{$record_number}");
            } else {
                /** @var array $record */
                $record = array_map('rtrim', mb_convert_encoding(dbase_get_record_with_names($this->resource, $record_number), 'UTF-8', 'CP866'));
                $this->cache_storage->set("{$this->cached_data_name}.{$record_number}", $record);
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
     * @param array|null $searching_array
     * @return int[]|null
     */
    public function selectIDsByCondition(string $header_name, string $searching_string, int $searching_mode = DBase::CONTAINS, ?array $searching_array = null): ?array
    {
        if ($this->cache_storage->exists("{$this->search_cache_name}.{$header_name}.{$searching_string}")) {
            return $this->cache_storage->get("{$this->search_cache_name}.{$header_name}.{$searching_string}");
        } else {
            $results = [];
            $checking_function = '';
            switch ($searching_mode) {
                case DBase::CONTAINS:
                    $checking_function = 'in_string';
                    break;
                case DBase::STARTS_WITH:
                    $checking_function = 'starts_with';
                    break;
                case DBase::EQUALS:
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
                if (call_user_func([$this, $checking_function], trim($record[$header_name]), $searching_string)) {
                    $results[] = $index;
                }
            }
            if ($passed_arr_is_empty && !empty($results)) {
                $this->cache_storage->set("{$this->search_cache_name}.{$header_name}.{$searching_string}", $results);
            }
            return $results;
        }
    }

    /**
     * @param string $target
     * @param string $searching_string
     * @return bool
     */
    private function starts_with(string $target, string $searching_string): bool
    {
        $length = strlen($searching_string);
        return (substr($target, 0, $length) === $searching_string);
    }

    /**
     * @param string $target
     * @param string $searching_string
     * @return bool
     */
    private function in_string(string $target, string $searching_string): bool
    {
        return (strpos($target, $searching_string) !== false);
    }

    /**
     * @param string $target
     * @param string $searching_string
     * @return bool
     */
    private function strings_are_equal(string $target, string $searching_string): bool
    {
        return ($target === $searching_string);
    }

    public function __destruct()
    {
        dbase_close($this->resource);
    }
}