<?php


namespace app\models;


class DBase
{
    public ?DBaseEntity $base_connection = null;
    const CONTAINS = 0;
    const STARTS_WITH = 1;
    const EQUALS = 2;

    public function __construct(string $file)
    {
        $this->load($file);
    }

    public function load(string $file)
    {
        $resource = dbase_open($file, 0);
        $this->base_connection = new DBaseEntity($resource, pathinfo($file, PATHINFO_FILENAME));
    }

    private function unload()
    {
        if ($this->base_connection) {
            unset($this->base_connection);
        }
    }

    public function search(array $search_params)
    {

    }

    public function __destruct()
    {
        $this->unload();
    }

}