<?php
/* PARAMETERS
 * @param $taskSearch TaskSearch
 * @param $projectsProvider Project[]
 * @param $usersProvider User[]
 * @param $tasksProvider UserProjectTask[]
 * @param $searchFieldsToHide string[]
 *
 *  */
?>

<h1>Consultar horas</h1>

<?php
/* SEARCH FORM */

Yii::$app->controller->renderPartial( '/userProjectTask/_searchForm',
        [
            'taskSearch' => $taskSearch,
            'projectsProvider' => $projectsProvider,
            'usersProvider' => $usersProvider,
            'tasksProvider' => $tasksProvider,
            'searchFieldsToHide' => $searchFieldsToHide,
            'showExportButton' => $showExportButton,
            'actionURL' => $actionURL,
 			'projectStatus' => NULL,
			'onlyManagedByUser' => FALSE,
       ] );

/* END SEARCH FORM */

?>

<?php
/***********************************/
/* Hidden form for refusing tasks  */
/***********************************/
?>

<div id="divChangeTask" style="display:none">
<?php echo CHtml::beginForm( array( 'userProjectTask/refuseTask' ), 'post', array(
    'id' => 'frmChangeTask',
) );?>
<?php echo CHtml::hiddenField('taskId'); ?>
<?php echo CHtml::textArea('motive', '', array(
    'style' => 'width: 100%; height: 90px',
)); ?>
<?php echo CHtml::endForm(); ?>
</div>

<script type="text/javascript">
// Hide refusing dialog
jQuery(document).ready(function(){
    jQuery("#divChangeTask").dialog({
            title: "¿Por qué desea rechazar la tarea?",
            width: 300,
            height: 200,
            autoOpen: false,
            modal: true,
            buttons: [
                {
                    text: 'Rechazar',
                    click: function() {
                        doChangeTask();
                    }
                },
                {
                    text: 'Cancelar',
                    click: function() { jQuery(this).dialog("close"); }
                }
            ]
    });
});

function doChangeTask()
{
    if( jQuery.trim(jQuery( "#frmChangeTask #motive" ).val()) == '' )
    {
        alert("Por favor, introduzca un motivo de rechazo o pulse Cancelar");
        return false;
    }

    jQuery("#divChangeTask").dialog("close");

    jQuery.fn.yiiGridView.update('user-project-task-grid',
        {
            type:'POST',
            url : jQuery("#frmChangeTask").attr( 'action' ),
            data: jQuery("#frmChangeTask").serialize(),
            success: function(data)
            {
                jQuery.fn.yiiGridView.update('user-project-task-grid');
				$('#user-project-task-grid').removeClass('grid-view-loading');
            },
            error: function()
            {
				$('#user-project-task-grid').removeClass('grid-view-loading');
            }
        }
    );

    return false;
}

</script>
<?php
/***********************************/
/* End of form for refusing tasks  */
/***********************************/
?>


<script type="text/javascript">
function refuseTask( taskId )
{
    jQuery( "#frmChangeTask #taskId" ).val( taskId );
    jQuery( "#divChangeTask" ).dialog( "open" );
}
</script>
<?php
            $this->widget( 'zii.widgets.grid.CGridView', array(
                'id' => 'user-project-task-grid',
                'dataProvider' => $tasksProvider,
                'filter' => null,
                'selectableRows' => 0,
                'summaryText' => 'Mostrando {start}-{end} resultado(s)',
                'columns' => array(
                    'project.name:text:Proyecto',
                    array(
                        'name' => 'Perfil',
                        'value' => 'WorkerProfiles::toString($data->profile_id)',
                    ),
                    array(
                        'name' => 'Fecha inicial',
                        'value' => 'PHPUtils::convertDateToLongString( $data->date_ini )',
                    ),
                    array(
                        'name' => 'Fecha final',
                        'value' => 'PHPUtils::convertDateToLongString( $data->date_end )',
                    ),
                    array(
                        'sortable' => 'false',
                        'name' => 'Tarea',
                        'value' => '$data->task_description',
                    ),
                    array(
						'header' => 'Ticket',
						'class' => 'CLinkColumn',
						'labelExpression' => '$data->ticket_id',
						'urlExpression' => 'CronosUtils::getTicketUrl($data->ticket_id);',
						'linkHtmlOptions' => array( 'target' => '_blank' ),
                    ),
                    /*
                    array(
                        'name' => 'Precio (€)',
                        'value' => '$data->getCost()',
                        'htmlOptions' => array(
                            'style' => 'text-align: right',
                        )
                    ),*/
                    // Duration instead of cost!!
                    array(
                        'name' => 'Duraci&ograve;n (horas)',
                        'value' => '$data->getFormattedDuration()',
                        'htmlOptions' => array(
                            'style' => 'text-align: center',
                        ),
                    ),
                    /*
                    array(
                        'header' => '',
                        'value' => 'CHtml::link( "Rechazar", "#", array(
                                "onclick" => "refuseTask($data->id);".
                                "class" => "taskRefuseLink",
                            ) );',
                        'value' => 'CHtml::link( "Rechazar", "#", array() )',
                        'htmlOptions' => array(
                            'style' => 'text-align: center',
                        ),
                        //'linkHtmlOptions' => array(
                        //    'class' => 'taskRefuseLink',
                        //),
                    ),*/
                    /*
                    array(
                        'header' => '',
                        'class' => 'CLinkColumn',
                        'labelExpression' => '($data->canRefuse())? "Rechazar":""',
                        'urlExpression' => '($data->canRefuse())? "javascript:refuseTask($data->id)":""',
                        'htmlOptions' => array(
                            'style' => 'text-align: center',
                        ),
                        'linkHtmlOptions' => array(
                            'class' => 'taskRefuseLink',
                        ),
                    ),*/
                ),
                    ) );
?>

            <div class="box" style="text-align:center; margin-top: 15px">
                Total horas: <strong> <?php echo $projectHours ?></strong>
<?php if( Yii::$app->user->isAdmin() ) {?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Total coste: <strong> <?php echo $projectPrice . ' €' ?></strong>

<?php } ?>
</div>