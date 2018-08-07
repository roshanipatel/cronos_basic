<h1>Gestionar Usuarios</h1>

<?php
yii\grid\GridView::widget(array(
    'id' => 'user-grid',
    'dataProvider' => $model,
    'filterModel' => $filter,
    //'selectableRows' => 0,
    'summary' => 'Mostrando {end} de {count} resultado(s)',
    'columns' => array(
        'username',
        'name',
        array(
            'class' => 'yii\grid\DataColumn', 
            'label' => 'email',
            'value' => function ($data) {
               return $data->email;
            },
            'filter' => false,
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
            'label' => 'Empresa',
            'value' => function ($data) {
               return $data->company->name;
            },
            //'filter' => "CHtml::listData(Company::model()->findAll(), 'id', 'name')",
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
            'label' => 'role',
            'value' => function ($data) {
               return Roles::toString( $data->role );
            },
            'filter' => false,
        ),
        array(
            'class' => 'yii\grid\ActionColumn',
            'buttons' => array(
                'delete' => array(
                    'visible' => '$data->canDelete()',
                ),
                'print' => array(
                    'visible' => 'false',
                ),
            ),
            'buttonOptions' => array( 'style' => 'text-align: left' ),
        ),
    ),
        ) ); ?>
