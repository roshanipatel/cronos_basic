<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Constants;
use app\models\enums\ExpenseType;
use app\components\utils\PHPUtils;
use app\models\enums\ExpensePaymentMethod;
?>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Imputar Gasto Proyecto
            </div>
            <div class="panel-body">
                <?php   
                $aFocus = array($model, 'companyName');
                if (!isset($model->id)) {
                    $aFocus = array($model, 'date_ini');
                }
                $isProjectManagerRole = Yii::$app->user->hasProjectManagerPrivileges(); 
                $form = ActiveForm::begin([
                    'id' => 'project-form',
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-12\">{input}</div>\n<div class=\"col-12\">{error}</div>",
                    ],
                    'enableClientValidation'=>false,
                    'validateOnSubmit' => true,
                    'options' => array('enctype' => 'multipart/form-data'),
                ]);
                ?>
                <?php echo $form->errorSummary( $model , array('class'=>'alert alert-danger'));?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>
                            <?php echo $form->field($model, 'id')->hiddenInput()->label(false); ?>
                            <?php echo $form->field($model, 'user_id')->hiddenInput()->label(false); ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php
                            echo $form->field($model, 'pdffile')->fileInput(array('size' => 45, 'maxlength' => 45));
                            if (isset($model->id)) {
                                $imghtml = Html::image('images/pdf.png');
                                echo Html::a($imghtml, array('downloadPdf', 'id'=>$model->id));
                            }?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php
                                $model->date_ini = PHPUtils::removeHourPartFromDate($model->date_ini);
                                echo $form->field($model, 'date_ini')->textInput(array('maxlength' => 20,));
                            ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php
                                echo $form->field($model, 'companyId')->hiddenInput(array('id' => 'company_id'))->label(false);	
                                echo $form->field($model, 'companyName')->textInput(array('id' => 'company_name'));
                            ?>
                            <span id="loadingCustomers"></span>               
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'project_id')->dropdownList(\yii\helpers\ArrayHelper::map($projects, 'id', 'name'),array('id' => 'company_projects','prompt' => 'Seleccione cliente',));
                            ?>
                            <span id="loadingProjects"></span>              
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'costtype')->dropdownList(ExpenseType::getDataForDropDown()); ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'importe')->textInput(array('size' => 10, 'maxlength' => 10));  ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'paymentMethod')->dropdownList(ExpensePaymentMethod::getDataForDropDown());?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'origen')->textInput(array('size' => 45, 'maxlength' => 45)); ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'destino')->textInput(array('size' => 45, 'maxlength' => 45)); ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'company')->textInput(array('size' => 45, 'maxlength' => 45)); ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'motivo')->textArea(array('cols' => 40, 'maxlength' => 1024)); ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'comentario')->textArea(array('cols' => 40, 'maxlength' => 1024)); ?>
                        </div>
                    </div>
                    <?php if( $isProjectManagerRole && isset($model->id)) { ?>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field( $model, 'status')->dropdownList( TaskStatus::getDataForDropDown() ); ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <?php echo Html::submitButton( $model->isNewRecord ? 'Crear' : 'Guardar' , ['class'=>'btn btn-success']); ?>
                        <?php echo Html::a( 'Volver',['#'] , ["onclick"=> "history.back();return false;" , "class"=>"btn btn-danger"] ); ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
                <script type="text/javascript">
                    jQuery(document).ready((function() {
                        jQuery( 'input[id^="ProjectExpense_date_ini"]' )
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
                        'projectStatus' => (!isset($projectStatus))?NULL : $projectStatus,
                        'onlyManagedByUser' => false,
                        'onlyUserEnvolved' => true
                ]);
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        var options = new Object();
                        options['companyIdInputSelector'] = '#company_id';
                        options['companyNameInputSelector'] = '#company_name';
                        options['projectSelectSelector'] = '#company_projects';
                        options['workerSelectSelector'] = '#ProjectExpense_user_id';
                        defineAutocompleteCustomers( options );
                        selectCostType();
                    });
                    
                    $("#ProjectExpense_costtype").change(function() {
                        selectCostType();
                    });
                    
                    $("#ProjectExpense_importe").change(function() {
                        $("#ProjectExpense_importe").val($("#ProjectExpense_importe").val().replace(",", "."));
                    });
                    
                    function selectCostType() {
                        if ($("#ProjectExpense_costtype").val() == '<?php echo ExpenseType::EXP_ALOJAMIENTO ?>') {
                            showAlojamiento();
                        } else if ($("#ProjectExpense_costtype").val() == '<?php echo ExpenseType::EXP_DIETAS ?>') {
                            showDietas();
                        } else if ($("#ProjectExpense_costtype").val() == '<?php echo ExpenseType::EXP_TRANSPORTE ?>' ||
                                $("#ProjectExpense_costtype").val() == '<?php echo ExpenseType::EXP_KM ?>') {
                            showTransporte();
                        } else {
                            hideAll();
                        }
                    }
                    
                    function hideAll() {
                        $("#div_origen").hide();
                        $("#div_destino").hide();
                    }
                    
                    function showAlojamiento() {
                        hideAll();
                    }
                    
                    function showDietas() {
                        hideAll();
                    }
                    
                    function showTransporte() {
                        hideAll();
                        $("#div_origen").show();
                        $("#div_destino").show();
                    }
                </script>
            </div>
        </div>
    </div>
</div>
