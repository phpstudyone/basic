<?php

//$conn_string  =  "host=localhost port=5432 dbname=test user=postgres password=58420184" ;
//$dbconn = pg_connect($conn_string);
//if (!$dbconn)
//    echo "连接失败！！！！！/r/n";
//else
//    echo "连接成功！！！！！/r/n";
//    pg_close($dbconn);
//phpinfo();die;
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
