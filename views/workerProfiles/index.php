<?php
$this->breadcrumbs=array(
	'Worker Profiles',
);

$this->menu=array(
	array('label'=>'Create WorkerProfiles', 'url'=>array('create')),
	array('label'=>'Manage WorkerProfiles', 'url'=>array('admin')),
);
?>

<h1>Worker Profiles</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
