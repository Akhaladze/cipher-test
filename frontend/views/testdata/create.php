<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Testdata */

$this->title = 'Create Testdata';
$this->params['breadcrumbs'][] = ['label' => 'Testdatas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="testdata-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
