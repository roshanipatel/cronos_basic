<h1>Gestionar Proyectos</h1>

<?php
$this->widget( 'zii.widgets.grid.CGridView', array(
    'id' => 'project-grid',
    'dataProvider' => $model->search(),
    'filter'=>$model,
    //'filter' => null,
    'selectableRows' => 0,
    'summaryText' => 'Mostrando {start}-{end} resultado(s)',
    'columns' => array(
        //'code',
        'name',
        array(
            'header' => 'Empresa',
            'name' => 'company_name',
            'value' => '$data->company->name',
        ),
        array(
            'name' => 'status',
            'value' => 'ProjectStatus::toString($data->status)',
			'filter' => ProjectStatus::getDataForDropdown(),
			'htmlOptions' => array(
				'style' => 'width: 100px'
			),
        ),
        array(
            'name' => 'cat_type',
            'value' => '(empty($data->cat_type))?"":ProjectCategories::toString($data->cat_type)',
			'filter' => ProjectCategories::getDataForDropdown(),
        ),
        array(
            'class' => 'CButtonColumn',
            'buttons' => array(
                'delete' => array(
                    'visible' => '!$data->hasTasks()',
                ),
                'print' => array(
                    'visible' => 'false',
                ),
            ),
            'htmlOptions' => array( 'style' => 'text-align: left' ),
        ),
    ),
) ); ?>
