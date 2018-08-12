<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Constants;
use app\models\enums\ExpenseType;
use app\components\utils\PHPUtils;
use app\models\enums\ExpensePaymentMethod;
?>
<div class="form">

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

   /* $form = $this->beginWidget('CActiveForm', array(
        'id' => 'project-form',
        'enableAjaxValidation' => false,
        'htmlOptions' => array('enctype' => 'multipart/form-data'),
        'focus' => $aFocus,
            ));*/
    ?>
    <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>
    <?php echo $form->errorSummary($model); ?>
    <?php if (Yii::$app->session->hasFlash(Constants::FLASH_OK_MESSAGE)) { ?>
        <div class="resultOk"><p><?php echo Yii::$app->session->getFlash(Constants::FLASH_OK_MESSAGE) ?></p></div>
    <?php }
    ?>
    <?php if (Yii::$app->session->hasFlash(Constants::FLASH_ERROR_MESSAGE)) { ?>
        <div class="errorSummary-short"><p><?php echo Yii::$app->session->getFlash(Constants::FLASH_ERROR_MESSAGE) ?></p></div>
    <?php }
    ?>
    <?php echo $form->field($model, 'id')->hiddenInput(); ?>
    <?php echo $form->field($model, 'user_id')->hiddenInput() ?>
    <div class="row">
        <?php
        //echo $form->labelEx($model, 'pdffile');
        echo $form->field($model, 'pdffile')->fileInput(array('size' => 45, 'maxlength' => 45));
        //echo $form->error($model, 'pdffile');
        if (isset($model->id)) {
            $imghtml = Html::image('images/pdf.png');
            echo Html::a($imghtml, array('downloadPdf', 'id'=>$model->id));
        }
        ?>
    </div>
    <div class="row">
        <?php //echo $form->labelEx($model, 'date_ini'); ?>
        <?php
        $model->date_ini = PHPUtils::removeHourPartFromDate($model->date_ini);
        echo $form->field($model, 'date_ini')->textInput(array(
            'maxlength' => 20,
        ));
        ?>
        <?php //echo $form->error($model, 'date_ini'); ?>
    </div>
    <div class="row">
        <?php //echo $form->labelEx($model, 'companyName'); ?>
        <?php
        echo $form->field($model, 'companyId')->hiddenInput(array('id' => 'company_id'));	
        echo $form->field($model, 'companyName')->textInput(array('id' => 'company_name'));
	echo "<span id=\"loadingCustomers\"></span>\n";
        ?>
        <?php //echo $form->error($model, 'companyName'); ?>        
    </div>
    <div class="row">
        <?php //echo $form->labelEx($model, 'project_id'); ?>
        <?php
        echo $form->field($model, 'project_id')->dropdownList(\yii\helpers\ArrayHelper::map($projects, 'id', 'name'),
					array(
				'id' => 'company_projects',
				'prompt' => 'Seleccione cliente',
				'style' => 'width: 110px'
			));
        echo "<span id=\"loadingProjects\"></span>\n";
        ?>
        <?php //echo $form->error($model, 'project_id'); ?>
    </div>
    <div class="row">
        <?php
        //echo $form->labelEx($model, 'costtype');
        echo $form->field($model, 'costtype')->dropdownList(ExpenseType::getDataForDropDown());
        //echo $form->error($model, 'costtype');
        ?>
    </div>
    <div class="row">
        <?php //echo $form->labelEx($model, 'importe'); ?>
        <?php echo $form->field($model, 'importe')->textInput(array('size' => 10, 'maxlength' => 10)); ?>
        <?php //echo $form->error($model, 'importe'); ?>
    </div>
    <div class="row">
        <?php
        //echo $form->labelEx($model, 'paymentMethod');
        echo $form->field($model, 'paymentMethod')->dropdownList(ExpensePaymentMethod::getDataForDropDown());
        //echo $form->error($model, 'paymentMethod');
        ?>
    </div>
    <div id="div_origen" class="row">
        <?php
        //echo $form->labelEx($model, 'origen');
        echo $form->field($model, 'origen')->textInput(array('size' => 45, 'maxlength' => 45));
        //echo $form->error($model, 'origen');
        ?>
    </div>
    <div id="div_destino" class="row">
        <?php
        //echo $form->labelEx($model, 'destino');
        echo $form->field($model, 'destino')->textInput(array('size' => 45, 'maxlength' => 45));
       // echo $form->error($model, 'destino');
        ?>
    </div>
    <div id="div_company" class="row">
        <?php
        //echo $form->labelEx($model, 'company');
        echo $form->field($model, 'company')->textInput(array('size' => 45, 'maxlength' => 45));
        //echo $form->error($model, 'company');
        ?>
    </div>
    <div id="div_motivo" class="row">
        <?php
        //echo $form->labelEx($model, 'motivo');
        echo $form->field($model, 'motivo')->textArea(array('cols' => 40, 'maxlength' => 1024));
        //echo $form->error($model, 'motivo');
        ?>
    </div>
    <div class="row">
        <?php //echo $form->labelEx($model, 'comentario'); ?>
        <?php echo $form->field($model, 'comentario')->textArea(array('cols' => 40, 'maxlength' => 1024)); ?>
        <?php //echo $form->error($model, 'comentario'); ?>
    </div>
    <?php
        if( $isProjectManagerRole && isset($model->id)) {
    ?>
            <div class="row">
                <?php //echo $form->labelEx( $model, 'status' ); ?>
                <?php echo $form->field( $model, 'status')->dropdownList( TaskStatus::getDataForDropDown() ); ?>
            <?php //echo $form->error( $model, 'status' ); ?>
            </div>
    <?php } ?>
        
    <div class="row buttons">
    <?php echo Html::submitButton($model->isNewRecord ? 'Crear' : 'Guardar' ); ?>
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
</div><!-- form -->