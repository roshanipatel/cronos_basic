<?php /* * ********** SEARCH FORM  ****************** */ ?>
<?php
/**
 * @param TaskSearch $taskSearch
 * @param Project[] $projectProvider
 * @param User[] $usersProvider
 * @param int[] $searchFieldsToHide @see TaskSearch for field definitions
 * @param bool $onlyManagedByUser
 * @oaran string $projectStatus
 * // Optional
 * @param bool $showExportButton [false]
 * @param CActiveForm $form
 */
use yii\helpers\Html;
use app\models\form\TaskSearch;
use app\models\enums\ProjectCategories;
use app\models\enums\WorkerProfiles;
use app\components\utils\PHPUtils;
?>
<?php
// Required fields
assert(isset($taskSearch));
assert(isset($projectsProvider));
assert(isset($usersProvider));
assert(isset($searchFieldsToHide));
assert(isset($onlyManagedByUser));
assert(isset($projectImputetypes));
//assert(isset($projectStatus));

if(!isset($showExportButton)) {
	$showExportButton = false;
}

$hasToDefineForm = !isset($form);
if($hasToDefineForm) {

	$form = ActiveForm::begin([
                'action' => Yii::$app->urlManager->createUrl($actionURL),
		'method' => 'get',
			]);
}
?>
<div class="row">
	<?php if(!in_array(TaskSearch::FLD_CUSTOMER, $searchFieldsToHide)) {?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'companyName')->label('companyName'); ?>
			<?= $form->field($taskSearch, 'companyId')->hiddenInput(['id'=>'company_id'])->label(false); ?>
			<?= $form->field($taskSearch, 'companyName')->textInput(['class'=>"form-control col-lg-6",'id' => 'company_name']) ;?>
			<span id="loadingCustomers"></span>
		</div>
	</div>
	<?php } ?>
	<?php if(!in_array(TaskSearch::FLD_PROJECT_ID, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'projectId')->label('projectId'); ?>
			<?= $form->field($taskSearch, 'projectId')->dropDownList(\yii\helpers\ArrayHelper::map($projectsProvider, 'id', 'name'),array('id' => 'company_projects','prompt' => 'Todos',)); ?>
			<span id="loadingProjects"></span>
		</div>
	</div>
	<?php }?>
	<?php if(!in_array(TaskSearch::FLD_PROJECT_STATUS, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<label for="projectStatus">Est. proyecto Op.</label>
			 <?= $form->field($taskSearch, 'projectStatus')->dropDownList(ProjectStatus::getDataForDropDown(),array('prompt' => 'Todos')); ?>
		</div>
	</div> 
	<?php } ?>
	<?php if(!in_array(TaskSearch::FLD_PROJECT_STATUS_COM, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<label for="projectStatus">Est. proyecto Com.</label>
			<?= $form->field($taskSearch, 'projectStatusCom')->dropDownList(ProjectStatus::getDataForDropDown(),array('prompt' => 'Todos'));?>
		</div>
	</div>
	<?php } ?>
	<?php if(!in_array(TaskSearch::FLD_PROJECT_CATEGORY, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<label for="projectCategoryType">Cat. proyecto</label>
			<?= $form->field($taskSearch, 'projectCategoryType')->dropDownList(ProjectCategories::getDataForDropDown(),
					array('prompt' => 'Todas'));?>
		</div>
	</div>
	<?php } ?>
	<?php  if(!in_array(TaskSearch::FLD_PROFILE, $searchFieldsToHide)) {?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'profile')->label('profile'); ?>
			<?= $form->field($taskSearch, 'profile')->dropDownList(WorkerProfiles::getDataForDropDown(),array('prompt' => 'Todos'));?>
		</div>
	</div>
	<?php } ?>
	<?php if(!in_array(TaskSearch::FLD_CREATOR, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'creator')->label('creator'); ?>
			<?= $form->field($taskSearch, 'creator')->dropDownList(\yii\helpers\ArrayHelper::map($usersProvider, 'id', 'name'),array('prompt' => 'Todos'));?>
            <span id="loadingWorkers"></span>
		</div>
	</div>
	<?php } ?>
	<?php if(!in_array(TaskSearch::FLD_OWNER, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'owner')->label('owner'); ?>
			<?= $form->field($taskSearch, 'owner')->dropDownList(\yii\helpers\ArrayHelper::map($managersProvider, 'id', 'name'),array('prompt' => 'Todos'));?>
            <span id="loadingWorkers"></span>
		</div>
	</div>
	<?php } ?>
	<?php if(!in_array(TaskSearch::FLD_STATUS, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'status')->label('status'); ?>
			<?= $form->field($taskSearch, 'status')->dropDownList(TaskStatus::getDataForDropDown(),array('prompt' => 'Todos'));?>
		</div>
	</div>
	<?php } ?>
</div>
<div class="row">
	<?php  if(!in_array(TaskSearch::FLD_DATE_INI, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?php $taskSearch->dateIni = PHPUtils::removeHourPartFromDate($taskSearch->dateIni); ?>
			<?= $form->field($taskSearch, 'dateIni')->label('dateIni'); ?>
			<?= $form->field($taskSearch, 'dateIni')->textInput(array('maxlength' => 16 , 'id'=>'TaskSearch_dateIni'));?>
		</div>
	</div>
	<?php } ?>
	<?php  if(!in_array(TaskSearch::FLD_DATE_END, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?php $taskSearch->dateEnd = PHPUtils::removeHourPartFromDate($taskSearch->dateEnd);?>
			<?= $form->field($taskSearch, 'dateEnd')->label('dateEnd'); ?>
			<?= $form->field($taskSearch, 'dateEnd')->textInput(array('maxlength' => 16 , 'id'=>'TaskSearch_dateEnd'));?>
		</div>
	</div>
	<?php } ?>
	<?php  if(!in_array(TaskSearch::FLD_TICKET, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'tickedId')->label('tickedId'); ?>
			<?= $form->field($taskSearch, TaskSearch::FLD_NAME_TICKET)->textInput(['class'=>"form-control col-lg-6"]) ;?>
		</div>
	</div>
	<?php } ?>
	<?php  if(!in_array(TaskSearch::FLD_DESCRIPTION, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'description')->label('description'); ?>
			<?= $form->field($taskSearch, TaskSearch::FLD_NAME_DESCRIPTION)->textInput(['class'=>"form-control col-lg-6"]) ;?>
		</div>
	</div>
	<?php } ?>
	<?php  if(!in_array(TaskSearch::FLD_IS_EXTRA, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'isExtra')->label('isExtra'); ?>
			<?= $form->field($taskSearch, 'isExtra')->dropDownList(TaskSearch::getDropdownForFlags(),array('prompt' => 'Todos'));?>
		</div>
	</div>
	<?php } ?>
	<?php  if(!in_array(TaskSearch::FLD_IS_BILLABLE, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'isBillable')->label('isBillable'); ?>
			<?= $form->field($taskSearch, 'isBillable')->dropDownList(TaskSearch::getDropdownForFlags(),array('prompt' => 'Todos'));?>
		</div>
	</div>
	<?php } ?>
	<?php  if(!in_array(TaskSearch::FLD_IMPUTE_TYPE, $searchFieldsToHide)) { ?>
	<div class="col-lg-3">
		<div class="form-group">
			<?= $form->field($taskSearch, 'imputetype')->label('imputetype'); ?>
			<?= $form->field($taskSearch, 'imputetype')->dropDownList(\yii\helpers\ArrayHelper::map($projectImputetypes, 'id', 'name'),array( 'multiple' => 'multiple',
                    'id' => 'imputetype_projects'));?>
		</div>
	</div>
	<?php } ?>
</div>
<div class="row">
	<div class="col-lg-12">
		<script type="text/javascript">
			function taskSearch( frm )
			{
				frm.action = '';
				frm.target = '_self';
				return true;
			}
		</script>
		<?php echo Html::submitButton('Buscar', array('onClick' => 'return taskSearch( this.form );'));?>
		<?php if($showExportButton) { ?>
			<script type="text/javascript">
				function exportToCSV( frm )
				{
					frm.action = '<?php echo Yii::$app->urlManager->createUrl('user-project-task/export-toCSV'); ?>';
					frm.target = '_blank';
					return true;
				}
			</script>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php echo Html::submitButton('Exportar a CSV',array('onClick' => 'return exportToCSV( this.form );'));?>
		<?php } ?>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready((function() {
		var dtSelector = 'input[id^="TaskSearch_date"]';
		if( jQuery(dtSelector).length != 2 )
		{
			alert( 'Invalid number of datepickers!' );
			return false;
		}
		jQuery( dtSelector )
		.attr('readonly', 'readonly')
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
</script>
<?php
$this->render('/userProjectTask/_projectsFromCustomerAutocomplete', [
	'projectStatus' => (!isset($projectStatus))?NULL : $projectStatus,
        'projectStatusCom' => (!isset($projectStatusCom))?NULL : $projectStatusCom,
	'onlyManagedByUser' => false, //$onlyManagedByUser,
        'onlyUserEnvolved' => true
]);
?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		var options = new Object();
		options['companyIdInputSelector'] = '#company_id';
		options['companyNameInputSelector'] = '#company_name';
		options['projectSelectSelector'] = '#company_projects';
                options['workerSelectSelector'] = '#TaskSearch_creator';
                options['managerSelectSelector'] = '#TaskSearch_owner';
		defineAutocompleteCustomers( options );
	});
        
        
        function validateTextarea() {
            var errorMsg = "El valor introducido tiene que ser un entero.";
            var textarea = this;
            var pattern = new RegExp('^' + $(textarea).attr('pattern') + '$');
            // check each line of text
            $.each($(this).val().split("\n"), function () {
                // check if the line matches the pattern
                var hasError = !this.match(pattern);
                if (typeof textarea.setCustomValidity === 'function') {
                    textarea.setCustomValidity(hasError ? errorMsg : '');
                } else {
                    // Not supported by the browser, fallback to manual error display...
                    $(textarea).toggleClass('error', !!hasError);
                    $(textarea).toggleClass('ok', !hasError);
                    if (hasError) {
                        $(textarea).attr('title', errorMsg);
                    } else {
                        $(textarea).removeAttr('title');
                    }
                }
                return !hasError;
            });
        }
    
</script>
<?php
if($hasToDefineForm) $this->endWidget();
?>
<?php /* * ********** END SEARCH FORM  ****************** */ ?>
