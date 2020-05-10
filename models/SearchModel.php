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
    //4+4+3
    //street 4
    //home 2
    public function rules()
    {
        return [
            [['area', 'district', 'city', 'street', 'house'], 'trim'],
            [['area', 'district'], 'required'],
        ];
    }
}