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

    /**
     * imooc 登录新地址，成功会返回json
     *
     * {
            "status": 10001,   //9006:不允许的来源请求 90003验证码为空
            "msg": "成功",
            "data": {
                "userInfo": {
                    "uid": "1155832"
                },
                "url": [
                    "http://www.imooc.com/user/ssologin?token=Ocb8J8UhVC9DLz-CQbQnuffjNYEm1OZiquCEACdPNvfjC69L7q5xTMOwAtpTHSqDsZ-n8cmYlH_lXFc0LvBlRelLHgB6Hk6MMKC-LD_d7mdi2Wxfq44jzwXLCjBE2o80JRWPYra3GwIa3PnoEieAc488aShoHxQ9MfJDekq8sp92G2DJKEaA4TaVsaW8Kg3MkA9QwGfBRhNDfve9ATifYkP8BVubKCDTcPj8A-EVzJovVYWbGOrEqA,,-a1wrtUbkEH7JiV",
                    "http://coding.imooc.com/user/ssologin?token=25Mmq6nj8Vj9poNt38aSd5io1MQqnXJrDHnssnHjppb6oJEqWAVndRKIGAI5_ruyYgf7gj2IxOKq8TbdaRc9uWwicpyb3pzLEFX8PtAwt3vsxrZCJZWYaHp6ZjqRo0mqdarQVf7tnKhYp6t2u1K1a8pnAFK8jsOoNvFZSwfQ_ee0sGTjznLyKQU7a7jYqAgTFdzgMjyT9WWm3mZRgw120UBjyBTR1mq3idq7yw_IKfEtjj2W4i7LIA,,-pVLjIOw"
                ]
            }
        }
     *
     */
    const NEW_LOGIN_URL = "http://www.imooc.com/passport/user/login";

    /**
     *imooc登录url（老登录url，还能用）
     */
    const OLD_LOGIN_URL = "http://www.imooc.com/user/login";

    /**
     * imooc列表页 采集的入口
     */
    const LIST_URL = "http://www.imooc.com/course/list";

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
     * 登录imooc
     * @param $curlobj
     * @param string $state
     */
    public static function loginImooc($curlobj,$state = 'new'){
        $data = [
            'username' => CollectUrl::IMOOC_USERNAME,
            'password' => CollectUrl::IMOOC_PASSWORD,
            'remember'=>1,
        ];
        $loginUrl = self::NEW_LOGIN_URL;
        if($state == 'new'){
            $data['referer'] = CollectUrl::HOST_URL;
        }elseif(state == 'old'){
            $loginUrl = self::OLD_LOGIN_URL;
        }
        curl_setopt($curlobj, CURLOPT_URL, $loginUrl);		// 设置访问网页的URL
        curl_setopt($curlobj, CURLOPT_RETURNTRANSFER, true);			// 执行之后不直接打印出来

        // Cookie相关设置，这部分设置需要在所有会话开始之前设置
        date_default_timezone_set('PRC'); // 使用Cookie时，必须先设置时区
        curl_setopt($curlobj, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt($curlobj, CURLOPT_COOKIEFILE, 'cookiefile');
        curl_setopt($curlobj, CURLOPT_COOKIEJAR, 'cookiefile');
        curl_setopt($curlobj, CURLOPT_COOKIE, session_name() . '=' . session_id());
        curl_setopt($curlobj, CURLOPT_HEADER, 0);
        curl_setopt($curlobj, CURLOPT_FOLLOWLOCATION, 1); // 这样能够让cURL支持页面链接跳转

        curl_setopt($curlobj, CURLOPT_POST, 1);
        curl_setopt($curlobj, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($curlobj);	// 执行
        $code = json_decode($result,true)['status'];
        if($code != 10001 && $state == 'new'){
            self::loginImooc($curlobj,'old');
        }
    }

    /**
     * 把需要爬的url保存在CURL表中
     * @param Array $preg 获取页面的链接的正则表达式  数组
     * @param string $page 页面内容
     */
    public static function saveUrlRewrite($preg,$page){
        foreach ($preg as $val){
            $matches = [];
            preg_match_all($val['url'],$page,$matches);
            if (!empty($matches)){
                foreach ($matches[0] as $value){
                    if($val['complete'] == 0)
                        $url = $value;
                    else $url = self::HOST_URL . $value;
                    $model = self::findOne(['url'=>$url]);
                    if (!$model){
                        $model = new self();
                        $model->url = $url;
                        $model->is_collect = self::IS_COLLECt_NOT;
                        $model->create_time = time();
                        $model->save();
                    }
                }die;
            }
        }
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
            [['url', 'is_collect', 'create_time'], 'required'],
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
