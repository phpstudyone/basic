<?php

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
class CollectDataCopyController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionVue(){
        $page = Yii::$app->request->post('page');
        $total = CollectDataCopy::find()
            ->select(['id','title','learn_name','is_exist','is_download'])
            ->orderBy(['id' => SORT_ASC])
            ->limit(10)
            ->offset(($page - 1) * 10)
            ->count();
        if($page){
            $list = PageList::lists($total,$page,10);
            $data = CollectDataCopy::find()
                ->select(['id','title','learn_name','is_exist','is_download'])
                ->orderBy(['id' => SORT_ASC])
                ->limit(10)
                ->offset(($page - 1) * 10)
                ->asArray()
                ->all();
            echo json_encode(['data'=>$data,'list'=>$list]);die;
        }
        return $this->render('vue');
    }

    /**
     * Lists all CollectDataCopy models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => CollectDataCopy::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CollectDataCopy model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CollectDataCopy model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CollectDataCopy();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CollectDataCopy model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing CollectDataCopy model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CollectDataCopy model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return CollectDataCopy the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CollectDataCopy::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
