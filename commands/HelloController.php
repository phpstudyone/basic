<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use app\components\DataHander;
use app\components\Redis;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";
    }

    public function actionDataTest(){
        $allTables = DataHander::getAllTables();
        if($allTables && $allTables != 'dataOver'){
            $tableName = current($allTables[0]);
            $str = "##新建" . $tableName . "\r\n";
            DataHander::writeFile($str);
            echo $str;
            $createTableSql = DataHander::getCreateTableSql($tableName);
            DataHander::writeFile($createTableSql . ';');
            $insertSql = DataHander::getInsertTableSql($tableName);
            DataHander::writeFile($insertSql);
            echo "插入".$tableName."数据成功";
            array_shift($allTables);
            if($allTables)
                Redis::setCache('allTables',$allTables);
            else{
                Redis::setCache('allTables','dataOver');
            }
        }
    }
}
