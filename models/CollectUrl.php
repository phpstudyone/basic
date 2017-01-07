<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "collect_url".
 *
 * @property string $id
 * @property string $url
 * @property integer $is_collect
 * @property string $create_time
 * @property string $collect_time
 */
class CollectUrl extends \yii\db\ActiveRecord
{
    const IS_COLLECt_YES = 1; //已采集
    const IS_COLLECt_NOT = 0; //未采集

    const HOST_URL = 'http://www.imooc.com';//主域名
//    const LOGIN_URL = "http://www.imooc.com/passport/user/login";       //imooc登录url(新登录url，需要验证来源和验证码)
    const LOGIN_URL = "http://www.imooc.com/user/login";                   //imooc登录url（老登录url，还能用）
    const LIST_URL = "http://www.imooc.com/course/list";                //imooc列表页 采集的入口

    const IMOOC_USERNAME = "845830229@qq.com";
    const IMOOC_PASSWORD = "zrhyhhxxy";

    /**
     * 获取采集状态
     * @param null $key
     * @return array
     */
    public static function getIsCollect($key = null){
        $data = [
            self::IS_COLLECt_NOT => 0,
            self::IS_COLLECt_YES => 1
        ];
        return $key === null ? $data : $data[$key];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'collect_url';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'is_collect', 'create_time', 'collect_time'], 'required'],
            [['is_collect', 'create_time', 'collect_time'], 'integer'],
            [['url'], 'string', 'max' => 625],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '主键id'),
            'url' => Yii::t('app', '采集的url'),
            'is_collect' => Yii::t('app', '是否已经采集'),
            'create_time' => Yii::t('app', '生成时间'),
            'collect_time' => Yii::t('app', '采集时间'),
        ];
    }
}
