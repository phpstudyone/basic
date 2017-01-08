<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests/codeception');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/pachong.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
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
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
