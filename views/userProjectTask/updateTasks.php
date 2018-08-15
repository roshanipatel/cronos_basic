<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

?>
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Modificar Horas</h1>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Modificar Horas
            </div>
            <div class="panel-body">
                <?php
                // Required elements
                    assert( isset( $tasksProvider ) );
                    $form = ActiveForm::begin([
                    'action' => '',
                    'method' => 'post'
                        ]);

                    /* SEARCH FORM */
                    $this->render( '/userProjectTask/_searchForm',
                            [
                                'taskSearch' => $taskSearch,
                                'projectsProvider' => $projectsProvider,
                                'searchFieldsToHide' => $searchFieldsToHide,
                                'usersProvider' => $usersProvider,
                                'managersProvider' => $managersProvider,
                                'projectStatus' => (!isset($projectStatus))?NULL : $projectStatus,
                                'onlyManagedByUser' => $onlyManagedByUser,
                                'projectImputetypes' => $projectImputetypes,
                                'form' => $form,
                            ]);

                    /* END SEARCH FORM */

                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function() {
                            $('#approve_button').click(function() {
                                return isAnyChecked();
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
                                    
                                    var innerHtml = $(this).html();
                                    if ($(this).html() == "(Empty)") {
                                        innerHtml = "";
                                    }
                                    
                                    $(this).after("<textarea pattern=\"\d*\" id='" + id + "' name='" + name + "'>" + innerHtml + "</textarea>");
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
                                                $.get('<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveWorkers' ) ?>',
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
                                                $.get('<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveWorkers' ) ?>',
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
                            
                            $('#select_all').click(function() {
                                $('input[type=checkbox]').each(function () {
                                    this.checked = !this.checked;
                                 });
                                return false;
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
                                        'url':'<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveProjectsFromCustomerIdAsListOptions' ) ?>',
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
                    $columns = [
                        ['class' => 'yii\grid\SerialColumn'],
                    	'Usuario',
                        'Manager',
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'Cliente',
                            'selected' => '$data->project->company->id',
                    		'selectData' => 'ServiceFactory::createCompanyService()->findCustomerForDropdown($data->project->company->id,Yii::$app->user)',
                    		'selectClass' => 'company'
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'Proyecto',
                    		'selected' => '$data->project_id',
                    		'selectData' => 'ServiceFactory::createProjectService()->findProjectsForCustomerAndManagerForDropdown($data->project_id,Yii::$app->user, true)',
                    		'selectClass' => 'project'
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'Tipo de imputación',
                    		'selected' => '$data->imputetype->id',
                    		'selectData' => 'ServiceFactory::createImputetypeService()->findImputetypesFromDropdown($data->project_id, $data->imputetype->id)',
                    		'selectClass' => 'imputetype'
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'Perfil',
                    		'selected' => '$data->profile_id',
                    		'selectData' => 'WorkerProfiles::getDataForDropDown()',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'Horas',
		                    'value' => '$data->getFormattedHourRange()',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'Duración',
		                    'value' => '$data->getFormattedDuration()',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'Ticket',
                            'value'=>'CronosUtils::getEditLabel($data->ticket_id, $data->id)."&nbsp;".CronosUtils::getTicketLink($data->ticket_id)',
                        ],
                        [
                             'class' => 'yii\grid\DataColumn',
                             'label' => 'Ticket',
		                     'value'=>'HTML::label($data->task_description, "labcomment[".$data->id."]")',
                           
                        ],
                        [
                    		'class' => 'CCheckBoxColumn',
                    		'checked' => 'false',
                    		'id' => 'toApprove',
                        ],
                    ];

if( $tasksProvider->itemCount > 0 )
{
?>
<div style="text-align: center">
<?php echo Html::submitButton( 'Modificar seleccionadas', array(
        'id' => 'update_button',
        'submit' => '',
        'params' => array( 'doApprove' => '1' )
) ); 
echo "&nbsp;";
    echo Html::submitButton( 'Select all', array(
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
	$day = [
            'class' => 'yii\grid\ActionColumn',
            'label' => 'Día',
			'labelExpression' => '$data->frm_date_ini',
            'urlExpression' =>
				'Yii::$app->createUrl("user-project-task/calendar", array("timestamp" => $data->date_ini->getTimestamp(), "user" => $data->user_id))',
        ];
} else {
	$day = [
            'class' => 'yii\grid\ActionColumn',
            'label' => 'Día',
            'value' => '$data->frm_date_ini',
        ];
}

array_splice($columns, 4, 0, array($day));

echo DataTables::widget([
    'id' => 'dataTables-example',
    //'ajaxUpdate' => FALSE,
    'dataProvider' => $tasksProvider,
    //'filter' => null,
    //'selectableRows' => 2,
    //'summaryText' => 'Mostrando {end} de {count} resultado(s)',
    'columns' => $columns,
]);
$this->endWidget();

?>
</div>
        </div>    
    </div>
</div>