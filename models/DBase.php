<?php


namespace app\models;


use Yii;

class DBase
{
    //jesus kill the guy named those dbases
    private ?DBaseEntity $base_connection = null;

    const CONTAINS = 0;
    const STARTS_WITH = 1;
    const EQUALS = 2;
    const SELECT_TYPES = 3;

    public function __construct(string $filename)
    {
        $this->load($filename);
    }

    /**
     * @param SearchParameter[] $search_params
     * @return int[]
     */
    public function search($search_params)
    {
        $result = [];
        foreach ($search_params as $param) {
            if ($param->mode === DBase::SELECT_TYPES) {
                $result = $this->base_connection->SelectIDsWithGivenType($param->header_name, $param->to_search, $param->level_type);
            } else {
                $result = $this->base_connection->selectIDsByCondition($param->header_name, $param->to_search, $param->mode, $result);
            }
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
            $result[] = $this->base_connection->getRecord($id);
        }
        return $result;
    }

    /**
     * @param string $filename
     */
    private function load($filename)
    {
        $file = $this->makeFullPath($filename);
        $resource = dbase_open($file, 0);
        $this->base_connection = new DBaseEntity($resource, pathinfo($file, PATHINFO_FILENAME), $filename === DBNameConstants::KLADR);
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