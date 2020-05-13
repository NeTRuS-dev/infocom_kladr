<?php

namespace app\controllers;

use app\models\checkers\ContainsStringChecker;
use app\models\checkers\EqualToAnyRowOfArrayChecker;
use app\models\checkers\EqualToStringChecker;
use app\models\DBase;
use app\models\DBNameConstants;
use app\models\SearchModel;
use app\models\SearchParameter;
use app\models\SubjectTypes;
use Yii;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        set_time_limit(0);

        $search = new SearchModel();
        $search->area = 'Ставрополь';
        $search->district = 'Изоб';
        $search->city = 'Солне';
        $search->street = 'Школь';
        $search->house = '5';
        $search->validate();
        $row = $search->parseSearch();
        echo '<pre>';
        VarDumper::dump($row);
        echo '</pre>';
        die();
        return $this->render('index');
    }

    public function actionProcessSearchRequest()
    {
        set_time_limit(0);

        $query = new SearchModel();
        if ($query->load(Yii::$app->request->post()) && $query->validate()) {
            return $this->asJson($query->parseSearch());
        } else {
            return $this->asJson($query->getFirstErrors());
        }
    }
}
