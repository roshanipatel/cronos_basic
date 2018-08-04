<h1>Ver Usuario <?php echo $model->name; ?></h1>

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


$this->widget( 'zii.widgets.CDetailView', array(
    'data' => $model,
    'attributes' => $attributes,
        )
); ?>
