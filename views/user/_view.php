<?php 
use yii\helpers\Html;
use app\models\enums\Roles;
use app\components\utils\PHPUtils;
use app\models\enums\WorkerProfiles;
 ?>
<div class="row">
  	<div class="col-lg-12 ">
    	<div class="panel panel-default">
      		<div class="panel-heading">
        		Usuarios
      		</div>
      		<div class="panel-body">
      			<div class="row">
	      			<div class="col-lg-12 form-group">
	      				<div class="col-lg-3">
	      					<b><?php echo Html::encode($model->getAttributeLabel('username')); ?>:</b>
	      				</div>
	      				<div class="col-lg-9">
							<?php echo Html::encode($model->username); ?>
	      				</div>
				    </div>
	      			<div class="col-lg-12 form-group">
	      				<div class="col-lg-3">
							<b><?php echo Html::encode($model->getAttributeLabel('name')); ?>:</b>
	      				</div>
	      				<div class="col-lg-9">
							<?php echo Html::encode($model->name); ?>
	      				</div>
				    </div>
	      			<div class="col-lg-12 form-group">
	      				<div class="col-lg-3">
							<b><?php echo Html::encode($model->getAttributeLabel('email')); ?>:</b>
	      				</div>
	      				<div class="col-lg-9">
							<?php echo Html::encode($model->email); ?>
	      				</div>
				    </div>
	      			<div class="col-lg-12 form-group">
	      				<div class="col-lg-3">
					        <b><?php echo Html::encode($model->getAttributeLabel('startcontract')); ?>:</b>
	      				</div>
	      				<div class="col-lg-9">
							<?php echo Html::encode(PHPUtils::removeHourPartFromDate($model->startcontract)); ?>
	      				</div>
				    </div>
	      			<div class="col-lg-12 form-group">
	      				<div class="col-lg-3">
					        <b><?php echo Html::encode($model->getAttributeLabel('endcontract')); ?>:</b>
	      				</div>
	      				<div class="col-lg-9">
							<?php echo Html::encode(PHPUtils::removeHourPartFromDate($model->endcontract)); ?>
	      				</div>
				    </div>
	      			<div class="col-lg-12 form-group">
	      				<div class="col-lg-3">
							<b><?php echo Html::encode($model->getAttributeLabel('company_id')); ?>:</b>
	      				</div>
	      				<div class="col-lg-9">
							<?php echo Html::encode($model->company->name); ?>
	      				</div>
				    </div>
	      			<div class="col-lg-12 form-group">
	      				<div class="col-lg-3">
							<b><?php echo Html::encode($model->getAttributeLabel('role')); ?>:</b>
	      				</div>
	      				<div class="col-lg-9">
							<?php echo Html::encode( Roles::toString( $model->role ) );?>
	      				</div>
				    </div>
	      			<div class="col-lg-12 form-group">
	      				<div class="col-lg-3">
							<b><?php echo Html::encode($model->getAttributeLabel('worker_dflt_profile')); ?>:</b>
	      				</div>
	      				<div class="col-lg-9">
							<?php echo Html::encode(WorkerProfiles::toString($model->worker_dflt_profile)); ?>
	      				</div>
				    </div>
				</div>
				<div class="row">
	      			<div class="col-lg-12 form-group">
						<?php echo Html::a( 'Volver',['#'] , ["onclick"=> "history.back();return false;" , "class"=>"btn btn-danger"] ); ?>
				    </div>
				</div>
      		</div>
		</div>
 	</div>
</div>
