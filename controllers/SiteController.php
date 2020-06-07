<?php

namespace app\controllers;

use Yii;
use yii\base\ErrorException;
use yii\db\Query;
use yii\web\Controller;
use yii\web\HttpException;

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
        if ((new Query())->from('migration')->count() < 11) {
            throw new HttpException(404, 'Примените миграции!!!');
        }
        return $this->render('index');
    }

}
