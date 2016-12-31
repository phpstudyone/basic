<?php
/**
 * 数据库操作类
 * Class DataHander
 */
namespace app\components;

use yii;
class DataHander{

    /**
     * 获取数据表所有的表
     */
    public static function getAllTables(){
        $allTables = Redis::getCache('allTables');
        $isOver = Redis::getCache('isOver');
        if(!$allTables && $isOver == 0){
            $sql = "show tables";
            $allTables = Yii::$app->db->createCommand($sql)->queryAll();
            Redis::setCache('allTables',$allTables);
            return $allTables;
        }else return false;
    }
}