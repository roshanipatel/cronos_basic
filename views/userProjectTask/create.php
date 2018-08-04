
<h1>Tareas</h1>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model,
    'isWorker' => $isWorker,
    'workers' => $workers,
    'customers' => $customers,
    'projects' => $projects,
    //'hours' => $hours,
    //'minutes' => $minutes,
    )); ?>