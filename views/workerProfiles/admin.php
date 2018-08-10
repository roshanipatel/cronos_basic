<?php
$this->breadcrumbs=array(
	'Worker Profiles'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List WorkerProfiles', 'url'=>array('index')),
	array('label'=>'Create WorkerProfiles', 'url'=>array('create')),
);

Yii::$app->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('worker-profiles-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Worker Profiles</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo Html::a('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?= $this->render('/workerProfiles/_search',[
	'model'=>$model,
]); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'worker-profiles-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'dflt_price',
		array(
			'class'=>'CButtonColumn',
		),
                'print' => array(
                    'visible' => 'false',
                ),
	),
)); ?>
