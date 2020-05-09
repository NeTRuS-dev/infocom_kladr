<?php


namespace app\models;


class DBase
{
    public ?DBaseEntity $base_connection = null;
    private array $search_cache = []; // Search optimizer

    public function __construct(string $file)
    {
        $this->load($file);
    }

    public function load(string $file)
    {
        $resource = dbase_open($file, 0);
        $this->base_connection = new DBaseEntity($resource);
    }

    private function unload()
    {
        if ($this->base_connection) {
            unset($this->base_connection);
        }
    }

    /**
     * @param string $header_name
     * @param string $searching_string
     * @param int|null $results_limit
     * @return array|null
     */
    public function search(string $header_name, string $searching_string, ?int $results_limit = null): ?array
    {
        if (is_null($this->base_connection)) {
            return null;
        }
        //TODO probably caching is useless here
        if ($this->search_cache[$header_name][$searching_string]) {
            return $this->search_cache[$header_name][$searching_string];
        } else {
            $size = $this->base_connection->getDatabaseSize();
//        $header_number = $this->base_connection->getHeaderNumber($header_name);
//        if (!is_null($header_number)) {
            $results = [];
            for ($i = 1; $i < $size; $i++) {
                $record = $this->base_connection->getRecord($i);
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
//        } else
//            return null;}
    }

    public function __destruct()
    {
        $this->unload();
    }

}