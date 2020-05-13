<?php


namespace app\models;

use app\models\checkers\CompositeChecker;
use app\models\checkers\ContainsStringChecker;
use app\models\checkers\EqualToAnyRowOfArrayChecker;
use app\models\checkers\EqualToStringChecker;
use app\models\checkers\StartsWithAnyStringOfArrayChecker;
use Yii;
use yii\base\Model;
use yii\caching\CacheInterface;

class SearchModel extends Model
{
    public string $area = '';
    public string $district = '';
    public string $city = '';
    public string $street = '';
    public string $house = '';

    private bool $is_validated = false;

    private DBase $KLADR_BASE;
    private DBase $STREET_BASE;
    private DBase $SOCRBASE;
    private DBase $DOMA_BASE;

    private CacheInterface $cache_storage;

    public function __construct()
    {
        parent::__construct();
        $this->KLADR_BASE = new DBase(DBNameConstants::KLADR);
        $this->STREET_BASE = new DBase(DBNameConstants::STREET);
        $this->SOCRBASE = new DBase(DBNameConstants::SOCRBASE);
        $this->DOMA_BASE = new DBase(DBNameConstants::DOMA);
        $this->cache_storage = Yii::$app->cache;
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

    public function afterValidate()
    {
        parent::afterValidate();
        $this->is_validated = true;
    }

    /**
     * @return array
     */
    public function parseSearch()
    {
        if ($this->is_validated === false) {
            return [];
        }
        $built_query = [];
        $query_result = [];
        $result_rows = [];
        /**
         * @var array|null $allowed_code_begins
         */
        $allowed_code_begins = null;
        //searching for area
        if (!empty($this->area)) {
            $cache_search_string = "{$this->KLADR_BASE->getFilePrefix()}.area_cached";
            $tmp_request = $this->cache_storage->get($cache_search_string);
            if ($tmp_request !== false) {
                $query_result = $tmp_request;
            } else {
                $built_query[] = new SearchParameter(
                    new EqualToAnyRowOfArrayChecker(
                        'SOCR',
                        $this->getTypes(SubjectTypes::AREA),
                        'SCNAME'));
                $query_result = $this->KLADR_BASE->execQuery($built_query);
                $built_query = [];
                $this->cache_storage->set($cache_search_string, $query_result);
            }

            $built_query[] = new SearchParameter(
                new ContainsStringChecker('NAME', $this->area),
                1, $query_result
            );
            $query_result = $this->KLADR_BASE->execQuery($built_query);
            $built_query = [];

            if (empty($query_result)) {
                return [];
            } else if (!empty($this->district) || !empty($this->city) || !empty($this->street) || !empty($this->house)) {
                $allowed_code_begins = $this->generateAllowedCodesArray($this->KLADR_BASE->getRowsByIds($query_result), SubjectTypes::AREA);
            } else {
                $result_rows = $this->KLADR_BASE->getRowsByIds($query_result);
            }
        }
        //searching for district
        if (!empty($this->district)) {
            $start_index = 1;

            if (!empty($this->area)) {
                $start_index = $query_result[0];
            }
            //getting all districts
            $cache_search_string = "{$this->KLADR_BASE->getFilePrefix()}.district_cached";
            $tmp_request = $this->cache_storage->get($cache_search_string);
            if ($tmp_request !== false) {
                $query_result = $tmp_request;
            } else {
                $built_query[] = new SearchParameter(new EqualToAnyRowOfArrayChecker('SOCR', $this->getTypes(SubjectTypes::DISTRICT), 'SCNAME'));
                $query_result = $this->KLADR_BASE->execQuery($built_query);
                $built_query = [];
                $this->cache_storage->set($cache_search_string, $query_result);
            }
            if (!is_null($allowed_code_begins)) {
                $built_query[] = new SearchParameter(new CompositeChecker([
                    new StartsWithAnyStringOfArrayChecker('CODE', $allowed_code_begins, 'CODE'),
                    new ContainsStringChecker('NAME', $this->district)
                ]), $start_index, $query_result);
            } else {
                $built_query[] = new SearchParameter(new ContainsStringChecker('NAME', $this->district), $start_index, $query_result);
            }
            $query_result = $this->KLADR_BASE->execQuery($built_query);
            $built_query = [];
            if (empty($query_result)) {
                return [];
            } else if (!empty($this->city) || !empty($this->street) || !empty($this->house)) {
                $allowed_code_begins = $this->generateAllowedCodesArray($this->KLADR_BASE->getRowsByIds($query_result), SubjectTypes::DISTRICT);
            } else {
                $result_rows = $this->KLADR_BASE->getRowsByIds($query_result);
            }
        }
        if (!empty($this->city)) {

            $start_index = $query_result[0];

            //getting all cities
            $cache_search_string = "{$this->KLADR_BASE->getFilePrefix()}.city_cached";
            $tmp_request = $this->cache_storage->get($cache_search_string);
            if ($tmp_request !== false) {
                $query_result = $tmp_request;
            } else {
                $small_territory_and_cities = [...$this->getTypes(SubjectTypes::SMALL_TER), ...$this->getTypes(SubjectTypes::CITY)];
                $built_query[] = new SearchParameter(new EqualToAnyRowOfArrayChecker('SOCR', $small_territory_and_cities, 'SCNAME'));
                $query_result = $this->KLADR_BASE->execQuery($built_query); //all cities
                $built_query = [];
                $this->cache_storage->set($cache_search_string, $query_result);
            }

            $built_query[] = new SearchParameter(new CompositeChecker([
                new StartsWithAnyStringOfArrayChecker('CODE', $allowed_code_begins, 'CODE'),
                new ContainsStringChecker('NAME', $this->city)
            ]), $start_index, $query_result);
            $query_result = $this->KLADR_BASE->execQuery($built_query);
            $built_query = [];
            if (empty($query_result)) {
                return [];
            } else if (!empty($this->street) || !empty($this->house)) {
                $allowed_code_begins = $this->generateAllowedCodesArray($this->KLADR_BASE->getRowsByIds($query_result), SubjectTypes::CITY);
            } else {
                $result_rows = $this->KLADR_BASE->getRowsByIds($query_result);
            }
        }
        if (!empty($this->street)) {

            //useless to select types for street and cache, just begin comparing

            $built_query[] = new SearchParameter(new CompositeChecker([
                new StartsWithAnyStringOfArrayChecker('CODE', $allowed_code_begins, 'CODE'),
                new ContainsStringChecker('NAME', $this->street)
            ]));
            $query_result = $this->STREET_BASE->execQuery($built_query);
            $built_query = [];

            if (empty($query_result)) {
                return [];
            } else if (!empty($this->house)) {
                $allowed_code_begins = $this->generateAllowedCodesArray($this->STREET_BASE->getRowsByIds($query_result), SubjectTypes::STREET);
            } else {
                $result_rows = $this->STREET_BASE->getRowsByIds($query_result);
            }
        }
        if (!empty($this->house)) {

            //useless to select types for street and cache, just begin comparing

            $built_query[] = new SearchParameter(new CompositeChecker([
                new StartsWithAnyStringOfArrayChecker('CODE', $allowed_code_begins, 'CODE'),
                new ContainsStringChecker('NAME', $this->house)
            ]));
            $query_result = $this->DOMA_BASE->execQuery($built_query);
            $result_rows = $this->DOMA_BASE->getRowsByIds($query_result);
        }


        return $result_rows;
    }

    private function getTypes(int $type)
    {
        $cache_search_string = "{$this->SOCRBASE->getFilePrefix()}.type_cache.{$type}";
        $tmp_request = $this->cache_storage->get($cache_search_string);
        $result = [];
        if ($tmp_request !== false) {
            $result = $tmp_request;
        } else {
            $result = $this->SOCRBASE->execQuery([new SearchParameter(new EqualToStringChecker('LEVEL', "$type"))]);
            $this->cache_storage->set($cache_search_string, $result);
        }
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
        return array_unique($result, SORT_REGULAR);
    }
}