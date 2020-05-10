<?php

namespace app\controllers;

use app\models\DBase;
use app\models\DBConstants;
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
        //4+4+3
        //street 4
        //home 2
        $base = new DBase(Yii::getAlias('@database') . DIRECTORY_SEPARATOR . DBConstants::KLADR);
        echo '<pre>';
        $time_pre = microtime(true);
        $row = $base->base_connection->selectIDsByCondition('NAME', 'Адыг');
        $time_post = microtime(true);
        $exec_time = $time_post - $time_pre;
        VarDumper::dump($row);
        VarDumper::dump($exec_time);
        echo '<br>';
        $time_pre = microtime(true);
        $row = $base->base_connection->selectIDsByCondition('NAME', 'Адыг');
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
        //TODO trim params
    }
}
