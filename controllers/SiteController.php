<?php

namespace app\controllers;

use app\components\Redis;
use app\components\RSA;
use Yii;
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
