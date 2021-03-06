<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\DBase;
use app\models\DBNameConstants;
use app\models\SearchModelDBF;
use app\models\SubjectTypes;
use Yii;
use yii\base\ErrorException;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MakeDbController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @return int Exit code
     */
    public function actionIndex()
    {
        $model = new SearchModelDBF();
        echo 'working with socrs' . PHP_EOL;
        $cur_count = intval((new Query())->from('socrbase')->count());
        $cur_id = intval((new Query())->from('socrbase')->max('id'));
        $size = $model->SOCRBASE->getDatabaseSize();
        $start = 1;
        if ($cur_count != 0) {
            $start = intval($cur_id) + 1;
        }
        if ($cur_count == 0 || $cur_id < $size) {
            for ($i = $start; $i <= $size; ++$i) {
                $item = $model->SOCRBASE->getItemById($i);
                Yii::$app->db->createCommand()->insert('socrbase', [
                    'LEVEL' => $item['LEVEL'],
                    'SCNAME' => $item['SCNAME'],
                    'SOCRNAME' => $item['SOCRNAME'],
                    'KOD_T_ST' => $item['KOD_T_ST'],
                ])->execute();
            }
        }

        echo 'working with areas' . PHP_EOL;
        $this->makeDb($model, $model->KLADR_BASE, SubjectTypes::AREA, 'area');
        echo 'working with districts' . PHP_EOL;
        $this->makeDb($model, $model->KLADR_BASE, SubjectTypes::DISTRICT, 'district');
        echo 'working with cities' . PHP_EOL;
        $this->makeDb($model, $model->KLADR_BASE, SubjectTypes::CITY, 'city');
        echo 'working with streets' . PHP_EOL;
        $this->makeDb($model, $model->STREET_BASE, SubjectTypes::DUMMY, 'street', false);
        echo 'working with houses' . PHP_EOL;
        $this->makeDb($model, $model->DOMA_BASE, SubjectTypes::DUMMY, 'house', false);

        return ExitCode::OK;
    }

    private function makeDb($model, $connection, $type, $db_name, $check_enabled = true)
    {
        $start = 1;
        $size = $connection->getDatabaseSize();
        $cur_count = intval((new Query())->from($db_name)->count());

        if ($check_enabled === false) {
            $cur_id = intval((new Query())->from($db_name)->max('id'));
            if (!($cur_count == 0 || $cur_id < $size)) {
                return;
            }
            if ($cur_count != 0) {
                $start = $cur_id + 1;
            }
        } elseif ($cur_count != 0) {
            return;
        }

        $matches = $model->getEntitiesWithPassedType($type);

        for ($i = $start; $i <= $size; ++$i) {
            if ($check_enabled && !in_array($i, $matches)) {
                continue;
            }
            $item = $connection->getItemById($i);
            Yii::$app->db->createCommand()->insert($db_name, [
                'NAME' => $item['NAME'],
                'SOCR' => $item['SOCR'],
                'CODE' => $item['CODE'],
                'INDEX' => $item['INDEX'],
                'GNINMB' => $item['GNINMB'],
                'UNO' => $item['UNO'],
                'OCATD' => $item['OCATD'],
            ])->execute();
        }
    }
}
