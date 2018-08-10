<h1>Report: Gastos Proyecto</h1>
<?php /* * ********** SEARCH FORM  ****************** */ ?>
<?php
// Required fields
$showManager = Yii::$app->user->hasDirectorPrivileges();
$onlyManagedByUser = !$showManager;

$managersProvider = ServiceFactory::createUserService()->findProjectWorkers(true);

$form = $this->beginWidget('CActiveForm', array(
    //'action' => $actionURL,
    'method' => 'get',
        ));

$aDiaActual = split("/", date("d/m/Y"));
$beginDay = mktime(0,0,0,$aDiaActual[1], 1, $aDiaActual[2]);
$endDay = mktime(0,0,0,$aDiaActual[1] + 1, 0, $aDiaActual[2]);

?>

<table id="tableTaskSearch">
    <tr>
        <td class="title_search_field">Fecha Inicio</td>
        <td class="title_search_field">Fecha Final</td>
        <td class="title_search_field">Empresa</td>
        <td class="title_search_field">Proyecto</td>
        <td class="title_search_field">Trabajador</td>
        <td class="title_search_field">Tipo Gasto</td>
    </tr>
    <tr>
        <td>
            <input type="text" id="ExpenseSearch_dateIni" name="ExpenseSearch_dateIni" value="<?php echo date("d/m/Y", $beginDay) ?>"/>
        </td>
        <td>
            <input type="text" id="ExpenseSearch_dateEnd" name="ExpenseSearch_dateEnd" value="<?php echo date("d/m/Y", $endDay) ?>"/>
        </td>
        <td>
            <input type="hidden" id="ReportCost_companyId" name="ReportCost_companyId" />
            <input type="text" id="ReportCost_companyName" name="ReportCost_companyName" />
            <span id="loadingCustomers"></span>
        </td>
        <td>
            <?php
            echo CHtml::dropDownList('ReportCost_projectId', "", array(), array(
            'prompt' => 'Todos',
            'style' => 'width: 100px'
        ));?>
            <span id="loadingProjects"></span>
        </td>
        <td>
            <?php
            echo CHtml::dropDownList('ReportCost_worker', "", CHtml::listData($managersProvider, 'id', 'name'), array(
            'prompt' => 'Todos',
            'style' => 'width: 100px'
        ));?>
        </td>
        <td>
            <?php
            echo CHtml::dropDownList('ReportCost_costtype', "", ExpenseType::getDataForDropDown(), array(
            'prompt' => 'Todos',
            'style' => 'width: 100px'
        ));?>
        </td>
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
                <script type="text/javascript">
                    function makeReport( frm )
                    {
                        frm.action = '<?php echo $this->createUrl('report-task/export-costs'); ?>';
                        frm.target = '_blank';
                        return true;
                    }
                </script>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <?php
    echo CHtml::submitButton('Make report', array(
        'onClick' => 'return makeReport( this.form );',
    ));
    ?>
        </td>
    </tr>
</table>
<script type="text/javascript">
    jQuery(document).ready((function() {
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
$this->render('../userProjectTask/_projectsFromCustomerAutocomplete', [
    'onlyManagedByUser' => $onlyManagedByUser,
    'onlyUserEnvolved' => true
]);
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var options = new Object();
        options['companyIdInputSelector'] = '#ReportCost_worker';
        options['companyNameInputSelector'] = '#ReportCost_companyName';
        options['projectSelectSelector'] = '#ReportCost_projectId';
        options['managerSelectSelector'] = '#ReportCost_worker';
        
        defineAutocompleteCustomers( options );
    });
</script>
<?php
$this->endWidget();
?>
