<?php


namespace app\models;


use Yii;

class DBase
{
    //jesus kill the guy named those dbases
    private ?DBaseEntity $base_connection = null;

    const STR_CONTAINS = 0;
    const STR_STARTS_WITH = 1;
    const STR_EQUALS = 2;

    const IN_ARRAY_CONTAINS = 3;
    const IN_ARRAY_STARTS_WITH = 4;
    const IN_ARRAY_EQUALS = 5;

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
            if ($param->mode >= 3) {
                $result = $this->base_connection->SelectIDsWithValueInArray($param->header_name, $param->header_in_array_name, $param->to_search, $param->mode, (empty($param->array_for_search) ? $result : $param->array_for_search));
            } else {
                $result = $this->base_connection->selectIDsByCondition($param->header_name, $param->to_search, $param->mode, (empty($param->array_for_search) ? $result : $param->array_for_search));
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
            $result[] = $this->getItemById($id);
        }
        return $result;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getItemById($id)
    {
        return $this->base_connection->getRecord($id);
    }

    /**
     * @param string $filename
     */
    private function load($filename)
    {
        $file = $this->makeFullPath($filename);
        $resource = dbase_open($file, 0);
//        $this->base_connection = new DBaseEntity($resource, $filename === DBNameConstants::KLADR);
        $this->base_connection = new DBaseEntity($resource, true);
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