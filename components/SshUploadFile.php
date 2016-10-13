<?php
/**
 * Created by PhpStorm.
 * User: rihui.zhang
 * Date: 2016/10/12
 * Time: 11:22
 */
namespace app\components;

use Yii;
use yii\base\Exception;
class SshUploadFile{
    /**
     * 批量获取远程服务器上文件
     * 有bug，没考虑目录存在情况
     * 应该目录不存在就创建
     * @param array $path
     * @param $localPath
     */
    public static function get(array $path , $localPath = "C:/wamp/www/shop/"){
        $ssh = Yii::$app->ssh2;
        foreach ($path as $value){
            try{
                $ssh->get($value,$localPath , $value);
            }catch (Exception $e){
//                echo $e->getMessage();
            }
        }
    }
}