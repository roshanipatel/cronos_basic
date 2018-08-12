<?php 
  use app\components\utils\PHPUtils;
  use app\models\enums\Roles;
  use app\models\enums\WorkerProfiles;
  use yii\widgets\DetailView;
?>
<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">Ver Usuario <?php echo $model->name; ?></h1>
  </div>
</div>

<?php

$attributes = array(
    'username',
    'name',
    'email',
    array(
        'label' => 'Inicio contrato',
        'type' => 'text',
        'value' => PHPUtils::removeHourPartFromDate($model->startcontract),
    ),
    array(
        'label' => 'Final contrato',
        'type' => 'text',
        'value' => PHPUtils::removeHourPartFromDate($model->endcontract),
    ),
    'company.name:text:Empresa',
    array(
        'label' => 'Rol',
        'type' => 'text',
        'value' => Roles::toString( $model->role ),
    )
);
// Only show profile if user is a worker of Open3s
if( $model->role != Roles::UT_CUSTOMER )
{
    $attributes[] = array(
        'label' => 'Perfil por defecto',
        'type' => 'text',
        'value' => WorkerProfiles::toString( $model->worker_dflt_profile ),
    );
}

 DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]) ?>
<!-- $this->widget( 'zii.widgets.DetailView', [
    'data' => $model,
    'attributes' => $attributes,
        ]
);  -->
<?php echo $this->render('_view', ['model'=>$model]); ?>