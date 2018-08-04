<h1>Aprobar Horas</h1>

<?php
// Required elements
assert( isset( $tasksProvider ) );

$form = $this->beginWidget( 'CActiveForm', [
            'action' => '',
            'method' => 'post'
                ] );

/* SEARCH FORM */
Yii::$app->controller->renderPartial( '/userProjectTask/_searchForm',
        [
            'taskSearch' => $taskSearch,
            'projectsProvider' => $projectsProvider,
            'searchFieldsToHide' => $searchFieldsToHide,
            'usersProvider' => $usersProvider,
            'managersProvider' => $managersProvider,
            'projectStatus' => (!isset($projectStatus))?NULL : $projectStatus,
            'projectStatusCom' => (!isset($projectStatusCom))?NULL : $projectStatusCom,
            'onlyManagedByUser' => $onlyManagedByUser,
            'projectImputetypes' => $projectImputetypes,
            'form' => $form,
        ] );

/* END SEARCH FORM */

?>

<script type="text/javascript">
    
    jQuery(document).ready(function() {
        $('#approve_button').click(function() {
            return isAnyChecked();
        });
        
        $('#select_all').click(function() {
            $('input[type=checkbox]').each(function () {
                this.checked = !this.checked;
             });
            return false;
        });
        
        $( "label[for^='labcomment']" ).click(function() {
            var name = $(this).attr("for").replace("lab", "");
            var id = $(this).attr("for").replace("lab", "").replace("[", "").replace("]", "");
            if ($('#' + id).length == 0) {
                $(this).after("<textarea id='" + id + "' name='" + name + "'>" + $(this).html() + "</textarea>");
                $( "#" + id ).focus(function()
                {
                    /*to make this flexible, I'm storing the current width in an attribute*/
                    $(this).attr('data-default_width', $(this).width());
                    $(this).attr('data-default_height', $(this).height());
                    $(this).animate({ width: 350, height: 80 }, 'slow');
                }).blur(function()
                {
                    /* lookup the original width */
                    var w = $(this).attr('data-default_width');
                    var h = $(this).attr('data-default_height');
                    $(this).animate({ width: w, height: h }, 'slow');            
                });
                $(this).hide();
            }
        });
        
        $( "label[for^='labticket']" ).click(function() {
            var name = $(this).attr("for").replace("lab", "");
            var id = $(this).attr("for").replace("lab", "").replace("[", "").replace("]", "");
            if ($('#' + id).length == 0) {
                $(this).after("<textarea id='" + id + "' name='" + name + "'>" + $(this).html() + "</textarea>");
                $( "#" + id ).keyup(validateTextarea);
                $( "#" + id ).focus(function()
                {
                    /*to make this flexible, I'm storing the current width in an attribute*/
                    $(this).attr('data-default_width', $(this).width());
                    $(this).attr('data-default_height', $(this).height());
                    $(this).animate({ width: 70, height: 20 }, 'slow');
                }).blur(function()
                {
                    /* lookup the original width */
                    var w = $(this).attr('data-default_width');
                    var h = $(this).attr('data-default_height');
                    $(this).animate({ width: w, height: h }, 'slow');            
                });
                $(this).hide();
            }
        });
        
        $('#TaskSearch_dateIni').change(function() {
                            $.get('<?php echo $this->createUrl( 'AJAX/retrieveWorkers' ) ?>',
                                        {
						startFilter: function() {
                                                    var startDate = "";
                                                    //Búsqueda en Consultar horas
                                                    if ($("#TaskSearch_dateIni").val() != undefined) {
                                                        startDate = $("#TaskSearch_dateIni").val();
                                                        //Busqueda en proyectos
                                                    }
                                                    return startDate;
                                                },
                                                endFilter: function() {
                                                    var endDate = "";
                                                    if ($("#TaskSearch_dateEnd").val() != undefined) {
                                                        endDate = $("#TaskSearch_dateEnd").val();
                                                    } 
                                                    return endDate;
                                                },
                                                selectWorkersPrompt: 'Todos', 
                                                onlyManagedByUser: "0"
					},
                                            function(result){
                                                $("#creator").html(result);
                                            });
                                        });
                                        
        $('#TaskSearch_dateEnd').change(function() {
                            $.get('<?php echo $this->createUrl( 'AJAX/retrieveWorkers' ) ?>',
                                        {
						startFilter: function() {
                                                    var startDate = "";
                                                    //Búsqueda en Consultar horas
                                                    if ($("#TaskSearch_dateIni").val() != undefined) {
                                                        startDate = $("#TaskSearch_dateIni").val();
                                                        //Busqueda en proyectos
                                                    }
                                                    return startDate;
                                                },
                                                endFilter: function() {
                                                    var endDate = "";
                                                    if ($("#TaskSearch_dateEnd").val() != undefined) {
                                                        endDate = $("#TaskSearch_dateEnd").val();
                                                    } 
                                                    return endDate;
                                                },
                                                selectWorkersPrompt: 'Todos', 
                                                onlyManagedByUser: "0"
					},
                                            function(result){
                                                $("#creator").html(result);
                                            });
                                        });
        
        $("select").filter(".company").each(function() {
            //alert($(this).attr("id"));
            $(this).change(function() {
                //var options = new Object();
                
		//options['companyIdInputSelector'] = '#' + $(this).attr("id");
                var companySelected = $(this).val();
		var destinationComponent = ('#' + $(this).attr("id")).replace("pc", "pj");
                //options['companyNameInputSelector'] = $("#" + $(this).attr("id") + " option[value='" + $(this).val() + "']").text();
		
                
                jQuery.ajax( {
                    'url':'<?php echo $this->createUrl( 'AJAX/retrieveProjectsFromCustomerIdAsListOptions' ) ?>',
                    'data': {
                        customerId: companySelected,
                        startFilter: function() {
                            var startDate = "";
                            //Búsqueda en Consultar horas
                            if ($("#TaskSearch_dateIni").val() != undefined) {
                                startDate = $("#TaskSearch_dateIni").val();
                                //Busqueda en proyectos
                            }
                            return startDate;
                        },
                        endFilter: function() {
                            var endDate = "";
                            if ($("#TaskSearch_dateEnd").val() != undefined) {
                                endDate = $("#TaskSearch_dateEnd").val();
                            } 
                            return endDate;
                        },
                        selectProjectPrompt: 'Todos',
                        onlyManagedByUser: "0",
                        onlyUserEnvolved: "1"
                    },
                    'dataType':'html',
                    'cache':false,
                    'success':function(html){
                        $(destinationComponent).html(html);
                        //jQuery(options['companyIdInputSelector']).attr('value', companyId);
                    },
                    'error':function(){
                        alert("Error retrieving projects");
                    },
                    'complete':function(){
                    }
                });
            });
         });
    });
    
    
    
    function isAnyChecked()
    {
        if( jQuery("input:checkbox:checked").length == 0 )
        {
            alert( "Seleccione alguna tarea para aprobar" );
            return false;
        }
        else
            return true;
    }
    
    
</script>
<?php

$columns = array(
	'worker.name:text:Usuario',
        'managerName:text:Manager',
	array(
                'class' => 'DropDownColumn',
		'header' => 'Cliente',
                'name' =>  '"pc[".$data->id."]"',
                'selected' => '$data->project->company->id',
		'selectData' => 'ServiceFactory::createCompanyService()->findCustomerForDropdown($data->project->company->id,Yii::$app->user)',
		'selectClass' => 'company'
	),
	array(
		'class' => 'DropDownColumn',
		'header' => 'Proyecto',
		'name' =>  '"pj[".$data->id."]"',
		'selected' => '$data->project_id',
		'selectData' => 'ServiceFactory::createProjectService()->findProjectsForCustomerAndManagerForDropdown($data->project_id,Yii::$app->user, false)',
		'selectClass' => 'project'
	),
        array(
		'class' => 'DropDownColumn',
		'header' => 'Tipo de imputación',
		'name' =>  '"pti[".$data->id."]"',
		'selected' => '$data->imputetype_id',
		'selectData' => 'ServiceFactory::createImputetypeService()->findImputetypesFromDropdown($data->project_id)',
		'selectClass' => 'imputetype'
	),
	array(
		'class' => 'DropDownColumn',
		'header' => 'Perfil',
		'name' =>  '"pr[".$data->id."]"',
		'selected' => '$data->profile_id',
		'selectData' => 'WorkerProfiles::getDataForDropDown()',
	),
	array(
		'header' => 'Horas',
		'value' => '$data->getFormattedHourRange()',
		'htmlOptions' => array(
			'style' => 'text-align: center',
		)
	),
	array(
		'header' => 'Duración',
		'value' => '$data->getFormattedDuration()',
		'htmlOptions' => array(
			'style' => 'text-align: center',
		)
	),
	array(
		'header' => 'Ticket',
                'value'=>'CHTML::label($data->ticket_id, "labticket[".$data->id."]")."&nbsp;".CronosUtils::getTicketLink($data->ticket_id)."" ',
		'type'=>'raw',
                'htmlOptions' => array(
			'style' => 'width: 70px;',
		)
	),
	array(
                'header' => 'Tarea',
		'value'=>'CHTML::label($data->task_description, "labcomment[".$data->id."]")',
                'type'=>'raw'
	),
	array(
		'class' => 'CCheckBoxColumn',
		'checked' => 'false',
		'id' => 'toApprove',
		'htmlOptions' => array(
			'style' => 'text-align: center',
		)
	)
);

if( $tasksProvider->itemCount > 0 )
{
?>
<div style="text-align: center">
<?php echo CHtml::submitButton( 'Aprobar seleccionadas', array(
        'id' => 'approve_button',
        'submit' => '',
        'params' => array( 'doApprove' => '1' )
) ); 
echo "&nbsp;";
    echo CHtml::submitButton( 'Select all', array(
                'id' => 'select_all' 
        ) ); 
?>
</div>
<?php
}
/**
 * Add hours column with desired format
 */
if(Yii::$app->user->hasDirectorPrivileges()) {
	$day = array(
            'header' => 'Día',
			'class' => 'CLinkColumn',
			'labelExpression' => '$data->frm_date_ini',
            'urlExpression' =>
				'Yii::$app->createUrl("userProjectTask/calendar", array("timestamp" => $data->date_ini->getTimestamp(), "user" => $data->user_id))',
            'htmlOptions' => array(
                'style' => 'text-align: center',
            )
        );
} else {
	$day = array(
            'header' => 'Día',
            'value' => '$data->frm_date_ini',
            'htmlOptions' => array(
                'style' => 'text-align: center',
            )
        );
}

array_splice($columns, 4, 0, array($day));

$this->widget( 'zii.widgets.grid.CGridView', array(
    'id' => 'user-project-task-grid',
    'ajaxUpdate' => FALSE,
    'dataProvider' => $tasksProvider,
    'filter' => null,
    'selectableRows' => 2,
    'summaryText' => 'Mostrando {end} de {count} resultado(s)',
    'columns' => $columns,
));
$this->endWidget();

?>
