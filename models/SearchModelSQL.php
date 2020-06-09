<?php


namespace app\models;


use yii\db\Query;

class SearchModelSQL extends \yii\base\Model implements ISearcher
{
    public array $data = [];
    private bool $get_minimum_info = true;
    private array $big_cities = [
        'Москва',
        'Севастополь',
        'Санкт-Петербург',
        'Байконур'
    ];

    public function rules()
    {
        return [
            ['data', 'safe'],
        ];
    }

    private function getQuery()
    {
        $query = new Query();
        if ($this->get_minimum_info) {
            $query->select(['id', 'NAME', 'SOCR', 'CODE']);
        }
        return $query;
    }

    private function getAreas()
    {
        return ($this->getQuery())
            ->from('area')
            ->where(['!=', 'SOCR', 'г'])
            ->orWhere(['in', 'NAME', $this->big_cities]);
    }

    public function getInitData()
    {
        return [
            'area' => $this->addMatchProp(($this->getAreas())->all()),
            'city' => $this->addMatchProp(($this->getQuery())->from('city')->where(['and', ['SOCR' => 'г'], ['in', 'NAME', $this->big_cities]])->all()),
        ];
    }

    public function getFullResponse()
    {
        $result = [];
        $this->get_minimum_info = false;
        if (isset($this->data['selected_house'])) {
            if (isset($this->data['selected_street']) || isset($this->data['selected_city'])) {
                $string = mb_strtolower($this->data['selected_house']);
                $slice = isset($this->data['selected_street']) ? $this->getCodeSlice($this->data['selected_street'], SubjectTypes::STREET) : $this->getCodeSlice($this->data['selected_city'], SubjectTypes::CITY);
                $result = ($this->getQuery())->from('house')
                    ->where(['like', 'CODE', $slice, false])
                    ->andWhere(['or',
                        ['like', 'NAME', $string . ',%', false],
                        ['like', 'NAME', '%,' . $string, false],
                        ['like', 'NAME', '%,' . $string . ',%', false]])
                    ->all();
                $result = $this->addFullSocr(array_map(function ($item) use ($string) {
                    $item['NAME'] = $string;
                    return $item;
                }, $result), SubjectTypes::HOUSE);
            } else {
                $result = [];
            }
        } elseif (isset($this->data['selected_street'])) {
            $result = [($this->getQuery())->from('street')->where(['id' => $this->data['selected_street']['id']])->one()];
            $result = $this->addFullSocr($result, SubjectTypes::STREET);
        } elseif (isset($this->data['selected_city'])) {
            $result = [($this->getQuery())->from('city')->where(['id' => $this->data['selected_city']['id']])->one()];
            $result = $this->addFullSocr($result, SubjectTypes::CITY);
        } elseif (isset($this->data['selected_district'])) {
            $result = [($this->getQuery())->from('district')->where(['id' => $this->data['selected_district']['id']])->one()];
            $result = $this->addFullSocr($result, SubjectTypes::DISTRICT);
        } elseif (isset($this->data['selected_area'])) {
            $result = [($this->getQuery())->from('area')->where(['id' => $this->data['selected_area']['id']])->one()];
            $result = $this->addFullSocr($result, SubjectTypes::AREA);
        } else {
            $result = [];
        }
        $result = $this->buildNameChain($result);
        return $result;

    }

    public function toDoSearch()
    {

        $result = [];

        if (isset($this->data['selected_city'])) {
            $result['street'] = $this->addMatchProp(($this->getQuery())->from('street')
                ->where(['like', 'CODE', $this->getCodeSlice($this->data['selected_city'], SubjectTypes::CITY), false])->all());
        }
        if (isset($this->data['selected_district'])) {

            if (!isset($result['street'])) {
                $result['street'] = $this->addMatchProp(($this->getQuery())->from('street')
                    ->where(['like', 'CODE', $this->getCodeSlice($this->data['selected_district'], SubjectTypes::CITY), false])->all());
            }
            if (!isset($result['city'])) {
                $result['city'] = $this->addMatchProp(($this->getQuery())->from('city')
                    ->where(['!=', 'SOCR', 'тер'])
                    ->andWhere(['like', 'CODE', $this->getCodeSlice($this->data['selected_district'], SubjectTypes::DISTRICT), false])->all());
            }
        }
        if (isset($this->data['selected_area'])) {
            if (!isset($result['street'])) {
                $result['street'] = $this->addMatchProp(($this->getQuery())->from('street')
                    ->where(['like', 'CODE', $this->getCodeSlice($this->data['selected_area'], SubjectTypes::CITY), false])->all());
            }
            if (!isset($result['city'])) {
                $result['city'] = $this->addMatchProp(($this->getQuery())->from('city')
                    ->where(['!=', 'SOCR', 'тер'])
                    ->andWhere(['like', 'CODE', $this->getCodeSlice($this->data['selected_area'], SubjectTypes::AREA), false])->all());
            }
            if (!isset($result['district'])) {
                $result['district'] = $this->addMatchProp(($this->getQuery())->from('district')
                    ->where(['not in', 'SOCR', ['тер', 'п']])
                    ->andWhere(['like', 'CODE', $this->getCodeSlice($this->data['selected_area'], SubjectTypes::AREA), false])->all());
            }
        }
        return $result;
    }

    public function getCheckHouseExistence()
    {
        if ((!isset($this->data['selected_street']) && !isset($this->data['selected_city'])) || !isset($this->data['checking_house'])) {
            return [
                'result' => false,
                "house_value" => (isset($this->data['checking_house']) ? $this->data['checking_house'] : '')
            ];
        }
        $string = mb_strtolower($this->data['checking_house']);
        $slice = isset($this->data['selected_street']) ? $this->getCodeSlice($this->data['selected_street'], SubjectTypes::STREET) : $this->getCodeSlice($this->data['selected_city'], SubjectTypes::CITY);
        return [
            'result' => (new Query())->from('house')
                    ->where(['like', 'CODE', $slice, false])
                    ->andWhere(['or',
                        ['like', 'NAME', $string . ',%', false],
                        ['like', 'NAME', '%,' . $string, false],
                        ['like', 'NAME', '%,' . $string . ',%', false]])
                    ->count() != 0,
            "house_value" => $this->data['checking_house']
        ];
    }

    private function addMatchProp(array $items): array
    {
        foreach ($items as &$item) {
            $item['matches'] = true;
        }
        return $items;
    }

    private function addFullSocr(array $items, int $level): array
    {
        $level_condition = ['=', 'LEVEL', $level];
        if ($level == SubjectTypes::SMALL_TER || $level == SubjectTypes::CITY) {
            $level_condition = ['or', ['=', 'LEVEL', SubjectTypes::SMALL_TER], ['=', 'LEVEL', SubjectTypes::CITY]];
        }
        foreach ($items as &$item) {
            $socrs = (new Query())->select('SOCRNAME')->from('socrbase')
                ->where($level_condition)
                ->andWhere(['SCNAME' => $item['SOCR']])->all();
            $socr = $socrs[array_rand($socrs)];
            $item['FULL_SOCR'] = $socr['SOCRNAME'];
        }
        return $items;
    }

    private function buildNameChain(array $array_with_selected_items): array
    {
        $chain = '';
        if (isset($this->data['selected_area']) && (isset($this->data['selected_district']) || isset($this->data['selected_city']) || isset($this->data['selected_street']) || isset($this->data['selected_house']))) {
            if (!in_array($this->data['selected_area']['NAME'], $this->big_cities)) {
                if ($this->data['selected_area']['SOCR'] === 'край') {
                    $chain .= "{$this->data['selected_area']['NAME']} {$this->data['selected_area']['SOCR']}";

                } else {
                    $chain .= "{$this->data['selected_area']['SOCR']} {$this->data['selected_area']['NAME']}";

                }
            }
        }
        if (isset($this->data['selected_district']) && (isset($this->data['selected_city']) || isset($this->data['selected_street']) || isset($this->data['selected_house']))) {
            $chain .= ($chain ? ", " : '') . "{$this->data['selected_district']['SOCR']} {$this->data['selected_district']['NAME']}";
        }
        if (isset($this->data['selected_city']) && (isset($this->data['selected_street']) || isset($this->data['selected_house']))) {
            $chain .= ($chain ? ", " : '') . "{$this->data['selected_city']['SOCR']} {$this->data['selected_city']['NAME']}";
        }
        if (isset($this->data['selected_street']) && (isset($this->data['selected_house']))) {
            $chain .= ($chain ? ", " : '') . "{$this->data['selected_street']['SOCR']} {$this->data['selected_street']['NAME']}";
        }
        foreach ($array_with_selected_items as &$item) {
            $item['NAME_CHAIN'] = $chain;
        }
        return $array_with_selected_items;
    }

    /**
     * @inheritDoc
     */
    public function getCodeSlice($row, $type)
    {
        $slice_length = 0;
        switch ($type) {
            case SubjectTypes::AREA:
                $slice_length = 2;
                break;
            case SubjectTypes::DISTRICT:
                $slice_length = 5;
                break;
            case SubjectTypes::CITY:
            case SubjectTypes::SMALL_TER:
                $slice_length = 11;
                break;
            case SubjectTypes::STREET:
                $slice_length = 15;
                break;
            case SubjectTypes::HOUSE:
                $slice_length = 19;
                break;
        }
        return mb_convert_encoding(mb_substr($row['CODE'], 0, $slice_length) . '%', 'UTF-8');
    }
}