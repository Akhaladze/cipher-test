<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\TestdataSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="testdata-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'user') ?>

    <?= $form->field($model, 'session_counter') ?>

    <?= $form->field($model, 'session_cipher') ?>

    <?= $form->field($model, 'request_string') ?>

    <?= $form->field($model, 'response_string') ?>

    <?php // echo $form->field($model, 'data') ?>

    <?php // echo $form->field($model, 'id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
