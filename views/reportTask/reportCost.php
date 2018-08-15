<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">Report: Gastos Proyecto</h1>
  </div>
</div>
<?php
use app\services\ServiceFactory;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\enums\ExpenseType;
// Required fields
$showManager = Yii::$app->user->hasDirectorPrivileges();
$onlyManagedByUser = !$showManager;

$managersProvider = ServiceFactory::createUserService()->findProjectWorkers(true);

?>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Report
            </div>
            <div class="panel-body">
                <?php 
                     $form = ActiveForm::begin([
                            'method' => 'get',
                        ]);

                $aDiaActual = split("/", date("d/m/Y"));
                $beginDay = mktime(0,0,0,$aDiaActual[1], 1, $aDiaActual[2]);
                $endDay = mktime(0,0,0,$aDiaActual[1] + 1, 0, $aDiaActual[2]);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="open_time">Fecha Inicio</label>
                                <input type="text" class="form-control" id="ExpenseSearch_dateIni" name="ExpenseSearch_dateIni" value="<?php echo date("d/m/Y", $beginDay) ?>"/>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="close_time">Fecha Final</label>
                                <input type="text" class="form-control" id="ExpenseSearch_dateEnd" name="ExpenseSearch_dateEnd" value="<?php echo date("d/m/Y", $endDay) ?>"/>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="personas">Empresa</label>
                                <input type="hidden" id="ReportCost_companyId" name="ReportCost_companyId" />
                                <input type="text" class="form-control" id="ReportCost_companyName" name="ReportCost_companyName" />
                                <span id="loadingCustomers"></span>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="diaslaborales">Proyecto</label>
                                <?php echo Html::dropDownList('ReportCost_projectId', "", array(), array('prompt' => 'Todos','class'=>'form-control'));?>
                                <span id="loadingProjects"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="festivos">Trabajador</label>
                                <?php echo Html::dropDownList('ReportCost_worker', "", \yii\helpers\ArrayHelper::map($managersProvider, 'id', 'name'), array('prompt' => 'Todos','class'=>'form-control'));?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="festivos">Tipo Gasto</label>
                                <?php echo Html::dropDownList('ReportCost_costtype', "", ExpenseType::getDataForDropDown(), array('prompt' => 'Todos','class' =>'form-control'));?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-lg-6"> 
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
                                    frm.action = '<?php echo Yii::$app->urlManager->createUrl(['report-task/export-costs']); ?>';
                                    frm.target = '_blank';
                                    return true;
                                }
                            </script>
                            <?php echo Html::submitButton('Make report', array('class'=>'btn btn-success','onClick' => 'return makeReport( this.form );',));?>
                        </div>
                    </div>
                </div>
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
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

