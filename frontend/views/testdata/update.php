<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Testdata */

$this->title = 'Update Testdata: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Testdatas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="testdata-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
