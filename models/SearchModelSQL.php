<?php


namespace app\models;


use yii\db\Query;

class SearchModelSQL extends \yii\base\Model implements ISearcher
{
    public array $data = [];

    public function rules()
    {
        return [
            ['data', 'safe'],
        ];
    }

    public function toDoSearch()
    {
        if (!isset($this->data['parent_subject'])) {
            return (new Query())->from('area')->all();
        } elseif (isset($this->data['get_districts'])) {
            return (new Query())->from('district')
                ->where(['like', 'CODE', $this->getCodeSlice($this->data['parent_subject'], SubjectTypes::AREA), false])->all();
        } elseif (isset($this->data['get_cities'])) {
            return (new Query())->from('city')
                ->where(['like', 'CODE', $this->getCodeSlice($this->data['parent_subject'], SubjectTypes::DISTRICT), false])->all();
        } elseif (isset($this->data['get_streets'])) {
            return (new Query())->from('street')
                ->where(['like', 'CODE', $this->getCodeSlice($this->data['parent_subject'], SubjectTypes::CITY), false])->all();
        } elseif (isset($this->data['get_houses'])) {
            return (new Query())->from('house')
                ->where(['like', 'CODE', $this->getCodeSlice($this->data['parent_subject'], SubjectTypes::STREET), false])->all();
        }
        return [];
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