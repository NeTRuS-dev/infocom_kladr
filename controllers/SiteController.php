<?php

namespace app\controllers;

use app\models\DBase;
use app\models\DBNameConstants;
use app\models\SearchModel;
use app\models\SearchParameter;
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
        $base = new DBase(DBNameConstants::KLADR);
        echo '<pre>';
        $time_pre = microtime(true);
        $row = $base->search([new SearchParameter('NAME', 'Адыг', DBase::CONTAINS)]);
        $time_post = microtime(true);
        $exec_time = $time_post - $time_pre;
        VarDumper::dump($row);
        VarDumper::dump($exec_time);
        echo '</pre>';
        die();
        return $this->render('index');
    }

    public function actionProcessSearchRequest()
    {
        $query = new SearchModel();
        if ($query->load(Yii::$app->request->post()) && $query->validate()) {

        } else {

        }
    }
}
