
<h1>Gestionar Empresas</h1>

<!--
<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>
-->

<?php
$this->widget( 'zii.widgets.grid.CGridView', array(
    'id' => 'company-grid',
    'dataProvider' => $model,
    'filter' => $filter,
    'selectableRows' => 0,
    'summaryText' => 'Mostrando {end} de {count} resultado(s)',
    'columns' => array(
        'name',
        array(
            'name' => 'email',
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
) );
?>
