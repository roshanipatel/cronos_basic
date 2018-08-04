<h1>Estado de Proyectos</h1>
<?php /* * ********** SEARCH FORM  ****************** */ ?>
<?php
assert(isset($projectsProvider));
// Required fields
$showExportButton = TRUE;
$searchFieldsToHide = array();
$showManager = Yii::app()->user->hasDirectorPrivileges();
$form = $this->beginWidget('CActiveForm', array(
    'method' => 'get',
    'action' => $this->createUrl('projectOveriew'),
         ));
if ($showManager) {
    $managersProvider = ServiceFactory::createUserService()->findProjectManagers();
} else {
    $managersProvider = array();
}
?>

<table id="tableTaskSearch">
        <tr>
            <td class="title_search_field">Fecha apertura</td>
            <td class="title_search_field">Fecha cierre</td>
            <td class="title_search_field">Cliente</td>
            <td class="title_search_field">Proyecto</td>
            <?php
            if ($showManager) {
                echo '<td class="title_search_field">Manager</td>';
            }
            ?>
            <td class="title_search_field">Est. proyecto Op.</td>
            <td class="title_search_field">Est. proyecto Com.</td>
            <td class="title_search_field">Categoría</td>
            <td class="title_search_field">Informe</td>
        </tr>
        <tr>
            <?php
            echo "<td>\n";
            echo $form->textField($model, 'open_time', array(
                'maxlength' => 20,
            ));
            echo "</td>\n";
            echo "<td>\n";
            echo $form->textField($model, 'close_time', array(
                'maxlength' => 20,
            ));
            echo "</td>\n";
            echo "<td>\n";
            echo $form->hiddenField($model, 'company_id', array('id' => 'company_id'));
            echo $form->textField($model, 'company_name', array(
                'id' => 'company_name',
                'style' => 'width: 160px'
            ));
            echo "<span id=\"loadingCustomers\"></span>\n";
            echo "</td>\n";
            echo "<td>\n";
            echo $form->dropDownList($model, 'id', CHtml::listData($projectsProvider, 'id', 'name'), array(
                'prompt' => 'Todos',
                'style' => 'width: 200px'
            ));
            echo "<span id=\"loadingProjects\"></span>\n";
            echo "</td>\n";
            if ($showManager) {
                echo "<td>\n";
                echo $form->dropDownList($model, 'manager_id', CHtml::listData($managersProvider, 'id', 'name'), array(
                    'prompt' => 'Todos',
                    'style' => 'width: 120px'
                ));
                echo "<span id=\"loadingWorkers\"></span>\n";
                echo "</td>\n";
            }
            echo "<td>\n";
            echo $form->dropDownList($model, 'status', ProjectStatus::getDataForDropDown(), array(
                'prompt' => 'Todos',
                'style' => 'width: 100px'
            ));
            echo "</td>\n";
            echo "<td>\n";
            echo $form->dropDownList($model, 'statuscommercial', ProjectStatus::getDataForDropDown(), array(
                'prompt' => 'Todos',
                'style' => 'width: 100px'
            ));
            echo "</td>\n";
            echo "<td>\n";
            echo $form->dropDownList($model, 'cat_type', ProjectCategories::getDataForDropDown(), array(
                'prompt' => 'Todas',
                'style' => 'width: 150px'
            ));
            echo "</td>\n";
            echo "<td>\n";
            echo $form->dropDownList($model, 'reporting', array("0" => "Con Informe", "1" => "Sin Informe"), array(
                'prompt' => 'Todas',
                'style' => 'width: 90px'
            ));
            echo "</td>\n";
            ?>
    </tr>
    <tr>
        <td class="title_search_field">Tipo imputación</td>        
    </tr>
    <tr>
        <?php
        echo "<td>\n";
        echo $form->dropDownList($model, 'imputetype', CHtml::listData($projectImputetypes, 'id', 'name'),
                        array(
                'style' => 'width: 120px',
                'multiple' => 'multiple',
                'id' => 'imputetype_projects',
        ));
        
        echo "</td>\n";
        ?>
    </tr>
    <tr>
        <td colspan="9" align="center">
            <br>
            <script type="text/javascript">
                function projectSearch( frm )
                {
                    frm.action = '';
                    frm.target = '_self';
                    return true;
                }
            </script>
<?php
echo CHtml::submitButton('Buscar', array(
    'onClick' => 'return projectSearch( this.form );',
));
?>
            <?php if ($showExportButton) { ?>
                <script type="text/javascript">
                    function exportToCSV( frm )
                    {
                        frm.action = '<?php echo $this->createUrl('project/exportToCSV'); ?>';
                        frm.target = '_blank';
                        return true;
                    }
                </script>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <?php
    echo CHtml::submitButton('Exportar a CSV', array(
        'onClick' => 'return exportToCSV( this.form );',
    ));
    ?>
            <?php } ?>
        </td>
    </tr>
</table>
<script type="text/javascript">
    jQuery(document).ready((function() {
        jQuery( 'input[id^="Project_open_time"],input[id^="Project_close_time"]' )
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
$this->renderPartial('../userProjectTask/_projectsFromCustomerAutocomplete', array(
    'projectStatus' => (!isset($projectStatus)) ? NULL : $projectStatus,
    'onlyManagedByUser' => false,
    'onlyUserEnvolved' => true
));
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var options = new Object();
        options['companyIdInputSelector'] = '#company_id';
        options['companyNameInputSelector'] = '#company_name';
        options['projectSelectSelector'] = '#Project_id';
        options['managerSelectSelector'] = '#Project_manager_id';
        defineAutocompleteCustomers( options );
    });
</script>
<?php
$this->endWidget();
?>
<?php /* * ********** END SEARCH FORM  ****************** */ ?>

<?php
$cs = Yii::app()->clientScript;
$cs->registerScriptFile('js/plugins/jquery.progressbar.min.js', CClientScript::POS_BEGIN);

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'project-grid',
    'dataProvider' => $model->search(),
    'filter' => null,
    'selectableRows' => 0,
    'summaryText' => 'Mostrando {end} de {count} resultado(s)',
    'ajaxUpdate' => FALSE,
    'columns' => array(
        //'code',
        array(
            'header' => 'Cliente',
            'name' => 'company_custom',
            'value' => '$data->company_custom',
        ),
        array(
            'header' => 'Proyecto',
            'name' => 'name',
        ),
        array(
            'name' => 'open_time',
            'header' => 'Apertura',
            'value' => 'PHPUtils::removeHourPartFromDate($data->open_time)',
            'htmlOptions' => array(
                'style' => 'width: 60px'
            ),
        ),
        array(
            'name' => 'close_time',
            'header' => 'Cierre',
            'value' => 'PHPUtils::removeHourPartFromDate($data->close_time)',
            'htmlOptions' => array(
                'style' => 'width: 60px'
            ),
        ),
        array(
            'name' => 'manager_custom',
            'visible' => $showManager,
            'header' => 'Manager',
            'value' => '$data->manager_custom'
        ),
        array(
            'name' => 'commercial_custom',
            'visible' => $showManager,
            'header' => 'Comercial',
            'value' => '$data->commercial_custom'
        ),
        array(
            'name' => 'status',
            'value' => 'ProjectStatus::toString($data->status)',
            'filter' => ProjectStatus::getDataForDropdown(),
            'htmlOptions' => array(
                'style' => 'width: 50px'
            ),
        ),
        array(
            'name' => 'statuscommercial',
            'value' => 'ProjectStatus::toString($data->statuscommercial)',
            'filter' => ProjectStatus::getDataForDropdown(),
            'htmlOptions' => array(
                'style' => 'width: 50px'
            ),
        ),
        array(
            'name' => 'category_name',
            'filter' => ProjectCategories::getDataForDropdown(),
            'htmlOptions' => array('style' => 'text-align: left; width:140px'),
        ),
        array(
            'header' => 'Horas',
            'class' => 'ProjectHoursProgressBarColumn',
            'htmlOptions' => array('style' => 'text-align: left; width:130px'),
        ),
        array(
            'name' => 'totalSeconds',
            'header' => 'Hours Executed',
            'htmlOptions' => array('style' => 'text-align: left; width:20px'),
        ),
        array(
            'header' => 'Max Hours',
            'value' => '$data->max_hours',
            'htmlOptions' => array('style' => 'text-align: left; width:20px'),
        ),
        array(
            'name' => 'executed',
            'header' => '% Executed',
            'htmlOptions' => array('style' => 'text-align: left; width:20px'),
        ),
        array(
            'class' => 'CButtonColumn',
            'visible' => 'Yii::app()->user->hasDirectorPrivileges()',
            'buttons' => array(
                'delete' => array(
                    'visible' => '!$data->hasTasks()',
                ),
                'update' => array(
                    'visible' => 'true',
                ),
                'view' => array(
                    'visible' => 'false',
                ),
                'print' => array(
                    'visible' => '$data->hasReport()',
                    'options' => array('target' => '_new'),
                ),
            ),
            'htmlOptions' => array(
                'style' => 'text-align: left',
            ),
            'headerHtmlOptions' => array(
                'style' => 'width: 40px',
            ),
        ),
    ),
));
?>
