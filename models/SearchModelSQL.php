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
        'Санкт-Петербург'
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

    public function toDoSearch()
    {
        if (empty($this->data)) {
            return ($this->getAreas())->all();
        } else {
            $result = [];
            if (isset($this->data['get_full_response'])) {
                $this->get_minimum_info = false;
                if (isset($this->data['selected_street'])) {
                    if (isset($this->data['selected_street'])) {
                        $result = ($this->getQuery())->from('house')
                            ->where(['like', 'CODE', $this->getCodeSlice($this->data['selected_street'], SubjectTypes::STREET), false])
                            ->andWhere(['or',
                                ['like', 'NAME', mb_strtolower($this->data['selected_house']) . ',%', false],
                                ['like', 'NAME', '%,' . mb_strtolower($this->data['selected_house']), false],
                                ['like', 'NAME', '%,' . mb_strtolower($this->data['selected_house']) . ',%', false]])
                            ->all();
                    } else {
                        $result = [];
                    }
                } elseif (isset($this->data['selected_street'])) {
                    $result = [($this->getQuery())->from('street')->where(['id' => $this->data['selected_street']['id']])->one()];
                } elseif (isset($this->data['selected_city'])) {
                    $result = [($this->getQuery())->from('city')->where(['id' => $this->data['selected_city']['id']])->one()];
                } elseif (isset($this->data['selected_district'])) {
                    $result = [($this->getQuery())->from('district')->where(['id' => $this->data['selected_district']['id']])->one()];
                } elseif (isset($this->data['selected_area'])) {
                    $result = [($this->getQuery())->from('area')->where(['id' => $this->data['selected_area']['id']])->one()];
                } else {
                    $result = [];
                }
                $result = $this->buildNameChain($result);

            } else {
                if (isset($this->data['selected_city'])) {
                    $result['street'] = ($this->getQuery())->from('street')
                        ->where(['like', 'CODE', $this->getCodeSlice($this->data['selected_city'], SubjectTypes::CITY), false])->all();
                }
                if (isset($this->data['selected_district'])) {

                    if (!isset($result['street'])) {
                        $result['street'] = ($this->getQuery())->from('street')
                            ->where(['like', 'CODE', $this->getCodeSlice($this->data['selected_district'], SubjectTypes::DISTRICT), false])->all();
                    }
                    if (!isset($result['city'])) {
                        $result['city'] = ($this->getQuery())->from('city')
                            ->where(['!=', 'SOCR', 'тер'])
                            ->andWhere(['like', 'CODE', $this->getCodeSlice($this->data['selected_district'], SubjectTypes::DISTRICT), false])->all();
                    }
                }
                if (isset($this->data['selected_area'])) {
                    if (!isset($result['city'])) {
                        $result['city'] = ($this->getQuery())->from('city')
                            ->where(['!=', 'SOCR', 'тер'])
                            ->andWhere(['like', 'CODE', $this->getCodeSlice($this->data['selected_area'], SubjectTypes::AREA), false])->all();
                    }
                    if (!isset($result['district'])) {
                        $result['district'] = ($this->getQuery())->from('district')
                            ->where(['!=', 'SOCR', 'п'])
                            ->andWhere(['like', 'CODE', $this->getCodeSlice($this->data['selected_area'], SubjectTypes::AREA), false])->all();
                    }
                }
            }
            return $result;
        }
    }

    private function buildNameChain(array $array_with_selected_items): array
    {
        $chain = '';
        if (isset($this->data['selected_area']) && (isset($this->data['selected_district']) || isset($this->data['selected_city']) || isset($this->data['selected_street']) || isset($this->data['selected_house']))) {
            if (!in_array($this->data['selected_area']['NAME'], $this->big_cities)) {
                $chain .= "{$this->data['selected_area']['SOCR']} {$this->data['selected_area']['NAME']}";
            }
        }
        if (isset($this->data['selected_district']) && (isset($this->data['selected_city']) || isset($this->data['selected_street']) || isset($this->data['selected_house']))) {
            $chain .= " -> {$this->data['selected_district']['SOCR']} {$this->data['selected_district']['NAME']}";
        }
        if (isset($this->data['selected_city']) && (isset($this->data['selected_street']) || isset($this->data['selected_house']))) {
            $chain .= " -> {$this->data['selected_city']['SOCR']} {$this->data['selected_city']['NAME']}";
        }
        if (isset($this->data['selected_street']) && (isset($this->data['selected_house']))) {
            $chain .= " -> {$this->data['selected_street']['SOCR']} {$this->data['selected_street']['NAME']}";
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
        return mb_convert_encoding(mb_substr($row['CODE'], 0, $slice_length) . '%', 'UTF-8');
    }
}