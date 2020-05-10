<?php


namespace app\models;


class DBaseEntity
{
    /**
     * @var resource $resource
     */
    private $resource;
    private int $database_size;
    private array $headers;
    private array $cached_data;
    private array $search_cache = [];

    //TODO add memcache
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
            if (array_key_exists($record_number, $this->cached_data))
                return $this->cached_data[$record_number];
            else {
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
     * @param int|null $results_limit
     * @return array|null
     */
    public function search(string $header_name, string $searching_string, ?int $results_limit = null): ?array
    {
        if ($this->search_cache[$header_name][$searching_string]) {
            return $this->search_cache[$header_name][$searching_string];
        } else {
            $results = [];
            for ($i = 1; $i < $this->database_size; $i++) {
                $record = $this->getRecord($i);
                if (strpos(trim($record[$header_name]), $searching_string) !== false) {
                    $results[] = $i;

                    if (($results_limit) && (count($results) === $results_limit))
                        break;
                }
            }
            if (!empty($results)) {
                $this->search_cache[$header_name][$searching_string] = $results;
            }
            return $results;
        }
    }

    public function __destruct()
    {
        dbase_close($this->resource);
    }
}