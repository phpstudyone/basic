<?php
/**
 * 数据库操作类
 * Class DataHander
 */
namespace app\components;

use yii;
class DataHander{

    //存储sql语句的文件路径
//    const SQL_FILE_PATH = "/Applications/XAMPP/htdocs/basic/sql.sql";
    const SQL_FILE_PATH = "/Applications/XAMPP/htdocs/basic/sql1.sql";

    /**
     * 获取数据表所有的表
     * @return array|mixed
     */
    public static function getAllTables(){
        $sql = "show tables";
        $allTables = Yii::$app->db->createCommand($sql)->queryAll();
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

    /**
     * 将字符串以追加的形式写入文件
     * @param $str
     */
    public static function writeFile($str){
        file_put_contents(self::SQL_FILE_PATH,$str . "\r\n",FILE_APPEND);
    }

    /**
     * 根据表名获取插入数据的sql
     * @param $tableName
     * @param string $encode
     * @return string
     */
    public static function getInsertTableSql($tableName){
        $insertSql = "insert into " . $tableName . " values ";
        $sql = "select * from " . $tableName . " limit 500";
        $data = Yii::$app->db->createCommand($sql)->queryAll();
        if ($data) {
            $count = count($data);
            foreach ($data as $key => $value) {
                $insertSql .= "(";
                if (is_array($value)) {
                    $i = 0;
                    $valueCount = count($value);
                    foreach ($value as $k => $v) {
                        $v = addslashes($v);
                        $insertSql .= '"' . $v . '"';
                        $i++;
                        if ($i != $valueCount) $insertSql .= ",";
                    }
                }
                $insertSql .= ")";
                if ($key == $count - 1) $insertSql .= ";\r\n";
                else $insertSql .= ",\r\n";
            }
        }
        return $insertSql;
    }
}