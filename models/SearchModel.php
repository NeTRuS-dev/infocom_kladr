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
            $query_result = $this->getEntitiesWithPassedType(SubjectTypes::AREA);
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
            $query_result = $this->getEntitiesWithPassedType(SubjectTypes::DISTRICT);

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
            $query_result = $this->getEntitiesWithPassedType(SubjectTypes::CITY);

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

    /**
     * @param int $level
     * @return int[]
     */
    private function getEntitiesWithPassedType(int $level)
    {
        $query_result = [];
        switch ($level) {
            case SubjectTypes::AREA:
                $query_result = $this->makeCacheableTypeQuery(
                    $this->KLADR_BASE,
                    "{$this->KLADR_BASE->getFilePrefix()}.area_cached",
                    new SearchParameter(
                        new EqualToAnyRowOfArrayChecker(
                            'SOCR',
                            $this->getTypes(SubjectTypes::AREA),
                            'SCNAME'
                        )
                    )
                );
                break;

            case SubjectTypes::DISTRICT:
                $query_result = $this->makeCacheableTypeQuery(
                    $this->KLADR_BASE,
                    "{$this->KLADR_BASE->getFilePrefix()}.district_cached",
                    new SearchParameter(
                        new EqualToAnyRowOfArrayChecker(
                            'SOCR',
                            $this->getTypes(SubjectTypes::DISTRICT),
                            'SCNAME'
                        )
                    )
                );
                break;
            case SubjectTypes::CITY:
            case SubjectTypes::SMALL_TER:
                $small_territory_and_cities = [...$this->getTypes(SubjectTypes::SMALL_TER), ...$this->getTypes(SubjectTypes::CITY)];
                $query_result = $this->makeCacheableTypeQuery(
                    $this->KLADR_BASE,
                    "{$this->KLADR_BASE->getFilePrefix()}.city_cached",
                    new SearchParameter(
                        new EqualToAnyRowOfArrayChecker(
                            'SOCR',
                            $small_territory_and_cities,
                            'SCNAME'
                        )
                    )
                );
                break;
        }
        return $query_result;
    }

    /**
     * @param int $type
     * @return array
     */
    private function getTypes($type)
    {
        $result = $this->makeCacheableTypeQuery(
            $this->SOCRBASE,
            "{$this->SOCRBASE->getFilePrefix()}.type_cache.{$type}",
            new SearchParameter(new EqualToStringChecker('LEVEL', "$type"))
        );
        return $this->SOCRBASE->getRowsByIds($result);
    }

    /**
     * @param DBase $connection
     * @param string $cache_search_string
     * @param SearchParameter $searchParameter
     * @return int[]
     */
    private function makeCacheableTypeQuery(DBase $connection, string $cache_search_string, SearchParameter $searchParameter)
    {
        $tmp_request = $this->cache_storage->get($cache_search_string);
        if ($tmp_request !== false) {
            $query_result = $tmp_request;
        } else {
            $query_result = $connection->execQuery([$searchParameter]);
            $this->cache_storage->set($cache_search_string, $query_result);
        }
        return $query_result;
    }

    /**
     * @param string $name
     * @return AbstractChecker
     */
    private function generateSubjectNameChecker($name)
    {
        return new ContainsStringChecker('NAME', $name);
    }

    /**
     * @param string $name
     * @return AbstractChecker
     */
    private function generateHouseNameChecker($name)
    {
        return new CompositeOrModeChecker([
            new StartsWithStringChecker('NAME', $name . ','),
            new EndsWithStringChecker('NAME', ',' . $name),
            new ContainsStringChecker('NAME', ',' . $name . ',')
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
                    if ((new StartsWithStringChecker('CODE', $prev_code))->check($item)) {
                        $name_chain = $prev['NAME_CHAIN'] . ' ->' . $name_chain;
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
                if ((new StartsWithStringChecker('CODE', $chain_code))->check($result)) {
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