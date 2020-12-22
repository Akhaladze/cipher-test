<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\Testdata */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="testdata-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'session_counter')->textInput() ?>

    <?= $form->field($model, 'session_cipher')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'request_string')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'response_string')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'data')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
