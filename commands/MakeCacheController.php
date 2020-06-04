<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\DBase;
use app\models\DBNameConstants;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MakeCacheController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @return int Exit code
     */
    public function actionIndex()
    {
        $KLADR_BASE = new DBase(DBNameConstants::KLADR);
        $STREET_BASE = new DBase(DBNameConstants::STREET);
        $SOCRBASE = new DBase(DBNameConstants::SOCRBASE);
        $DOMA_BASE = new DBase(DBNameConstants::DOMA);
        $KLADR_BASE->makeCache();
        $STREET_BASE->makeCache();
        $SOCRBASE->makeCache();
        $DOMA_BASE->makeCache();
        return ExitCode::OK;
    }
}
