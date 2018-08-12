<div class="row">
  <div class="col-lg-12">
	<h1 class="page-header">Ver Empresa <?php echo $model->name; ?></h1>
  </div>
</div>
<?php
use yii\widgets\DetailView;
 DetailView::widget([
    'model' => $model,
    'attributes' => 
    [
    	'name',
		'email',
    ],
]) ;
 //$this->widget( 'zii.widgets.DetailView', [
 //   'data' => $model,
 //   'attributes' => $attributes,
  //      ]
//);  
 echo $this->render('_view', ['model'=>$model]);
?>