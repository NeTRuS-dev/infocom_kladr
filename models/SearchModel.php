<?php


namespace app\models;

use app\models\checkers\AbstractChecker;
use app\models\checkers\CompositeAndModeChecker;
use app\models\checkers\CompositeOrModeChecker;
use app\models\checkers\ContainsStringChecker;
use app\models\checkers\EndsWithStringChecker;
use app\models\checkers\EqualToAnyRowOfArrayChecker;
use app\models\checkers\EqualToStringChecker;
use app\models\checkers\StartsWithAnyStringOfArrayChecker;
use app\models\checkers\StartsWithStringChecker;
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
            ['area', 'required', 'when' => function ($model) {
                return empty($model->district);
            }, 'message' => 'Заполните область'],
            ['district', 'required', 'when' => function ($model) {
                return empty($model->area);
            }, 'message' => 'Или район'],
        ];
    }

    public function afterValidate()
    {
        parent::afterValidate();
        $this->is_validated = true;
    }

    public function toDoSearch()
    {
        return $this->collectSearchResult();
    }

    /**
     * @return array
     */
    private function collectSearchResult()
    {
        if ($this->is_validated === false) {
            return [];
        }
        $built_query = [];
        $query_result = [];
        $result_rows = [];
        /**
         * @var array|null $code_and_name_chains
         */
        $code_and_name_chains = null;
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
                $this->generateSubjectNameChecker($this->area),
                1, $query_result
            );
            $query_result = $this->KLADR_BASE->execQuery($built_query);
            $built_query = [];

            if (empty($query_result)) {
                return [];
            } else if (!empty($this->district) || !empty($this->city) || !empty($this->street) || !empty($this->house)) {
                $code_and_name_chains = $this->generateChainsArray($this->KLADR_BASE->getRowsByIds($query_result), SubjectTypes::AREA);
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
            if (!is_null($code_and_name_chains)) {
                $built_query[] = new SearchParameter(new CompositeAndModeChecker([
                    new StartsWithAnyStringOfArrayChecker('CODE', $code_and_name_chains, 'CODE'),
                    $this->generateSubjectNameChecker($this->district)
                ]), $start_index, $query_result);
            } else {
                $built_query[] = new SearchParameter(
                    $this->generateSubjectNameChecker($this->district),
                    $start_index, $query_result
                );
            }
            $query_result = $this->KLADR_BASE->execQuery($built_query);
            $built_query = [];
            if (empty($query_result)) {
                return [];
            } else if (!empty($this->city) || !empty($this->street) || !empty($this->house)) {
                $code_and_name_chains = $this->generateChainsArray($this->KLADR_BASE->getRowsByIds($query_result), SubjectTypes::DISTRICT, $code_and_name_chains);
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

            $built_query[] = new SearchParameter(new CompositeAndModeChecker([
                new StartsWithAnyStringOfArrayChecker('CODE', $code_and_name_chains, 'CODE'),
                $this->generateSubjectNameChecker($this->city)
            ]), $start_index, $query_result);
            $query_result = $this->KLADR_BASE->execQuery($built_query);
            $built_query = [];
            if (empty($query_result)) {
                return [];
            } else if (!empty($this->street) || !empty($this->house)) {
                $code_and_name_chains = $this->generateChainsArray($this->KLADR_BASE->getRowsByIds($query_result), SubjectTypes::CITY, $code_and_name_chains);
            } else {
                $result_rows = $this->KLADR_BASE->getRowsByIds($query_result);
            }
        }
        if (!empty($this->street)) {

            //useless to select types for street and cache, just begin comparing

            $built_query[] = new SearchParameter(new CompositeAndModeChecker([
                new StartsWithAnyStringOfArrayChecker('CODE', $code_and_name_chains, 'CODE'),
                $this->generateSubjectNameChecker($this->street)
            ]));
            $query_result = $this->STREET_BASE->execQuery($built_query);
            $built_query = [];

            if (empty($query_result)) {
                return [];
            } else if (!empty($this->house)) {
                $code_and_name_chains = $this->generateChainsArray($this->STREET_BASE->getRowsByIds($query_result), SubjectTypes::STREET, $code_and_name_chains);
            } else {
                $result_rows = $this->STREET_BASE->getRowsByIds($query_result);
            }
        }
        if (!empty($this->house)) {

            //useless to select types for street and cache, just begin comparing

            $built_query[] = new SearchParameter(new CompositeAndModeChecker([
                new StartsWithAnyStringOfArrayChecker('CODE', $code_and_name_chains, 'CODE'),
                $this->generateHouseNameChecker($this->house)
            ]));
            $query_result = $this->DOMA_BASE->execQuery($built_query);
            $result_rows = $this->DOMA_BASE->getRowsByIds($query_result);
        }


        return $this->mergeWithNameChains($code_and_name_chains, $result_rows);
    }

    private function getTypes(int $type)
    {
        $cache_search_string = "{$this->SOCRBASE->getFilePrefix()}.type_cache.{$type}";
        $tmp_request = $this->cache_storage->get($cache_search_string);
        if ($tmp_request !== false) {
            $result = $tmp_request;
        } else {
            $result = $this->SOCRBASE->execQuery([new SearchParameter(new EqualToStringChecker('LEVEL', "$type"))]);
            $this->cache_storage->set($cache_search_string, $result);
        }
        return $this->SOCRBASE->getRowsByIds($result);
    }

    /**
     * @param string $name
     * @return AbstractChecker
     */
    private function generateSubjectNameChecker(string $name): AbstractChecker
    {
        $lower_name = mb_strtolower($name);
        $capitalized_name_only_first = ucfirst($lower_name);
        $capitalized_name_all_words = ucwords($lower_name);
        return new CompositeOrModeChecker([
            new ContainsStringChecker('NAME', $capitalized_name_only_first),
            new ContainsStringChecker('NAME', $name),
            new ContainsStringChecker('NAME', $capitalized_name_all_words)
        ]);
    }

    /**
     * @param string $name
     * @return AbstractChecker
     */
    private function generateHouseNameChecker(string $name): AbstractChecker
    {
        $lower_name = mb_strtolower($name);

        return new CompositeOrModeChecker([
            new StartsWithStringChecker('NAME', $lower_name . ','),
            new EndsWithStringChecker('NAME', ',' . $lower_name),
            new ContainsStringChecker('NAME', ',' . $lower_name . ',')
        ]);
    }

    /**
     * @param array $array_to_allow
     * @param int $array_level
     * @param array $previous_step_codes
     * @return array
     */
    private function generateChainsArray($array_to_allow, $array_level, $previous_step_codes = [])
    {
        $result = [];
        foreach ($array_to_allow as $item) {
            $name_chain = $item['SOCR'] . ' ' . $item['NAME'];
            if (!empty($previous_step_codes)) {
                foreach ($previous_step_codes as $prev) {
                    $prev_code = $prev['CODE'];
                    if (substr($item['CODE'], 0, strlen($prev_code)) === $prev_code) {
                        $name_chain = $prev['NAME_CHAIN'] . '->' . $name_chain;
                        break;
                    }
                }
            }
            $result[] = [
                'CODE' => $this->getCodeSlice($item, $array_level),
                'NAME_CHAIN' => $name_chain,
            ];
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * @param array $code_and_name_chains
     * @param array $result_rows
     * @return array
     */
    private function mergeWithNameChains($code_and_name_chains, $result_rows)
    {
        if (empty($code_and_name_chains)) {
            return $result_rows;
        }
        foreach ($result_rows as &$result) {
            foreach ($code_and_name_chains as $chain) {
                $chain_code = $chain['CODE'];
                if (substr($result['CODE'], 0, strlen($chain_code)) === $chain_code) {
                    $result['NAME_CHAIN'] = $chain['NAME_CHAIN'];
                    break;
                }
            }
        }
        return $result_rows;
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

}