<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Proyectos</h1>
	</div>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
    'summaryText' => 'Mostrando {start}-{end} resultado(s)',
)); ?>
