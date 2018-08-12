<?php

use yii\helpers\Html;
use fedemotta\datatables\DataTables;
use common\components\Common;
use app\models\enums\Roles;
?>
<div class="row">
  	<div class="col-lg-12">
		<h1 class="page-header">Gestionar Tareas</h1>
  	</div>
</div>
<div class="row">
  	<div class="col-lg-12">
    	<div class="panel panel-default">
      		<div class="panel-heading">
        		Gestionar Tareas
      		</div>
      		<div class="panel-body">
      			<?php
				echo DataTables::widget([
					'id' => 'dataTables-example',
              		'class'=>'table table-striped table-bordered table-hover',
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
				]);
				?>
  			</div>
    	</div>
    </div>
</div>

?>
<!--
<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>
-->

