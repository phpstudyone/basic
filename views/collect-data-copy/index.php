<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Collect Data Copies');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="collect-data-copy-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Collect Data Copy'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'title',
            'learn_name',
            [
                'label' => '是否存在下载链接',
                'attribute'=>'is_exist',
                'value' => function($model) {
                    return !empty($model->is_exist) ? \app\models\CollectDataCopy::getIsExist($model->is_exist) : '';
                }
            ],
            [
                'label' => '是否已下载',
                'attribute'=>'is_download',
                'value' => function($model) {
                    return !empty($model->is_download) ? \app\models\CollectDataCopy::getIsDownload($model->is_download) : '';
                }
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
