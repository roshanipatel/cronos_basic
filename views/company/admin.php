<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">Gestionar Empresas</h1>
  </div>
</div>
<?php
use yii\helpers\Html;
use fedemotta\datatables\DataTables;
use common\components\Common;
?>
<?= DataTables::widget([
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
