<?php

use yii\helpers\Html;
use fedemotta\datatables\DataTables;
use common\components\Common;
use app\models\enums\Roles;
?>
<div class="row">
    <div class="col-lg-12">
		<h1 class="page-header">Tareas</h1>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Tareas
            </div>
            <div class="panel-body">
            	<?php
				echo DataTables::widget([
					'dataProvider'=>$dataProvider,
					'itemView'=>'_view',
				    'summaryText' => 'Mostrando {start}-{end} resultado(s)',
				    ]);
				    ?>
			</div>
		</div>
	</div>
</div>

