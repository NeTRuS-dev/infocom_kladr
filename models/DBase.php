<?php


namespace app\models;


use Yii;

class DBase
{
    //jesus kill the guy named those dbases
    private ?DBaseEntity $base_connection = null;

    public function __construct(string $filename)
    {
        $this->load($filename);
    }

    /**
     * @param SearchParameter[] $search_params
     * @return int[]
     */
    public function execQuery($search_params)
    {
        $result = [];
        foreach ($search_params as $param) {
            $result = $this->base_connection->selectIDsByCondition($param->checker, $param->start_index, (empty($param->array_for_search) ? $result : $param->array_for_search));
        }
        return $result;
    }

    /**
     * @param int[] $ids
     * @return array
     */
    public function getRowsByIds($ids)
    {
        $result = [];
        foreach ($ids as $id) {
            $result[] = $this->getItemById($id);
        }
        $this->base_connection->checkChunkCachingNeed();
        return $result;
    }

    /**
     * @param int $id
     * @return array|null
     */
    private function getItemById($id)
    {
        return $this->base_connection->getRecord($id);
    }

    /**
     * @return string
     */
    public function getFilePrefix()
    {
        return $this->base_connection->cache_prefix;
    }

    /**
     * @param string $filename
     */
    private function load($filename)
    {
        $file = $this->makeFullPath($filename);
        $resource = dbase_open($file, 0);
        $this->base_connection = new DBaseEntity($resource, pathinfo($file, PATHINFO_FILENAME));
    }

    /**
     * @param string $filename
     * @return string
     */
    private function makeFullPath($filename)
    {
        return Yii::getAlias('@database') . DIRECTORY_SEPARATOR . $filename;
    }

}