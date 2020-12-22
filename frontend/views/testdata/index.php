<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel frontend\models\TestdataSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Результаты выполнения тестового пакета';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="testdata-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Testdata', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

	<p>
	
        <?= Html::a('Очистить таблицу', ['deleteall'], ['class' => 'btn btn-warning']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
          //  ['class' => 'yii\grid\SerialColumn'],

            //'user',
            'request_string:ntext',
			'session_counter',
            'session_cipher',
            'response_string:ntext',
            'data',
            //'id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
