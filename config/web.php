<?php

$params = require(__DIR__ . '/params.php');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'in the following',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'redis' => [
            'class' => 'yii\redis\Cache',
//            'class' => 'yii\redis\Connection',//此种配置不能在set里面设置过期时间，需要单独设置 不采用
            'redis'=>[
                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
            ]
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'ftp' => [
            'class' => 'app\extensions\ftp\FtpComponent',
            'host' => '172.16.40.250',
            'username' => 'app',
            'password' => 'baiyang',
            'ssl' => false,
            'timeout' => 90,
            'port' => 21,
            'autoConnect' => true,
            'dir' => '',
        ],

        'ssh2' => [
            'class' => 'app\extensions\ssh2\Ssh2',
            'host' => '172.16.40.250',
            'username' => 'app',
            'password' => 'baiyang',
            'port' => 22,
            'pubkeyfile' => "C:/wamp/www/basic/web/id_rsa.pub",
            'privkeyfile' => "C:/wamp/www/basic/web/App",
            'autoConnect' => true,
            'dir' => '/var/www/html/web/',
        ],

        'ctp' => [
            'class' => 'app\extensions\ctp\ChineseToPiny',
        ],

        'db' => require(__DIR__ . '/pachong.php'),

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;