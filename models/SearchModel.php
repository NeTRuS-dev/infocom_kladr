<?php


namespace app\models;

use yii\base\Model;

class SearchModel extends Model
{
    public string $area = '';
    public string $district = '';
    public string $city = '';
    public string $street = '';
    public string $house = '';
    private DBase $KLADR;
    private DBase $NAMEMAP;
    private DBase $STREET;
    private DBase $SOCRBASE;
    private DBase $DOMA;

    public function __construct()
    {
        parent::__construct();
        $this->KLADR = new DBase(DBNameConstants::KLADR);
        $this->NAMEMAP = new DBase(DBNameConstants::NAMEMAP);
        $this->STREET = new DBase(DBNameConstants::STREET);
        $this->SOCRBASE = new DBase(DBNameConstants::SOCRBASE);
        $this->DOMA = new DBase(DBNameConstants::DOMA);
    }

    public function rules()
    {
        return [
            [['area', 'district', 'city', 'street', 'house'], 'trim'],
            ['district', 'required', 'when' => function ($model) {
                return empty($model->area);
            }],
        ];
    }

    public function parseSearch()
    {
        $built_query = [];
        $query_result = [];
        if (!empty($this->area)) {
            //TODO possible cache type searching
            $built_query[] = new SearchParameter('SOCR', DBase::IN_ARRAY_EQUALS, $this->getTypes(SubjectTypes::AREA), 'SCNAME');
            //
            $built_query[] = new SearchParameter('NAME', DBase::STR_CONTAINS, $this->area);
            $query_result = $this->KLADR->search($built_query);
            $built_query = [];

        }
        if (!empty($this->district)) {
            //TODO possible cache type searching
            $built_query[] = new SearchParameter('SOCR', DBase::IN_ARRAY_EQUALS, $this->getTypes(SubjectTypes::DISTRICT), 'SCNAME');
            $tmp_result = $this->KLADR->search($built_query); //add districts
            $built_query = [];
            //
            $built_query[] = new SearchParameter('SOCR', DBase::IN_ARRAY_EQUALS, $this->getTypes(SubjectTypes::DISTRICT), 'SCNAME');


            $built_query[] = new SearchParameter('NAME', DBase::STR_CONTAINS, $this->district);

        }
    }

    private function getTypes(int $type)
    {
        //TODO possible caching
        $result = $this->SOCRBASE->search([new SearchParameter('LEVEL', DBase::STR_EQUALS, "$type")]);
        return $this->SOCRBASE->getRowsByIds($result);
    }

    /**
     * @param array $row
     * @param int $type
     * @return string
     */
    private function getCodeSlice($row, $type)
    {
        //4+4+3
        //street 4
        //home 2
        $header_name = 'CODE';
        $slice_length = 0;
        switch ($type) {
            case SubjectTypes::AREA:
                $slice_length = 4;
                break;
            case SubjectTypes::DISTRICT:
                $slice_length = 8;
                break;
            case SubjectTypes::CITY:
            case SubjectTypes::SMALL_TER:
                $slice_length = 11;
                break;
            case SubjectTypes::STREET:
                $slice_length = 15;
                break;
            case SubjectTypes::HOUSE:
                $slice_length = 17;
                break;
        }
        return mb_substr($row[$header_name], 0, $slice_length);
    }

    /**
     * @param array $array_to_allow
     * @param int $array_level
     * @return array
     */
    private function generateAllowedCodesArray($array_to_allow, $array_level)
    {
        $result = [];
        foreach ($array_to_allow as $item) {
            $result[] = ['CODE' => $this->getCodeSlice($item, $array_level)];
        }
        return $result;
    }
}