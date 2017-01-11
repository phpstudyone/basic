<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\ToolHandler;
use app\models\CollectDataCopy;
use yii\console\Controller;
use app\components\DataHander;
use app\components\Redis;

use app\models\CollectData;
use app\models\CollectUrl;
use Yii;
use yii\base\Exception;
use yii\db\Query;

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

    /**
     * 全能方法，一个方法搞定所有数据，能完善能补充能新建
     */
    public function actionAlmighty(){
        set_time_limit(0);
        ini_set('memory_limit','1000M');
        $count = 7;             //数据表已有数据的最大id（也可以自定义，看想下载到多少）
        for($id = 1 ; $id <= $count ; $id++){
            $getVideoUrl = CollectDataCopy::GET_IMOOC_DOWNLOAD . "?mid=". $id . '$mode=falsh';
            $jsonData = CollectDataCopy::getContentByCurl($getVideoUrl);
            $result = json_decode($jsonData,true);
            if(isset($result['data']['result']['name']) && !empty($result['data']['result']['name'])){
                $model = CollectDataCopy::findOne(['video_id'=>$id]);
                if(!$model){
                    //不存在，判断是否存在
                    $model = new CollectDataCopy();
                    $model->video_id = $id;
                    $model->title = $result['data']['result']['name'];
                    $model->video_url = CollectDataCopy::VIDEO_URL_PREFIX . $id;
                    $model->create_time = time();
                }

                //完善课程信息
                if(!$model->learn_id){
                    $learnInfo = CollectDataCopy::getLearInfoByVideo($model->video_url);
                    $model->learn_id = $learnInfo['learn_id'];
                    $model->learn_name = $learnInfo['learn_name'];
                    $model->learn_url = $learnInfo['learn_url'];
                }

                //下载视频
                if(isset($result['data']['result']['mpath'][2]) && !empty($result['data']['result']['mpath'][2])) {
                    $model->is_exist = CollectDataCopy::IS_EXIST_YES;
                    if($model->is_download == CollectDataCopy::IS_DOWNLOAD_NOT){
                        $model->download_begin_time = time();
                        $Hmp4 = $result['data']['result']['mpath'][2];
                        $root = "c:/video";
                        $path = date('Y/m/d') . "/" ;
                        ToolHandler::createDir($path,$root);
                        $suffx = ToolHandler::getExt($Hmp4);
                        $path = $root . '/' . $path  . time() . mt_rand(100,999) . ".". $suffx;
                        $model->video_path = $path;
                        $model->download_begin_time = time();
                        ToolHandler::download_remote_file_with_curl($Hmp4,$path);
                        $model->download_end_time = time();
                        $model->is_download = CollectDataCopy::IS_DOWNLOAD_YES;
                    }
                }else{
                    $model->is_exist = CollectDataCopy::IS_EXIST_NOT;
                    $model->is_download = CollectDataCopy::IS_DOWNLOAD_NOT;
                }
                $model->json_string = $jsonData;
                $model->json_data = var_export($result,true);
                $model->save();
            }
        }
    }

    /**
     * 补充数据
     */
    public function actionRepairData(){
        set_time_limit(0);
        ini_set('memory_limit','1000M');
        $flag = true;
        while($flag){
            $data = (new Query())->select(['id','video_url'])
                ->from(CollectDataCopy::tableName())
                ->where(['json_string'=>''])
                ->orderBy(['id'=>SORT_ASC])
                ->limit(1)
                ->one();
            if($data){
                //获取课程信息
                $model = CollectDataCopy::findOne(['id'=>$data['id']]);
                $learnInfo = CollectDataCopy::getLearInfoByVideo($model->video_url);
                $model->learn_id = $learnInfo['learn_id'];
                $model->learn_name = $learnInfo['learn_name'];
                $model->learn_url = $learnInfo['learn_url'];
                //获取下载地址的json信息
                $getVideoUrl = CollectDataCopy::GET_IMOOC_DOWNLOAD . "?mid=". $model->id . '$mode=falsh';
                $jsonData = CollectDataCopy::getContentByCurl($getVideoUrl);
                $model->json_string = $jsonData;
                $model->json_data = var_export(json_decode(CollectDataCopy::getContentByCurl($getVideoUrl),true),true);
                $model->save();
            }else $flag = false;
        }
    }

    /**
     * 遍历视频，获取所有能下载的视频
     */
    public function actionErgodic(){
        set_time_limit(0);
        ini_set('memory_limit','1000M');
        for($id = 1 ; $id <= CollectDataCopy::MAXIMUM ; $id++){
            //操作前先判断是否已存在数据表(下次可以直接执行，加大循环数)
            $model = CollectDataCopy::findOne(['video_id'=>$id]);
            if(!$model){
                $getVideoUrl = CollectDataCopy::GET_IMOOC_DOWNLOAD . "?mid=". $id . '$mode=falsh';
                $result = json_decode(CollectDataCopy::getContentByCurl($getVideoUrl),true);
                if(isset($result['data']['result']['name']) && !empty($result['data']['result']['name'])){
                    $model = new CollectDataCopy();
                    $model->video_id = $id;
                    $model->title = $result['data']['result']['name'];
                    $model->is_exist = 1;
                    $model->video_url = CollectDataCopy::VIDEO_URL_PREFIX . $id;
                    $model->create_time = time();
                    $model->is_download = CollectDataCopy::IS_DOWNLOAD_NOT;
                    $model->is_exist = CollectDataCopy::IS_EXIST_NOT;
                    if(isset($result['data']['result']['mpath'][0]) && !empty($result['data']['result']['mpath'][0])){
                        $model->is_exist = CollectDataCopy::IS_EXIST_YES;
                    }
                    $model->save();
                    unset($model);
                }
            }
        }
    }

    /**
     * 爬虫
     */
    public function actionPachong(){
        set_time_limit(0);
        ini_set('memory_limit','1000M');
        try {
            $curlobj = curl_init();			// 初始化
            CollectUrl::loginImooc($curlobj,'new');
            curl_setopt($curlobj, CURLOPT_URL, "http://www.imooc.com/course/list");
//            curl_setopt($curlobj, CURLOPT_URL, "http://www.imooc.com/learn/752");
            curl_setopt($curlobj, CURLOPT_POST, 0);
            curl_setopt($curlobj, CURLOPT_HTTPHEADER, array("Content-type: text/html"));
            $output=curl_exec($curlobj);	// 执行

            /**
             * 要爬取的url
             */
            $pregCollect = [
                ['complete'=>1,'url'=>'/href="(\/course\/list\?\w{0,}=\w{0,})"/'],
                ['complete'=>1,'url'=>'/<a href="(\/course\/list\?.*=.*)" data.*/'],
                ['complete'=>1,'url'=>'/href="(\/learn\/\d{1,})"/'],
                ['complete'=>0,'url'=>'/(http:\/\/.*\.imooc\.com\/\w{1,}\/\d{1,}\.html)/'],
                ['complete'=>1,'url'=>'/href="(\/view\/\d{1,})"/'],
            ];

            /**
             * 要采集的视频url
             * href=["|\'](\/video\/\d{1,})["|\']
             */
            $preg = ["/href=[" . '"' . "|'](\/video\/\d{1,})[" . '"' . "|']/"];

            while (true){
                if($output){
                    //把要爬的url放入数据库
                    CollectUrl::saveUrl($pregCollect, $output);
                    //把采集的视频url存入数据库
                    CollectData::saveData($preg, $output);
                    $output = CollectUrl::curlWhile($curlobj);
                }else continue;
            }
            curl_close($curlobj);			// 关闭cURL
            echo $output;
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }

    /**
     * 下载视频
     */
    public function actionDownload(){
        set_time_limit(0);
        ini_set('memory_limit','1000M');
        $flag = true;
        while($flag){
            $url = (new Query())
                ->select(['video_url','id'])
                ->from(CollectData::tableName())
                ->where('is_download=:is_download',[':is_download'=>CollectData::IS_DOWNLOAD_NOT])
                ->orderBy(['id'=>SORT_ASC])
                ->limit(1)
                ->one();
            if($url){
                $preg = '/http:\/\/www.imooc.com\/video\/(\d{1,})/';
                $matches = [];
                preg_match($preg,$url['video_url'],$matches);
                $model = CollectData::findOne(['id'=>$url['id']]);
                $model->download_begin_time = time();
                if(isset($matches[1]) && !empty($matches[1])){
                    $mid = $matches[1];
                    $getVideoUrl = CollectData::GET_IMOOC_DOWNLOAD . "?mid=". $mid . '$mode=falsh';
                    $result = json_decode(CollectData::getContentByCurl($getVideoUrl),true);
                    //高清视频地址
                    $Hmp4 = isset($result['data']['result']['mpath'][0]) ? $result['data']['result']['mpath'][0] : '';
                    $videoName = isset($result['data']['result']['name']) ? $result['data']['result']['name'] : '';
                    if(!empty($Hmp4)){
                        $model->title = $videoName;
                        $root = "c:/video";
                        $path = date('Y/m/d') . "/" ;
                        ToolHandler::createDir($path,$root);
                        $suffx = ToolHandler::getExt($Hmp4);
                        $path = $root . '/' . $path . mt_rand(1,999) . time() . ".". $suffx;
                        $model->video_path = $path;
                        $model->download_begin_time = time();
                        ToolHandler::download_remote_file_with_curl($Hmp4,$path);
                        $model->download_end_time = time();
                    }
                }
                $model->is_download = CollectData::IS_DOWNLOAD_YES;
                $model->save();
                unset($model);
            }else{
                $flag = false;
            }
        }
    }



    /**
     * 数据库导出
     */
    public function actionDataTest(){
        $allTables = DataHander::getAllTables();
        if($allTables && $allTables != 'dataOver'){
            $tableName = current($allTables[0]);
            $str = "##新建" . $tableName . "\r\n";
            DataHander::writeFile($str);
            echo $str . date('Y-m-d H:i:s');
            $createTableSql = DataHander::getCreateTableSql($tableName);
            DataHander::writeFile($createTableSql . ';');
            $insertSql = DataHander::getInsertTableSql($tableName);
            DataHander::writeFile($insertSql);
            echo "插入".$tableName."数据成功 ".date('Y-m-d H:i:s') ."\r\n";
            array_shift($allTables);
            if($allTables)
                Redis::setCache('allTables',$allTables);
            else{
                Redis::setCache('allTables','dataOver');
            }
        }
    }
}
