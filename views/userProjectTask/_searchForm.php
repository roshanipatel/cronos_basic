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
	$form = $this->beginWidget('CActiveForm', array(
                'action' => $this->createUrl($actionURL),
		'method' => 'get',
			));
}
?>

<table id="tableTaskSearch">
    <tr>
        <?php
    if(!in_array(TaskSearch::FLD_CUSTOMER, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'companyName');
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_PROJECT_ID, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'projectId');
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_PROJECT_STATUS, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo "<label for=\"projectStatus\">Est. proyecto Op.</label>\n";
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_PROJECT_STATUS_COM, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo "<label for=\"projectStatus\">Est. proyecto Com.</label>\n";
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_PROJECT_CATEGORY, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo "<label for=\"projectCategoryType\">Cat. proyecto</label>\n";
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_PROFILE, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'profile');
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_CREATOR, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'creator');
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_OWNER, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'owner');
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_STATUS, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'status');
			echo "</td>\n";
		}
                ?>
    </tr>
    <tr>
        <?php
        
        if(!in_array(TaskSearch::FLD_CUSTOMER, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->hiddenField($taskSearch, 'companyId', array('id' => 'company_id'));
			echo $form->textField($taskSearch, 'companyName', array('id' => 'company_name'));
			echo "<span id=\"loadingCustomers\"></span>\n";
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_PROJECT_ID, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->dropDownList($taskSearch, 'projectId', CHtml::listData($projectsProvider, 'id', 'name'),
					array(
				'id' => 'company_projects',
				'prompt' => 'Todos',
				'style' => 'width: 110px'
			));
			echo "<span id=\"loadingProjects\"></span>\n";
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_PROJECT_STATUS, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->dropDownList($taskSearch, 'projectStatus', ProjectStatus::getDataForDropDown(),
					array(
				'prompt' => 'Todos',
				'style' => 'width: 60px'
			));
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_PROJECT_STATUS_COM, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->dropDownList($taskSearch, 'projectStatusCom', ProjectStatus::getDataForDropDown(),
					array(
				'prompt' => 'Todos',
				'style' => 'width: 60px'
			));
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_PROJECT_CATEGORY, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->dropDownList($taskSearch, 'projectCategoryType', ProjectCategories::getDataForDropDown(),
					array(
				'prompt' => 'Todas',
				'style' => 'width: 80px'
			));
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_PROFILE, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->dropDownList($taskSearch, 'profile', WorkerProfiles::getDataForDropDown(),
					array(
				'prompt' => 'Todos',
				'style' => 'width: 60px'
			));
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_CREATOR, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->dropDownList($taskSearch, 'creator', CHtml::listData($usersProvider, 'id', 'name'),
					array(
				'prompt' => 'Todos',
				'style' => 'width: 90px'
			));
                        echo "<span id=\"loadingWorkers\"></span>\n";
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_OWNER, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->dropDownList($taskSearch, 'owner', CHtml::listData($managersProvider, 'id', 'name'),
					array(
				'prompt' => 'Todos',
				'style' => 'width: 90px'
			));
                        echo "<span id=\"loadingWorkers\"></span>\n";
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_STATUS, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->dropDownList($taskSearch, 'status', TaskStatus::getDataForDropDown(),
					array(
				'prompt' => 'Todos',
				'style' => 'width: 60px'
			));
			echo "</td>\n";
		}
                ?>
    </tr>
    <tr>
		<?php
		if(!in_array(TaskSearch::FLD_DATE_INI, $searchFieldsToHide)) {
			$taskSearch->dateIni = PHPUtils::removeHourPartFromDate($taskSearch->dateIni);
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'dateIni');
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_DATE_END, $searchFieldsToHide)) {
			$taskSearch->dateEnd = PHPUtils::removeHourPartFromDate($taskSearch->dateEnd);
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'dateEnd');
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_TICKET, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'tickedId');
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_DESCRIPTION, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'description');
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_IS_EXTRA, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'isExtra');
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_IS_BILLABLE, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'isBillable');
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_IMPUTE_TYPE, $searchFieldsToHide)) {
			echo "<td class=\"title_search_field\">\n";
			echo $form->labelEx($taskSearch, 'imputetype');
			echo "</td>\n";
		}
		?>
	</tr>
	<tr>
		<?php
		if(!in_array(TaskSearch::FLD_DATE_INI, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->textField($taskSearch, 'dateIni', array(
				'maxlength' => 16,
			));
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_DATE_END, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->textField($taskSearch, 'dateEnd', array(
				'maxlength' => 16,
			));
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_TICKET, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->textField($taskSearch, TaskSearch::FLD_NAME_TICKET);
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_DESCRIPTION, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->textField($taskSearch, TaskSearch::FLD_NAME_DESCRIPTION);
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_IS_EXTRA, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->dropDownList($taskSearch, 'isExtra', TaskSearch::getDropdownForFlags(),
					array(
				'prompt' => 'Todos',
				'style' => 'width: 60px'
			));
			echo "</td>\n";
		}
		if(!in_array(TaskSearch::FLD_IS_BILLABLE, $searchFieldsToHide)) {
			echo "<td>\n";
			echo $form->dropDownList($taskSearch, 'isBillable', TaskSearch::getDropdownForFlags(),
					array(
				'prompt' => 'Todos',
				'style' => 'width: 60px'
			));
			echo "</td>\n";
		}
                if(!in_array(TaskSearch::FLD_IMPUTE_TYPE, $searchFieldsToHide)) {
			echo "<td>\n";
                        echo $form->dropDownList($taskSearch, 'imputetype', CHtml::listData($projectImputetypes, 'id', 'name'),
                                        array(
                                'style' => 'width: 120px',
                                'multiple' => 'multiple',
                                'id' => 'imputetype_projects',
                        ));
			echo "</td>\n";
		}
		?>
	</tr>
	<tr>
		<td colspan="12" align="center">
			<br>
			<script type="text/javascript">
				function taskSearch( frm )
				{
					frm.action = '';
					frm.target = '_self';
					return true;
				}
			</script>
			<?php
			echo CHtml::submitButton('Buscar', array(
				'onClick' => 'return taskSearch( this.form );',
			));
			?>
			<?php if($showExportButton) { ?>
				<script type="text/javascript">
					function exportToCSV( frm )
					{
						frm.action = '<?php echo $this->createUrl('userProjectTask/exportToCSV'); ?>';
						frm.target = '_blank';
						return true;
					}
				</script>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?php
				echo CHtml::submitButton('Exportar a CSV',
						array(
					'onClick' => 'return exportToCSV( this.form );',
				));
				?>
			<?php } ?>
		</td>
	</tr>
</table>
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
$this->renderPartial('_projectsFromCustomerAutocomplete', array(
	'projectStatus' => (!isset($projectStatus))?NULL : $projectStatus,
        'projectStatusCom' => (!isset($projectStatusCom))?NULL : $projectStatusCom,
	'onlyManagedByUser' => false, //$onlyManagedByUser,
        'onlyUserEnvolved' => true
));
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
