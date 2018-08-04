
<h1>Ver Proyecto <?php echo $model->name; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		//'id',
		//'code',
		'name',
		'company.name:text:Empresa',
	),
)); ?>
