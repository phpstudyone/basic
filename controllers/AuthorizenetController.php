<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/7/28
 * Time: 12:43
 */
namespace app\controllers;

use app\components\PageList;
use Yii;
use app\models\CollectDataCopy;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CollectDataCopyController implements the CRUD actions for CollectDataCopy model.
 */
class AuthorizenetController extends Controller{

    public $enableCsrfValidation = false;

    public function actionIndex(){
        return $this->render('index');
    }

    public function actionPaymentprocessor(){
        echo 111;
    }
}