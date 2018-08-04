<?php
use yii\helpers\Html;
?>
<h1>Tareas</h1>

<?php echo Yii::$app->controller->renderPartial('/userProjectTask/_form', [
    'model'=>$model,
    'isWorker' => $isWorker,
    'workers' => $workers,
    'customers' => $customers,
    'showDate' => $showDate,
    'showUser' => $showUser
    ]); ?>