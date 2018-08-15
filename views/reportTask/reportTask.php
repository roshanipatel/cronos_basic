<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">Report: Actividad</h1>
  </div>
</div>
<?php
$this->registerJsFile(Yii::$app->request->BaseUrl .'/js/jquery-1.6.2.js',['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile(Yii::$app->request->BaseUrl .'/js/jquery-ui-1.8.8.custom.js',['position' => \yii\web\View::POS_HEAD]); 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\ServiceFactory;
// Required fields
$showManager = Yii::$app->user->hasDirectorPrivileges();
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
                                <input type="text" class="form-control" id="open_time" name="open_time" value="<?php echo date("d/m/Y", $beginDay) ?>"/>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="close_time">Fecha Final</label>
                                <input type="text" class="form-control" id="close_time" name="close_time" value="<?php echo date("d/m/Y", $beginDay) ?>"/>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="personas">Personas</label>
                                <input type="text" class="form-control" id="personas" name="personas" value=""/>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="diaslaborales">Días Laborales</label>
                                <input type="text" class="form-control" id="diaslaborales" name="diaslaborales" value=""/>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label" for="festivos">Festivos</label>
                                <input type="text" class="form-control" id="festivos" name="festivos" value=""/>
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
                                    frm.action = '<?php echo Yii::$app->urlManager->createUrl('report-task/export-activity'); ?>';
                                    frm.target = '_blank';
                                    return true;
                                }
                            </script>
                            <?php echo Html::submitButton('Make report', array('class'=>'btn btn-success','onClick' => 'return makeReport( this.form );'));?>            
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
                <script type="text/javascript">
                    var $j = jQuery.noConflict();
                    $j(document).ready((function() {
                        $j( 'input[id^="open_time"],input[id^="close_time"]' )
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
                        $j( "div.ui-datepicker" ).css("font-size", "80%");
                    }));
                </script>
            </div>
        </div>
    </div>
</div>

