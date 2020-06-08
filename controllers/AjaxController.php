<?php


namespace app\controllers;


use app\models\SearchModelDBF;
use app\models\SearchModelSQL;
use Yii;
use yii\filters\Cors;
use yii\web\Controller;

class AjaxController extends Controller
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

    public function actionProcessSearchRequest()
    {
        $query = new SearchModelSQL();
        $query->load(Yii::$app->request->post(), '');
        if ($query->validate()) {
            return $this->asJson($query->toDoSearch());
        } else {
            return $this->asJson(['errors' => $query->getFirstErrors()]);
        }
    }

    public function actionGetFullResponse()
    {
        $query = new SearchModelSQL();
        $query->load(Yii::$app->request->post(), '');
        if ($query->validate()) {
            return $this->asJson($query->getFullResponse());
        } else {
            return $this->asJson(['errors' => $query->getFirstErrors()]);
        }
    }

    public function actionCheckHouseExistence()
    {
        $query = new SearchModelSQL();
        $query->load(Yii::$app->request->post(), '');
        return $this->asJson($query->getCheckHouseExistence());
    }

    public function actionGetInitData()
    {
        $query = new SearchModelSQL();
        return $this->asJson($query->getInitData());
    }
}