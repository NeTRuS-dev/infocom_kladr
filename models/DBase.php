<?php


namespace app\models;


class DBase
{
    private ?DBaseEntity $base_connection = null;
    const CONTAINS = 0;
    const STARTS_WITH = 1;
    const EQUALS = 2;
    const IN_ARRAY = 3;

    public function __construct(string $file)
    {
        $this->load($file);
    }

    /**
     * @param SearchParameter[] $search_params
     * @return int[]
     */
    public function search(array $search_params): array
    {
        $result = [];
        foreach ($search_params as $param) {
            if ($param->mode === self::IN_ARRAY) {
                //TODO implement in array check
            } else {
                $result = $this->base_connection->selectIDsByCondition($param->header_name, $param->string_to_search, $param->mode, $result);

            }
        }
        return $result;
    }

    public function getRowsByIds(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $result[] = $this->base_connection->getRecord($id);
        }
        return $result;
    }

    private function load(string $file)
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

    public function __destruct()
    {
        $this->unload();
    }

}