<h1>Consultar Gastos Proyectos</h1>
<?php /* * ********** SEARCH FORM  ****************** */ ?>
<?php
assert(isset($projectsProvider));
assert(isset($onlyManagedByUser));
// Required fields
$isProjectManagerRole = Yii::app()->user->hasProjectManagerPrivileges() || Yii::app()->user->hasAdministrativePrivileges();
$form = $this->beginWidget('CActiveForm', array(
    //'action' => $actionURL,
    'method' => 'get',
        ));

//If project manager
if (Yii::app()->user->isProjectManager()) {
    $workersProvider = ServiceFactory::createUserService()->findWorkersByManager(true, Yii::app()->user->id);
} else if (Yii::app()->user->hasAdministrativePrivileges()) {
    $workersProvider = ServiceFactory::createUserService()->findAllWorkers(true);
} else if ($isProjectManagerRole) {
    $workersProvider = ServiceFactory::createUserService()->findProjectWorkers(true);
} else {
    $workersProvider = array();
}
?>
<script type="text/javascript">
    function isAnyChecked()
    {
        if( jQuery("input:checkbox:checked").length == 0 )
        {
            alert( "Seleccione algun cost para aprobar" );
            return false;
        }
        else
            return true;
    }
</script>
<table id="tableTaskSearch">
    <tr>
        <td class="title_search_field">Fecha apertura</td>
        <td class="title_search_field">Fecha cierre</td>
        <td class="title_search_field">Cliente</td>
        <td class="title_search_field">Proyecto</td>
        <?php
        if ($isProjectManagerRole) {
            ?>
            <td class="title_search_field">Imputador</td>
            <?php
        }
        ?>
        <td class="title_search_field">Tipo gasto</td>
        <td class="title_search_field">Forma de pago</td>
    </tr>
    <tr>
        <?php
        echo "<td>\n";
        echo $form->textField($model, 'dateIni', array(
            'maxlength' => 20,
        ));
        echo "</td>\n";
        echo "<td>\n";
        echo $form->textField($model, 'dateEnd', array(
            'maxlength' => 20,
        ));
        echo "</td>\n";
        echo "<td>\n";
        echo $form->hiddenField($model, 'companyId', array('id' => 'company_id'));
        echo $form->textField($model, 'companyName', array(
            'id' => 'company_name',
            'style' => 'width: 200px'
        ));
        echo "<span id=\"loadingCustomers\"></span>\n";
        echo "</td>\n";
        echo "<td>\n";
        echo $form->dropDownList($model, 'projectId', CHtml::listData($projectsProvider, 'id', 'name'), array(
            'prompt' => 'Todos',
            'style' => 'width: 200px'
        ));
        echo "<span id=\"loadingProjects\"></span>\n";
        echo "</td>\n";
        if ($isProjectManagerRole) {
            echo "<td>\n";
            echo $form->dropDownList($model, 'worker', CHtml::listData($workersProvider, 'id', 'name'), array(
                'prompt' => 'Todos',
                'style' => 'width: 120px'
            ));
            echo "<span id=\"loadingWorkers\"></span>\n";
            echo "</td>\n";
        }
        echo "<td>\n";
        echo $form->dropDownList($model, 'costtype', ExpenseType::getDataForDropDown(), array(
            'prompt' => 'Todos',
            'style' => 'width: 100px'
        ));
        echo "</td>\n";
        echo "<td>\n";
        echo $form->dropDownList($model, 'paymentMethod', ExpensePaymentMethod::getDataForDropDown(), array(
            'prompt' => 'Todos',
            'style' => 'width: 100px'
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
            &nbsp;
<?php 
    if ($approveCost) {
        echo CHtml::submitButton( 'Aprobar seleccionadas', array(
                'id' => 'approve_button',
                'submit' => '',
                'params' => array( 'doApprove' => '1' )
        ) ); 
    }
    echo "&nbsp;";
    echo CHtml::submitButton( 'Select all', array(
                'id' => 'select_all' 
        ) ); 
    ?>
            <script type="text/javascript">
                    function exportToCSV( frm )
                    {
                            frm.action = '<?php echo $this->createUrl('projectExpense/exportToCSV'); ?>';
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
            
        
        </td>
    </tr>
</table>
<script type="text/javascript">
    jQuery(document).ready((function() {
        
        $('#approve_button').click(function() {
            return isAnyChecked();
        });
        
        $('#select_all').click(function() {
            $('input[type=checkbox]').each(function () {
                this.checked = !this.checked;
             });
            return false;
        });
        
        jQuery( 'input[id^="ExpenseSearch_dateIni"],input[id^="ExpenseSearch_dateEnd"]' )
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
    'onlyManagedByUser' => $onlyManagedByUser,
    'onlyUserEnvolved' => true
));
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var options = new Object();
        options['companyIdInputSelector'] = '#company_id';
        options['companyNameInputSelector'] = '#company_name';
        options['projectSelectSelector'] = '#ExpenseSearch_projectId';
        options['workerSelectSelector'] = '#ExpenseSearch_worker';
        
        defineAutocompleteCustomers( options );
    });
</script>
<?php

$aExpenses = array(
        array(
            'header' => "Fecha Gasto",
            'name' => 'dateIni',
            'value' => 'PHPUtils::removeHourPartFromDate($data->date_ini)',
            'htmlOptions' => array(
                'style' => 'width: 100px'
            )
        ),
        array(
            'header' => 'Cliente',
            'name' => 'companyName',
            'value' => '$data->companyName',
            'htmlOptions' => array(
                'style' => 'width: 200px'
            )
        ),
        array(
            'header' => 'Proyecto',
            'name' => 'projectName',
            'value' => '$data->projectName',
            'htmlOptions' => array(
                'style' => 'width: 200px'
            )
        ),
        array(
            'name' => 'costtype',
            'value' => 'ExpenseType::toString($data->costtype)',
            'htmlOptions' => array(
                'style' => 'width: 100px'
            )
        ),
        array(
            'name' => 'paymentMethod',
            'value' => 'ExpensePaymentMethod::toString($data->paymentMethod)',
            'htmlOptions' => array(
                'style' => 'width: 100px'
            )
        ),
        array(
            'header' => 'Imputador',
            'name' => 'workerName',
            'value' => '$data->workerName',
            'htmlOptions' => array(
                'style' => 'width: 100px'
            ),
            'visible' => 'Yii::app()->user->hasDirectorPrivileges()',
        ),
        array(
            'header' => 'Importe',
            'name' => 'importe',
            'value' => '$data->importe',
            'htmlOptions' => array(
                'style' => 'width: 100px'
            )
        ),
        array(
            'class' => 'CButtonColumn',
            'template'=>'{update}{delete}{pdf}',
            'buttons' => array(
                'view' => array(
                    'visible' => 'false'
                ),
                'delete' => array(
                    'visible' => '$data->project->statuscommercial != ProjectStatus::PS_CLOSED',
                ),
                'pdf' => array(
                    'label'=>'Download PDF',
                    'visible' => '$data->pdffile != ""',
                    'imageUrl'=>Yii::app()->request->baseUrl.'/images/pdf.png',
                    'url'=>'$this->grid->controller->createUrl("/ProjectExpense/downloadPdf/$data->id")',
                    ),
                'print' => array(
                    'visible' => 'false',
                ),
            ),
            'htmlOptions' => array(
                'style' => 'text-align: left',
            ),
            'headerHtmlOptions' => array(
                'style' => 'width: 50px',
            ),
        )
    );

if ($approveCost) {
    $aExpenses[] = array(
            'class' => 'CCheckBoxColumn',
            'checked' => 'false',
            'id' => 'toApprove',
            'htmlOptions' => array(
                'style' => 'text-align: center',
            )
        );
}

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'project-grid',
    'dataProvider' => $costsProvider,
    //'filter' => $model,
    'filter' => null,
    'selectableRows' => 0,
    'summaryText' => 'Mostrando {end} de {count} resultado(s)',
    'ajaxUpdate' => FALSE,
    'columns' => $aExpenses
));

$this->endWidget();
?>
