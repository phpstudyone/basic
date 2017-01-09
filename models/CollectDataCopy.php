<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "collect_data_copy".
 *
 * @property string $id
 * @property string $video_id
 * @property string $title
 * @property string $video_url
 * @property integer $is_download
 * @property integer $is_exist
 * @property string $video_path
 * @property string $create_time
 * @property string $download_begin_time
 * @property string $download_end_time
 */
class CollectDataCopy extends \yii\db\ActiveRecord
{
    const MAXIMUM = 100000;         //最大的视频id
    //获取视频前缀
    const VIDEO_URL_PREFIX = "http://www.imooc.com/video/";
    //获取视频下载地址的url
    const GET_IMOOC_DOWNLOAD = "www.imooc.com/course/ajaxmediainfo/";

    const IS_DOWNLOAD_NOT = 0;
    const IS_DOWNLOAD_YES = 1;

    /**
     * 获取下载状态
     * @param null $key
     * @return array
     */
    public static function getIsDownload($key = null){
        $data = [
            self::IS_DOWNLOAD_NOT => '未下载',
            self::IS_DOWNLOAD_YES => '已下载'
        ];
        return $key === null ? $data : $data[$key];
    }

    const IS_EXIST_NOT = 0;
    const IS_EXIST_YES = 1;

    /**
     * 获取状态
     * @param null $key
     * @return array
     */
    public static function getIsExist($key = null){
        $data = [
            self::IS_EXIST_NOT => '不存在',
            self::IS_EXIST_YES => '存在'
        ];
        return $key === null ? $data : $data[$key];
    }

    /**
     * 使用CURL方式获取网页内容
     * @param string $url 要获取的内容的url
     * @return string mixed 获取的内容
     */
    public static function getContentByCurl($url){
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);			// 执行之后不直接打印出来
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'collect_data_copy';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'video_id', 'video_url', 'is_download', 'is_exist', 'create_time'], 'required'],
            [['is_download','video_id', 'is_exist', 'create_time', 'download_begin_time', 'download_end_time'], 'integer'],
            [['title'], 'string', 'max' => 128],
            [['video_url', 'video_path'], 'string', 'max' => 625],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '主键id'),
            'title' => Yii::t('app', '标题'),
            'video_url' => Yii::t('app', '视频链接'),
            'is_download' => Yii::t('app', '是否已经下载 0:未下载 1:已下载'),
            'is_exist' => Yii::t('app', '视频链接是否存在 0:不存在 1:存在'),
            'video_path' => Yii::t('app', '视频路径'),
            'create_time' => Yii::t('app', '创建时间'),
            'download_begin_time' => Yii::t('app', '下载开始时间'),
            'download_end_time' => Yii::t('app', '下载结束时间'),
        ];
    }
}
