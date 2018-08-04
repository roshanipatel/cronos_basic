<h1>Proyectos</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
    'summaryText' => 'Mostrando {start}-{end} resultado(s)',
)); ?>
