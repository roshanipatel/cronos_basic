
<h1>Gestionar Empresas</h1>

<!--
<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>
-->
<?php
use yii\helpers\Html;
use yii\grid\GridView;
use common\components\Common;
?>
<?= GridView::widget([
    'id' => 'company-grid',
    'dataProvider' => $model,
    'filterModel' => $filter,
    //'selectableRows' => 0,
    'summary' => 'Mostrando {end} de {count} resultado(s)',
    'columns' => array(
        'name',
        'email',
        /*array(
            'name' => 'email',
            'filter' => false,
        ),*/
        array(
            'class' => 'yii\grid\ActionColumn',
            /*'buttons' => array(
                'delete' => array(
                    'visible' => '$data->canDelete()',
                ),
                'print' => array(
                    'visible' => 'false',
                ),
            ),
            'htmlOptions' => array( 'style' => 'text-align: left' ),*/
        ),
    ),
]);
?>
