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
     * @return array|mixed
     */
    public static function getAllTables(){
        $allTables = Redis::getCache('allTables');
        $isOver = Redis::getCache('isOver');
        if(!$allTables){
            $sql = "show tables";
            $allTables = Yii::$app->db->createCommand($sql)->queryAll();
            Redis::setCache('allTables',$allTables);
        }
        return $allTables;
    }

    /**
     * 根据表名获取建表语句
     * @param $tableName
     * @return bool|mixed
     */
    public static function getCreateTableSql($tableName){
        $sql = "SHOW CREATE TABLE " . $tableName;
        $createSql = Yii::$app->db->createCommand($sql)->queryOne();
        if($createSql){
            return end($createSql);
        }else return false;
    }
}