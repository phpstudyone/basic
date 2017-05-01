<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CollectDataCopy */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Collect Data Copy',
]) . $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Collect Data Copies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="collect-data-copy-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
