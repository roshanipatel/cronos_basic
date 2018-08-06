<h1>Actualizar Tarea</h1>

<?php echo Yii::$app->controller->renderPartial( '/userProjectTask/_form', [
    'model' => $model,
    'isWorker' => $isWorker,
    'workers' => $workers,
    'customers' => $customers,
    'projects' => $projects,
    //'hours' => $hours,
    //'minutes' => $minutes,
    ] ); ?>