<?php
$this->breadcrumbs=array(
	'Worker Profiles'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List WorkerProfiles', 'url'=>array('index')),
	array('label'=>'Create WorkerProfiles', 'url'=>array('create')),
	array('label'=>'Update WorkerProfiles', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete WorkerProfiles', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage WorkerProfiles', 'url'=>array('admin')),
);
?>

<h1>View WorkerProfiles #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'dflt_price',
	),
)); ?>
