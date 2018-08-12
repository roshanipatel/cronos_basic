<?php use yii\helpers\Html;?>
<div class="row">
  	<div class="col-lg-12 ">
    	<div class="panel panel-default">
      		<div class="panel-heading">
        		Empresa
      		</div>
      		<div class="panel-body">
      			<div class="row">
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
