<?php
/**
 * Created by PhpStorm.
 * User: rihuizhang
 * Date: 16-10-6
 * Time: 下午8:55
 */
namespace app\components;
use Yii;
class Redis{

    const EXPIPE_TIME = 3600;

    /**
     * 设置缓存
     * @param string $key
     * @param string $value
     * @param int $expre
     * @return mixed
     */
    public static function setCache($key , $value , $expre = self::EXPIPE_TIME){
        return Yii::$app->redis->set($key,$value,$expre);
    }

    /**
     * 设置多个缓存
     * @param array $data
     * @param int $expre
     * @return mixed
     */
    public static function setCaches(array $data , $expre = self::EXPIPE_TIME){
        return Yii::$app->redis->mset($data,$expre);
    }

    /**
     * 获取缓存
     * @param $key
     * @return mixed
     */
    public static function getCache($key){
        return Yii::$app->redis->get($key);
    }

    /**
     * 获取多个缓存
     * @param array $data
     * @return mixed
     */
    public static function getCaches(array $data){
        return Yii::$app->redis->mget($data);
    }
}