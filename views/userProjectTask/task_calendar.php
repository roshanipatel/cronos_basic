
<h1>Tareas</h1>
<?php echo Yii::$app->view->renderPartial('/userProjectTask/_form', [
    'model'=>$model,
    'isWorker' => $isWorker,
    'workers' => $workers,
    'customers' => $customers,
    'showDate' => $showDate,
    'showUser' => $showUser
    ]); ?>