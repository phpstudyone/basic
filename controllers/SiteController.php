<?php

namespace app\controllers;


use app\components\RSA;
use app\components\SshUploadFile;
use app\models\CollectData;
use app\models\CollectUrl;
use Yii;
use yii\base\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * 更具用户id生产 tbl_arawrds 表数据
     *
     * user_id, first_name, last_name, business_name, email, user_type, subscription, status, published_count, city, state, country,
    accessed_award_site, copied_bade_info, tweeted, facebook_shared, copied_blog_header, copied_larger_badge, copied_certificate,
    download_small_badge, download_large_badge, download_certificate, download_blog_header, download_small_badge_count,
    download_large_badge_count, download_blog_header_count, download_certificate_count, year, accepted_online, accepted_partial, accepted_print
     *
     */
    public function actionCreateSql(){
        $emails = ['gzp1@twobrightlights.com','gzp2@twobrightlights.com','gzp3@twobrightlights.com'];
        foreach ($emails as $email){
            //查询id, email,first_name,last_name,organization_name,user_type,user_status as statuscity,subscription_name
            $sql = "select
  u.id as user_id, u.email, up.first_name, up.last_name, up.organization_name, ut.user_type, u.user_status as status,ua.city, s.subscription_name
from users u
  left join user_profiles up on u.id=up.user_id
  left join usertypes ut on u.usertype_id=ut.id
  left join user_addresses ua on u.id=ua.user_id
  left join user_subscriptions us on u.id=us.user_id
  left join subscriptions s on us.subscription_id=s.id
where u.email ='".$email."' limit 1;";
            $result = Yii::$app->db->createCommand($sql)->queryOne();
            if($result['user_id']){
                $insertData = [
                    'user_id' => $result['user_id'],
                    'first_name' => $result['first_name'],
                    'last_name' => $result['last_name'],
                    'business_name' => $result['organization_name'],
                    'email' => $result['email'],
                    'user_type' => $result['user_type'],
                    'subscription' => $result['subscription_name'],
                    'status' => $result['status'],
                    'published_count' => null,
                    'city' => $result['city'],
                    'state' => null,
                    'country' => null,
                    'accessed_award_site' => null,
                    'copied_bade_info' => null,
                    'tweeted' => null,
                    'facebook_shared' => null,
                    'copied_blog_header' => null,
                    'copied_larger_badge' => null,
                    'copied_certificate' => null,
                    'download_small_badge' => null,
                    'download_large_badge' => null,
                    'download_certificate' => null,
                    'download_blog_header' => null,
                    'download_small_badge_count' => null,
                    'download_large_badge_count' => null,
                    'download_blog_header_count' => null,
                    'download_certificate_count' => null,
                    'year' => '2017',
                    'accepted_online' => null,
                    'accepted_partial' => null,
                    'accepted_print' => null
                ];
                $key = implode(array_keys($insertData),',');
                $value = '"' . implode(array_values($insertData),'","') . '"';


                echo "INSERT INTO twobrightlights.tbl_awards(" . $key . ")values(" . $value . ");" . "<br />";

            }
        }

    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * 爬虫测试
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

    public function actionText1(){
        for($i = 1 ; $i<10;$i++){
            try{
                if($i == 5){
                    throw new Exception('测试');
                }
                echo $i;
            }catch (Exception $e){
            }
        }
    }


    public function actionText(){
        SshUploadFile::get([
            "Application\Shop\View\Public\error.html",
            'Application/Shop/View/Public/error.html',
        ],"c:/");die;
    }
    public function actionIndex()
    {
        header ( "Content-type:text/html;charset=utf-8" );
        $connection = ssh2_connect('172.16.40.250', 22, array('hostkey'=>'ssh-rsa'));

        if (ssh2_auth_pubkey_file($connection, 'app',
            "C:/wamp/www/basic/web/id_rsa.pub",
            "C:/wamp/www/basic/web/App", 'baiyang')) {
            echo "连接172.16.40.250:22成功";
            $sftp = ssh2_sftp($connection);
            if(ssh2_scp_recv($connection, '/var/www/html/web/Application/Shop/View/Public/error.html', './error.html')){
                echo "接受文件成功";
            }

            if(ssh2_scp_send ($connection, './error.html', '/var/www/html/web/Application/Shop/View/Public/error1.html')){
                echo "发送文件成功";
            }

        } else {
            die('Public Key Hostbased Authentication Failed');
        }die;




        $ftp = Yii::$app->ftp;
        var_dump($ftp);die;
        phpinfo();die;
        $arr = RSA::createRsaKey();
        $myfile = fopen ( "rsa_public_key.pem", "w" ) or die ( "Unable to open file!" );
        fwrite ( $myfile, $arr ['publicKey'] );
        fclose ( $myfile );

        $myfile = fopen ( "rsa_private_key.pem", "w" ) or die ( "Unable to open file!" );
        fwrite ( $myfile, $arr ['privateKey'] );
        fclose ( $myfile );
        print_r ( $arr );
        die;
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
}