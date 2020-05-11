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
        $result_query = [];
        $result_query[] = new SearchParameter('SOCR', DBase::IN_ARRAY, $this->getTypes(1), 'SCNAME');
        $result_query[] = new SearchParameter('NAME', DBase::STR_CONTAINS, 'Дагест');
        $row = $base->search($result_query);
        VarDumper::dump($base->getRowsByIds($row));
        echo '</pre>';
        die();
        return $this->render('index');
    }

    //TODO remove this
    private function getTypes(int $type)
    {
        $base = new DBase(DBNameConstants::SOCRBASE);
        $result = $base->search([new SearchParameter('LEVEL', DBase::STR_EQUALS, "$type")]);
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
