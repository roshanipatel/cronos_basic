<?php
use yii\helpers\Json;
use yii\helpers\Html; 
use yii\helpers\ArrayHelper;

use app\controllers\UserProjectTaskController;
use app\models\enums\ProjectStatus;

/**
 * Parameters:
 * @param bool $showExtendedFields
 * @param bool $showDate
 * @param bool $isWorker
 * @param UserProjectTask $model
 * @param array[] $workers
 */

//$cs = Yii::$app->clientScript;
$this->registerCssFile( 'css/plugins/jquery.weekcalendar.css' );
$this->registerCssFile( 'css/plugins/timePicker.css' );
$this->registerJsFile( 'js/plugins/jquery.weekcalendar.js');
$this->registerJsFile( 'js/plugins/jquery.timePicker.min.js');
?>

<script type="text/javascript">
    function updateComplete(){
        ajaxSavingTask.hide();
        updatingTask = false;
        calendar.weekCalendar('option', 'readonly', false);
    }

    var updatingTask = false;

    function submitAutomaticAJAXRequest(isDelete, url, data){
        
        updatingTask = true;  
        jQuery.post( url, data,
            function(result){
                var resultObj = jQuery.parseJSON(result);
                
                if( resultObj['code'] == "<?php echo UserProjectTaskController::OP_RESULT_OK ?>" ){
                    calendar.weekCalendar("refresh");
                    // Dialog close takes place on end of refresh (calendar event)
                }
                else if ( resultObj['code'] == "<?php echo UserProjectTaskController::OP_RESULT_ERROR ?>" ){
                    jQuery('#user-task-result').html(resultObj['msg']);
                    updateComplete();
                }
            }
        ).error(function(){
            jQuery('#user-task-result').html('<div class="errorSummary-short">Error interno del servidor</div>');
            updateComplete();
        });
    }
    
    function submitAJAXRequest(isDelete, url, data){
        if( updatingTask ){
            return;
        } 
        if(( ! isDelete ) && ( ! validateTaskForm() ) ){
            return;
        }
        
        updatingTask = true;
        ajaxSavingTask.show();
        jQuery.post( url, data,
            function(result){
                var resultObj = jQuery.parseJSON(result);
                if( resultObj['code'] == "<?php echo UserProjectTaskController::OP_RESULT_OK ?>" ){
                    calendar.weekCalendar("refresh");
                    // Dialog close takes place on end of refresh (calendar event)
                }
                else if ( resultObj['code'] == "<?php echo UserProjectTaskController::OP_RESULT_ERROR ?>" ){
                    jQuery('#user-task-result').html(resultObj['msg']);
                    updateComplete();
                }
            }
        ).error(function(){
            jQuery('#user-task-result').html('<div class="errorSummary-short">Error interno del servidor</div>');
            updateComplete();
        });
    }
    
    function submitTaskForm(isClone) {
        if(isClone) {
            
            jQuery('#user-project-task-form').find("#UserProjectTask_id").val("");
            
            var sHoraMinutoInicioActual = jQuery('#user-project-task-form').find("#UserProjectTask_frm_hour_ini").val();
            var aHoraInicialParts = sHoraMinutoInicioActual.split(":");
            
            var sHoraMinutoFinalActual = jQuery('#user-project-task-form').find("#UserProjectTask_frm_hour_end").val();
            var aHoraFinalParts = sHoraMinutoFinalActual.split(":");
            
            var novaHoraInicio = parseInt(aHoraFinalParts[0]);
            var sHoraMinuto = "";
            if(novaHoraInicio < 10) {
                sHoraMinuto = "0" + novaHoraInicio + ":" + aHoraInicialParts[1];
            } else if (novaHoraInicio < 24) {   
                sHoraMinuto = novaHoraInicio + ":" + aHoraInicialParts[1];
            }
            jQuery('#user-project-task-form').find("#UserProjectTask_frm_hour_ini").val(sHoraMinuto);
            
            var novaHoraFinal = parseInt(aHoraFinalParts[0]) + 1;
            var sHoraMinuto = "";
            if(novaHoraFinal < 10) {
                sHoraMinuto = "0" + novaHoraFinal + ":" + aHoraFinalParts[1];
            } else if (novaHoraInicio < 24) {   
                sHoraMinuto = novaHoraFinal + ":" + aHoraFinalParts[1];
            }
            jQuery('#user-project-task-form').find("#UserProjectTask_frm_hour_end").val(sHoraMinuto);
            
            jQuery('#user-project-task-form').find("#UserProjectTask_status").val("NEW");
            
            jQuery('#UserProjectTask_frm_date_ini').val(jQuery('#frm_date_ini2').val());
            jQuery('#UserProjectTask_frm_date_end').val(jQuery('#UserProjectTask_frm_date_ini').val());
            submitAJAXRequest( false, "<?php echo Yii::$app->urlManager->createUrl( 'userProjectTask/saveTask' ); ?>",
            jQuery('#user-project-task-form').serializeArray() );
            
        } else {
            jQuery('#UserProjectTask_frm_date_ini').val(jQuery('#frm_date_ini2').val());
            jQuery('#UserProjectTask_frm_date_end').val(jQuery('#UserProjectTask_frm_date_ini').val());
            submitAJAXRequest( false, "<?php echo Yii::$app->urlManager->createUrl( 'userProjectTask/saveTask' ); ?>",
            jQuery('#user-project-task-form').serializeArray() );
        }
    }
    
    function submitAutomaticTaskForm(calEventId, calEventDateIni, calEventDateFin) {
        
        var updatedTask = new Object();
        updatedTask['UserProjectTask'] = new Object();
        
        updatedTask['UserProjectTask']['id'] = calEventId;
        updatedTask['UserProjectTask']['frm_date_ini'] = calEventDateFin.toString('dd/MM/yyyy');
        updatedTask['UserProjectTask']['frm_date_end'] = calEventDateFin.toString('dd/MM/yyyy');
        updatedTask['UserProjectTask']['frm_hour_ini'] = calEventDateIni.toString('HH:mm');
        updatedTask['UserProjectTask']['frm_hour_end'] = calEventDateFin.toString('HH:mm');
        
        submitAutomaticAJAXRequest( false, "<?php echo Yii::$app->urlManager->createUrl( 'userProjectTask/automaticSaveTask' ); ?>",
            updatedTask );
    }

    function deleteTask(taskId){
        submitAJAXRequest( true, "<?php echo Yii::$app->urlManager->createUrl( 'userProjectTask/delete' ); ?>", {id: taskId} );
    }
</script>
<script type="text/javascript">

	var DEFAULT_IS_EXTRA = false;
	var DEFAULT_IS_BILLABLE = true;

	function setYiiCheckbox(checkboxName, value){
		if(jQuery(checkboxName).length == 0){
			return;
		} else if(jQuery(checkboxName).length > 1){
			alert("More than 1 element with name " + checkboxName);
			return;
		}
		if(typeof(value) == "string"){
			value = parseInt(value);
		}
		if(typeof(value) == "number"){
			value = value != 0;
		}
		var checkbox = jQuery(checkboxName)[0];
		var currentCheckboxValue = (checkbox.checked === true);
		if(currentCheckboxValue != value){
			jQuery(checkboxName).trigger("click");
		}
	}

    function resetTaskForm(readonly){
        ajaxSavingTask.hide();
        jQuery('#user-task-result').html('');
        jQuery('#user-project-task-form').each(function(){
            this.reset();
        });
		setYiiCheckbox('#UserProjectTask_is_extra', DEFAULT_IS_EXTRA);
		setYiiCheckbox('#UserProjectTask_is_billable', DEFAULT_IS_BILLABLE);
        // Make form readonly or not
        if( readonly ){
            jQuery('#user-project-task-form').
                find('[name!="frm_date_ini2"]').
				// Firefox doesn't seem to like a disabled checkbox to trigger the click :(
                find('[type!="checkbox"]').
                attr('disabled','disabled');
        }
        else {
            jQuery('#user-project-task-form').
                find('[name!="frm_date_ini2"]').
                removeAttr('disabled');
        }
    }

    function setTaskFormInitValues(calEvent){
        var taskId = (calEvent['id'] == undefined)? '' : calEvent['id'];
        jQuery('#UserProjectTask_id').val(taskId);
        jQuery('#UserProjectTask_user_id').val(jQuery('#user_selector').val());
        jQuery('#frm_date_ini2').val(calEvent.start.toString('dd/MM/yyyy'));
        jQuery('#UserProjectTask_frm_hour_ini').val(calEvent.start.toString('HH:mm'));
        jQuery('#UserProjectTask_frm_hour_end').val(calEvent.end.toString('HH:mm'));
		var isExtra = (calEvent['is_extra'] != undefined) ? (calEvent['is_extra']) : DEFAULT_IS_EXTRA;
		var isBillable = (calEvent['is_billable'] != undefined) ? (calEvent['is_billable']) : DEFAULT_IS_BILLABLE;
		setYiiCheckbox('#UserProjectTask_is_extra', isExtra);
		setYiiCheckbox('#UserProjectTask_is_billable', isBillable);
        if( calEvent['id'] !== undefined ){
<?php
if( $showExtendedFields ) {
    ?>
                    jQuery('#UserProjectTask_status').val(calEvent.status);
                    jQuery('#UserProjectTask_profile_id').val(calEvent.profile_id);
    <?php
}
?>
            jQuery('#UserProjectTask_task_description').val(calEvent.description);
            jQuery('#UserProjectTask_ticket_id').val(calEvent.ticket);
            jQuery('#company_name').val(calEvent.customer_name);
            if( calEvent.readonly ){
                jQuery("#customer_projects")
                    .empty()
                    .append('<option value="">' + calEvent.project_name + '</option>');
            
                jQuery("#imputetype_projects")
                    .empty()
                    .append('<option value="">' + calEvent.project_name + '</option>');
            } else {
                var url = '<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveProjectsFromCustomerIdAsListOptions' ) ?>';
                var data = {
                    customerId: calEvent.customer_id,
                    startFilter: calEvent.start.toString('dd/MM/yyyy'),
                    endFilter: calEvent.end.toString('dd/MM/yyyy'),
                    selectProjectPrompt : 'Seleccione proyecto...',
                    projectStatus : '<?php echo ProjectStatus::PS_OPEN ?>'
                };
                populateProjectsSelect(url,data,calEvent.project_id, calEvent.project_name);
                
                var url = '<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveImputetypesFromProjectAsListOptions' ) ?>';
                var data = {
                    projectId: calEvent.project_id,
                    //selectProjectPrompt : 'Seleccione un tipo de imputación...'
                };
                populateImputetypeSelect(url,data,calEvent.imputetype_id, calEvent.imputetype_name);
            }
        }
        jQuery('#company_name').focus();
    }
</script>
<script type="text/javascript">
    function displayFormForEvent(calEvent, element, dayFreeBusyManager, calendar, mouseupEvent, readonly){
        
        var isNew = calEvent['id'] === undefined;
        var buttonName = isNew ? 'Crear' : 'Guardar';
        var dialogButtons = {};
        var dialogTitle;
        if( readonly ){
            dialogTitle = 'Consultar tarea';
        } else {
            dialogButtons[buttonName] = function() {
                submitTaskForm(false);
            };
            if( ! isNew ){
                dialogButtons["Copiar"] = function() {
                    submitTaskForm(true);
                };
                dialogButtons["Borrar"] = function() {
                    deleteTask(calEvent['id']);
                };
                dialogTitle = 'Actualizar tarea';
                $("#frm_date_ini2").removeAttr('disabled');
            }
            else{
                dialogTitle = 'Nueva tarea';               
                $("#frm_date_ini2").attr('disabled', 'disabled');
            }
        }
        dialogButtons["Cancelar"] = function() {
            dialogContent.dialog("close");
        };
        
        dialogContent.dialog({
            modal:true,
            width: 400,
            maxWidth: 500,
            title: dialogTitle,
            close: function() {
                jQuery('#user-task-result').html('');
                ajaxSavingTask.hide();
                dialogContent.dialog("destroy");
                dialogContent.hide();
                calendar.weekCalendar("removeUnsavedEvents");
            },
            buttons: dialogButtons,
            open: function(){
                // Reset form
                resetTaskForm(readonly);
                setTaskFormInitValues(calEvent);
                if( readonly ){
                    jQuery('#user-project-task-form').each(function(){
                    });
                }
            }
        }).show();
        // Make buttons look nice
        jQuery( "button:ui-button" ).css('font-size', '0.8em')

    }
</script>
<script type="text/javascript">

    calendar = jQuery("#task-calendar");
    dialogContent = jQuery("#task-input-form");
    
	jQuery(document).ready((function() {
		jQuery( 'input[id^="frm_date_ini2"]' )
		.datepicker(
		{
			'dateFormat': 'dd/mm/yy',
			'timeFormat': 'hh:mm',
			'monthNames': [ 'Enero', 'Febrero', 'Marzo', 'Abril',
				'Mayo', 'Junio', 'Julio', 'Agosto',
				'Setiembre', 'Octubre', 'Noviembre', 'Diciembre' ],
			'showAnim': 'fold',
			'type' : 'date',
			'dayNamesMin' : ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa' ],
			'firstDay' : 1,
			'currentText' : 'Hoy',
			'closeText' : 'Listo',
			'showButtonPanel' : true
		});
                
		jQuery( "div.ui-datepicker" ).css("font-size", "80%");
	}));
    

    jQuery(document).ready(function(){
        var hoursSelector  = 'input[id^="UserProjectTask_frm_hour_"]';
        jQuery(hoursSelector).timePicker({
            show24Hours: true,
            separator: ':',
            step: 30
        });
<?php
	// By default
	if($isWorker) {
		$userIdStr = Yii::$app->user->id;
	} else {
		$userIdStr = 'jQuery("#user_selector").val()';
	}
        $companyIdStr = 'jQuery("#company_selector").val()';
	if(empty($showDate)) {
		$dateToLoad = 'new Date()';
	} else {
		$dateToLoad = "new Date($showDate * 1000)";
	}
?>
        var showDate = <?php echo $dateToLoad ?>;
        calendar.weekCalendar({
            data: function(start,end,callback){
                calendar.weekCalendar("clear");
                jQuery.getJSON('<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveTasksForUserAndWeek' ) ?>',
					{
						'date': start.toString('dd/MM/yyyy'),
						'userId' : <?php echo $userIdStr ?>,
                                                'companyId' : <?php echo $companyIdStr ?>
					},
					function(result){
						callback(result);
					}
				);
                        <?php
                        if(!$isWorker) {
                            ?>
                            var selectedUser = jQuery("#user_selector").val();
                            $.get('<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveWorkers' ) ?>',
                                        {
						startFilter: start.toString('dd/MM/yyyy'),
                                                endFilter: end.toString('dd/MM/yyyy'),
                                                selectWorkersPrompt: 'Todos', 
                                                onlyManagedByUser: "0"
					},
                                            function(result){
                                                $("#user_selector").html(result);
                                                $("#user_selector").val(selectedUser);
                                            });
                            <?php
                        }
                        ?>
                        var selectedCompany = jQuery("#company_selector").val();
                            $.get('<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveCompanies' ) ?>',
                                        {
						startFilter: start.toString('dd/MM/yyyy'),
                                                endFilter: end.toString('dd/MM/yyyy'),
                                                selectWorkersPrompt: 'Todos', 
                                                worker: jQuery("#user_selector").val()
					},
                                            function(result){
                                                $("#company_selector").html(result);
                                                $("#company_selector").val(selectedCompany);
                                            });
                        
			},
            height: function(calendar){
                return 600;
            },
            timeslotsPerHour: 4,
            dateFormat: 'd M Y',
            date: showDate,
            timeFormat: null,
            alwaysDisplayTimeMinutes: true,
            use24Hour: true,
            daysToShow: 7,
            firstDayOfWeek: 1,
            useShortDayNames: false,
            timeSeparator: ' a ',
            startParam: 'start',
            endParam: 'end',
            businessHours: {start: 8, end: 18, limitDisplay: false},
            newEventText: 'Nueva tarea',
            timeslotHeight: 20,
            defaultEventLength: 2,
            minDate: null,
            maxDate: null,
            buttons: true,
            buttonText: {
                today: 'Hoy',
                lastWeek: 'Semana anterior',
                nextWeek: 'Semana posterior'
            },
            switchDisplay: {},
            scrollToHourMillis: 500,
            allowCalEventOverlap: false,
            overlapEventsSeparate: false,
            totalEventsWidthPercentInOneColumn : 100,
            readonly: false,
            allowEventCreation: true,
            draggable: function(calEvent, element) {
                return true;
            },
            resizable: function(calEvent, element) {
                return true;
            },
            keypress: function(calEvent, element) {
                
            },
                     
            /* Click de la tasca */
            eventClick: function(calEvent, element, dayFreeBusyManager, calendar, clickEvent) {
                
                $(element).dblclick(function() {               
                    displayFormForEvent(calEvent,element,dayFreeBusyManager,calendar,clickEvent,calEvent.readonly);
                });
                
            },
            
            eventRender: function(calEvent, element) {
                if(calEvent.readonly ) {
                    element.find(".wc-time").css({backgroundColor: "#999", border:"1px solid #888"});
                }
                element.attr("title", getCalEventDesc(calEvent));
                element.css("backgroundColor", "#" + companyColors[calEvent.customer_id]);
                return element;
            },
            eventAfterRender: function(calEvent, element) {
                return element;
            },
            eventRefresh: function(calEvent, element) {
                return element;
            },
            eventDrag: function(calEvent, element) {
            },
            eventDrop: function(calEvent, element) {         
               submitAutomaticTaskForm(calEvent.id, calEvent.start, calEvent.end);
            },
            eventResize: function(calEvent, element) {
                submitAutomaticTaskForm(calEvent.id, calEvent.start, calEvent.end);
            },
    
            eventNew: function(calEvent, element, dayFreeBusyManager, calendar, mouseupEvent) {
                displayFormForEvent(calEvent,element,dayFreeBusyManager,calendar,mouseupEvent,false);    
            },

            eventMouseover: function(calEvent, $event) {
            },
            eventMouseout: function(calEvent, $event) {
            },
            
            /*eventMouseDown: function(calEvent, $event) {
                alert("click");
            },*/
            
            calendarBeforeLoad: function(calendar) {
<?php
// Show users dropdown if admin
if( ! $isWorker ) {
	if($showUser == NULL){
		$userIdStr = Yii::$app->user->id;
	} else{
		$userIdStr = $showUser;
	}
	$usersSelect = Html::dropDownList('user_selector', $userIdStr, ArrayHelper::map( $workers, 'id', 'name' ), array(
				'onchange' => 'updateTasks()',
				'style' => 'font-size: 1.2em',
				'id' => 'user_selector',
				'name' => 'user_selector'
			) );
	// Remove new line characters
	$usersSelect = preg_replace( '/\n+/', '', $usersSelect );
?>
				var userSelector = jQuery('#user_selector');
				if( userSelector.length == 0 ) {
					var users = '<?php echo $usersSelect; ?>';
					jQuery('div.wc-nav').after('<div style="float:right;margin-top:2px">' + users + '</div>');
				}
<?php } ?>

<?php
$companySelect = Html::dropDownList('company_selector', "", ArrayHelper::map( $customers, 'id', 'name' ), array(
                                'prompt' => 'Todos',
				'onchange' => 'updateTasks()',
				'style' => 'font-size: 1.2em',
				'id' => 'company_selector',
				'name' => 'company_selector'
			) );
	// Remove new line characters
	$companySelect = preg_replace( '/\n+/', '', $companySelect );
?>
				var companySelector = jQuery('#company_selector');
				if( companySelector.length == 0 ) {
					var companies = '<?php echo $companySelect; ?>';
					jQuery('div.wc-nav').after('<div style="float:right;margin-top:2px">' + companies + '</div>');
				}

            $("#company_selector > option").each(function() {
                $(this).attr('style', 'background: #' + companyColors[this.value]);
            });
			},
            calendarAfterLoad: function(calendar) {
                updateComplete();
                dialogContent.dialog("close");
            },
            noEvents: function() {
            },
            eventHeader: function(calEvent, calendar) {
                var options = calendar.weekCalendar('option');
                var one_hour = 3600000;
                var displayTitleWithTime = calEvent.end.getTime() - calEvent.start.getTime() <= (one_hour / options.timeslotsPerHour);
                if (displayTitleWithTime) {
                    return calendar.weekCalendar(
                    'formatTime', calEvent.start) +
                        ': ' + getCalEventDesc(calEvent, 60);
                } else {
                    return calendar.weekCalendar(
                    'formatTime', calEvent.start) +
                        options.timeSeparator +
                        calendar.weekCalendar(
                    'formatTime', calEvent.end);
                }
            },
            eventBody: function(calEvent, calendar) {
                var one_hour = 3600000;
                var displayTitleWithTime = calEvent.end.getTime() - calEvent.start.getTime() / one_hour;
                return getCalEventDesc(calEvent, parseInt((displayTitleWithTime * 60)));
            },
            shortMonths: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'],
            longMonths: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre',
                'Octubre', 'Noviembre', 'Diciembre'],
            shortDays: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
            longDays: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
        });
    });

    // Define update tasks function for when a user is selected
    function updateTasks(){
        //console.log(sel);
        jQuery("#task-calendar").weekCalendar("refresh");
    }

    function getCalEventDesc(calEvent, max){
        var description = (calEvent['customer_name'] + " " + calEvent['project_name'] + " " +
                ((calEvent['project_name'] == undefined) ? '' : calEvent.description));
        if (max) {
            return description.substr(0,max);
        } else {
            return description;
        }
    }
</script>
