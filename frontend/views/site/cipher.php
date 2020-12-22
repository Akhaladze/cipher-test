<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Cipher Test Page';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Результаты выполнения тестового пакета: </p>

    <code>url сервиса: http://signer-service-cipher-00.apps.cl02.core.local/</code>
<?php	
	//echo $request->format; // outputs: 'json'
	echo "<br>";
	//echo $response_start_session->content; // outputs: 'json'
	echo "<br>";
	echo 'Количество пакетных сессий: ' . $counter;
	echo "<br>";
	echo '<a href="/testdata"> Перейти к результатам тестирования</a>';
?>
</div>
