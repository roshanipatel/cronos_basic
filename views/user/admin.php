<h1>Gestionar Usuarios</h1>

<?php
$this->widget( 'zii.widgets.grid.CGridView', array(
    'id' => 'user-grid',
    'dataProvider' => $model,
    'filter' => $filter,
    'selectableRows' => 0,
    'summaryText' => 'Mostrando {end} de {count} resultado(s)',
    'columns' => array(
        'username',
        'name',
        array(
            'name' => 'email',
            'filter' => false,
        ),
        array(
            'header' => 'Empresa',
            'name' => 'company_name',
            'value' => '$data->company->name',
            //'filter' => "CHtml::listData(Company::model()->findAll(), 'id', 'name')",
        ),
        array(
            'name' => 'role',
            'value' => 'Roles::toString( $data->role )',
            'filter' => false,
        ),
        array(
            'class' => 'CButtonColumn',
            'buttons' => array(
                'delete' => array(
                    'visible' => '$data->canDelete()',
                ),
                'print' => array(
                    'visible' => 'false',
                ),
            ),
            'htmlOptions' => array( 'style' => 'text-align: left' ),
        ),
    ),
        ) ); ?>
