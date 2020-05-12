<?php


namespace app\models;

use app\models\checkers\ContainsStringChecker;
use app\models\checkers\EqualToAnyRowOfArrayChecker;
use app\models\checkers\EqualToStringChecker;
use app\models\checkers\StartsWithAnyStringOfArrayChecker;
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
            [['area', 'district'], 'trim'],
//            [['area', 'district', 'city', 'street', 'house'], 'trim'],
            ['district', 'required', 'when' => function ($model) {
                return empty($model->area);
            }],
        ];
    }

    public function parseSearch()
    {
        $built_query = [];
        $query_result = [];
        //searching for area
        if (!empty($this->area)) {
            //TODO possible cache type searching
            $built_query[] = new SearchParameter(
                new EqualToAnyRowOfArrayChecker(
                    'SOCR',
                    $this->getTypes(SubjectTypes::AREA),
                    'SCNAME'));
            //
            $built_query[] = new SearchParameter(
                new ContainsStringChecker('NAME', $this->area),
            );
            $query_result = $this->KLADR->search($built_query);
            $built_query = [];

        }
        //searching for district
        if (!empty($this->district)) {
            $allowed_code_begins = null;
            $start_index = 1;

            if (!empty($this->area) && !empty($query_result)) {
                $allowed_code_begins = $this->generateAllowedCodesArray($this->KLADR->getRowsByIds($query_result), SubjectTypes::AREA);
                $start_index = $query_result[0];
            }
            //TODO possible cache type searching
            //getting all districts
            $built_query[] = new SearchParameter(new EqualToAnyRowOfArrayChecker('SOCR', $this->getTypes(SubjectTypes::DISTRICT), 'SCNAME'));
            $query_result = $this->KLADR->search($built_query); //add districts
            $built_query = [];
            $make_clear_query = true;
            if (!is_null($allowed_code_begins)) {
                $make_clear_query = false;
                $built_query[] = new SearchParameter(new StartsWithAnyStringOfArrayChecker('CODE', $allowed_code_begins, 'CODE'), $start_index, $query_result);

            }
            //
            //if isset area index after first found area
            //getting searched distr from those in area
            if ($make_clear_query) {
                $built_query[] = new SearchParameter(new ContainsStringChecker('NAME', $this->district), $start_index, $query_result);

            } else {
                $built_query[] = new SearchParameter(new ContainsStringChecker('NAME', $this->district), $start_index);

            }
            $query_result = $this->KLADR->search($built_query);
            $built_query = [];
        }


        return $this->KLADR->getRowsByIds($query_result);
    }

    private function getTypes(int $type)
    {
        //TODO possible caching
        $result = $this->SOCRBASE->search([new SearchParameter(new EqualToStringChecker('LEVEL', "$type"))]);
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
                $slice_length = 2;
                break;
            case SubjectTypes::DISTRICT:
                $slice_length = 4;
                break;
            case SubjectTypes::CITY:
            case SubjectTypes::SMALL_TER:
                $slice_length = 8;
                break;
            case SubjectTypes::STREET:
                $slice_length = 11;
                break;
            case SubjectTypes::HOUSE:
                $slice_length = 15;
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
        return array_unique($result);
    }
}