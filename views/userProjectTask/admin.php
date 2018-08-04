<h1>Gestionar Tareas</h1>

<!--
<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>
-->
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'user-project-task-grid',
	'dataProvider'=>$model->search(),
	'filter'=>null,
    'selectableRows' => 0,
    'summaryText' => 'Mostrando {start}-{end} resultado(s)',
	'columns'=>array(
		'worker.name:text:Usuario',
		'project.name:text:Proyecto',
		array( 'header' => 'Estado',
               'value' => 'TaskStatus::toString( $data->status )', ),
        array(
            'header' => 'Inicio',
            'value' => '$data->getLongDateIni()',
        ),
        array(
            'header' => 'Final',
            'value' => '$data->getLongDateEnd()',
        ),
		/*
		'task_description',
		'ticket_id',
		*/
		array(
			'class'=>'CButtonColumn',
		),
                'print' => array(
                    'visible' => 'false',
                ),
	),
));
?>
