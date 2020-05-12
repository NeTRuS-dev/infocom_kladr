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
        $search=new SearchModel();
        $search->area='Ставрополь';
        $search->district='Изоб';
        $row=$search->parseSearch();
        echo '<pre>';
        VarDumper::dump($row);
        echo '</pre>';
        die();
        return $this->render('index');
    }

    //TODO remove this
    private function getTypes(int $type)
    {
        $base = new DBase(DBNameConstants::SOCRBASE);
        $result = $base->execQuery([new SearchParameter(new EqualToStringChecker('LEVEL', "$type"))]);
        return $base->getRowsByIds($result);
    }

    public function actionProcessSearchRequest()
    {
        $query = new SearchModel();
        if ($query->load(Yii::$app->request->post()) && $query->validate()) {

        } else {

        }
    }
}
