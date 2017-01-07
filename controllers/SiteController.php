<?php

namespace app\controllers;


use app\components\RSA;
use app\components\SshUploadFile;
use app\models\CollectUrl;
use Yii;
use yii\base\Exception;
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
        $data=[
            'username' => CollectUrl::IMOOC_USERNAME,
            'password' => CollectUrl::IMOOC_PASSWORD,
            'remember'=>1,
            'referer'=>CollectUrl::HOST_URL];
        try {
            $curlobj = curl_init();			// 初始化
            curl_setopt($curlobj, CURLOPT_URL, CollectUrl::LOGIN_URL);		// 设置访问网页的URL
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
//             		curl_setopt($curlobj, CURLOPT_HTTPHEADER, array("application/x-www-form-urlencoded; charset=utf-8",
//             				"Content-length: ".strlen($data)
//             		));
            var_dump(curl_exec($curlobj),session_name() . '=' . session_id());die;	// 执行

            curl_setopt($curlobj, CURLOPT_URL, "http://www.imooc.com/course/list");
// 			curl_setopt($curlobj, CURLOPT_URL, "http://www.imooc.com/learn/520");
            curl_setopt($curlobj, CURLOPT_POST, 0);
            curl_setopt($curlobj, CURLOPT_HTTPHEADER, array("Content-type: text/html"));
            $output=curl_exec($curlobj);	// 执行
            var_dump($output);die;
            while (true){
                //把要爬的url放入数据库
                $preg = array(
                    '/<a href="(\/course\/list\?c=.*)" data.*/',
                    '/<a href="(\/view\/\d{3})" target="\_self">/',
                    '/<a href="(\/learn\/\d{3})" class="btn-red start-study-btn r">/'
                );
                Tool::saveUrlRewrite($preg, $output);
// 				<a target="_blank" href='/video/10005' class="J-media-item studyvideo">
                //把要获取的url放入数据库
                $preg = array('/<a target="_blank" href=\'(\/video\/\d{4,6})\' class="J-media-item studyvideo">/');
                Tool::saveGetUrlRewrite($preg, $output);
                $output = Tool::curlWhile($curlobj);
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
