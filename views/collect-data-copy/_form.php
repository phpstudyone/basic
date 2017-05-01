<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CollectDataCopy */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="collect-data-copy-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'video_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'video_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'learn_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'learn_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_download')->textInput() ?>

    <?= $form->field($model, 'is_exist')->textInput() ?>

    <?= $form->field($model, 'video_path')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'create_time')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'json_string')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'json_data')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'download_begin_time')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'download_end_time')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
