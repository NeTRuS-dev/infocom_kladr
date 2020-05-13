<?php

namespace app\controllers;

use app\models\SearchModel;
use Yii;
use yii\filters\Cors;
use yii\web\Controller;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
            ]
        ];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

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
        return $this->render('index');
    }

    public function actionProcessSearchRequest()
    {
        set_time_limit(0);

        $query = new SearchModel();
        if ($query->load(Yii::$app->request->post(), '') && $query->validate()) {
            return $this->asJson($query->toDoSearch());
        } else {
            return $this->asJson(['errors' => $query->getFirstErrors()]);
        }
    }
}
