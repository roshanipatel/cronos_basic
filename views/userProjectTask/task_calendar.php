<div class="row">
	<div class="col-lg-12">
	    <h1 class="page-header">Tareas</h1>
	</div>
<!-- /.col-lg-12 -->
</div>

<?php echo $this->render('/userProjectTask/_form', [
    'model'=>$model,
    'isWorker' => $isWorker,
    'workers' => $workers,
    'customers' => $customers,
    'showDate' => $showDate,
    'showUser' => $showUser
    ]); ?>