
<h1>Ver Tarea</h1>

<?php
$this->widget( 'zii.widgets.CDetailView', array(
    'data' => $model,
    'attributes' => array(
        'worker.name:text:Usuario',
        'project.name:text:Proyecto',
        array( 'label' => 'Estado',
            'value' => TaskStatus::toString( $model->status ) ),
        array( 'label' => 'Perfil',
            'value' => WorkerProfiles::toString( $model->profile_id ) ),
        array( 'label' => 'Fecha inicial',
            'value' => $model->getLongDateIni() ),
        array( 'label' => 'Fecha final',
            'value' => $model->getLongDateEnd() ),
        'task_description',
        array(
            'label' => 'Ticket',
            'type' => 'raw',
            'value' => Html::a( 'ticket url',
                                    CHtml::normalizeUrl( CronosUtils::getTicketUrl( $model->ticket_id ) ),
                                    array( 'target' => '_blank' ) ),
        ),
    ),
) ); ?>
