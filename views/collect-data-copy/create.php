<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\CollectDataCopy */

$this->title = Yii::t('app', 'Create Collect Data Copy');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Collect Data Copies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="collect-data-copy-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
