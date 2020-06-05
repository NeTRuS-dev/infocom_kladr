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
use yii\base\ErrorException;
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
class MakeDbController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @return int Exit code
     */
    public function actionIndex()
    {
        $filenames = [DBNameConstants::KLADR, DBNameConstants::STREET, DBNameConstants::SOCRBASE, DBNameConstants::DOMA];
        foreach ($filenames as $filename) {
            try {
                $BASE = new DBase($filename);
            } catch (ErrorException $e) {
                return ExitCode::UNSPECIFIED_ERROR;
            }
            echo 'processing ' . $filename . PHP_EOL;
            $BASE->makeCache();
        }
        return ExitCode::OK;
    }
}
