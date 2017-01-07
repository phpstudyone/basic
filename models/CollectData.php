<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "collect_data".
 *
 * @property string $id
 * @property string $title
 * @property string $video_url
 * @property integer $is_download
 * @property string $video_path
 * @property string $create_time
 * @property string $download_begin_time
 * @property string $download_end_time
 */
class CollectData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'collect_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'video_url', 'is_download', 'video_path', 'create_time', 'download_begin_time', 'download_end_time'], 'required'],
            [['is_download', 'create_time', 'download_begin_time', 'download_end_time'], 'integer'],
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
            'is_download' => Yii::t('app', '是否已经下载'),
            'video_path' => Yii::t('app', '视频路径'),
            'create_time' => Yii::t('app', '创建时间'),
            'download_begin_time' => Yii::t('app', '下载开始时间'),
            'download_end_time' => Yii::t('app', '下载结束时间'),
        ];
    }
}
