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

    public function rules()
    {
        return [
            [['area', 'district', 'city', 'street', 'house'], 'trim'],
            [['area', 'district'], 'required'],
        ];
    }
}