<h1>Tareas</h1>

<?php echo $this->render('/userProjectTask/_form', [
    'model'=>$model,
    'isWorker' => $isWorker,
    'workers' => $workers,
    'customers' => $customers,
    'showDate' => $showDate,
    'showUser' => $showUser
    ]); ?>