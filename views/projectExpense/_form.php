<div class="form">

    <?php   
    $aFocus = array($model, 'companyName');
    if (!isset($model->id)) {
        $aFocus = array($model, 'date_ini');
    }
    $isProjectManagerRole = Yii::app()->user->hasProjectManagerPrivileges();    
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'project-form',
        'enableAjaxValidation' => false,
        'htmlOptions' => array('enctype' => 'multipart/form-data'),
        'focus' => $aFocus,
            ));
    ?>
    <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>
    <?php echo $form->errorSummary($model); ?>
    <?php if (Yii::app()->user->hasFlash(Constants::FLASH_OK_MESSAGE)) { ?>
        <div class="resultOk"><p><?php echo Yii::app()->user->getFlash(Constants::FLASH_OK_MESSAGE) ?></p></div>
    <?php }
    ?>
    <?php if (Yii::app()->user->hasFlash(Constants::FLASH_ERROR_MESSAGE)) { ?>
        <div class="errorSummary-short"><p><?php echo Yii::app()->user->getFlash(Constants::FLASH_ERROR_MESSAGE) ?></p></div>
    <?php }
    ?>
    <?php echo $form->hiddenField($model, 'id'); ?>
    <?php echo $form->hiddenField($model, 'user_id') ?>
    <div class="row">
        <?php
        echo $form->labelEx($model, 'pdffile');
        echo $form->fileField($model, 'pdffile', array('size' => 45, 'maxlength' => 45));
        echo $form->error($model, 'pdffile');
        if (isset($model->id)) {
            $imghtml = CHtml::image('images/pdf.png');
            echo CHtml::link($imghtml, array('downloadPdf', 'id'=>$model->id));
        }
        ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'date_ini'); ?>
        <?php
        $model->date_ini = PHPUtils::removeHourPartFromDate($model->date_ini);
        echo $form->textField($model, 'date_ini', array(
            'maxlength' => 20,
        ));
        ?>
        <?php echo $form->error($model, 'date_ini'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'companyName'); ?>
        <?php
        echo $form->hiddenField($model, 'companyId', array('id' => 'company_id'));	
        echo $form->textField($model, 'companyName', array('id' => 'company_name'));
	echo "<span id=\"loadingCustomers\"></span>\n";
        ?>
        <?php echo $form->error($model, 'companyName'); ?>        
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'project_id'); ?>
        <?php
        echo $form->dropDownList($model, 'project_id', CHtml::listData($projects, 'id', 'name'),
					array(
				'id' => 'company_projects',
				'prompt' => 'Seleccione cliente',
				'style' => 'width: 110px'
			));
        echo "<span id=\"loadingProjects\"></span>\n";
        ?>
        <?php echo $form->error($model, 'project_id'); ?>
    </div>
    <div class="row">
        <?php
        echo $form->labelEx($model, 'costtype');
        echo $form->dropDownList($model, 'costtype', ExpenseType::getDataForDropDown());
        echo $form->error($model, 'costtype');
        ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'importe'); ?>
        <?php echo $form->textField($model, 'importe', array('size' => 10, 'maxlength' => 10)); ?>
        <?php echo $form->error($model, 'importe'); ?>
    </div>
    <div class="row">
        <?php
        echo $form->labelEx($model, 'paymentMethod');
        echo $form->dropDownList($model, 'paymentMethod', ExpensePaymentMethod::getDataForDropDown());
        echo $form->error($model, 'paymentMethod');
        ?>
    </div>
    <div id="div_origen" class="row">
        <?php
        echo $form->labelEx($model, 'origen');
        echo $form->textField($model, 'origen', array('size' => 45, 'maxlength' => 45));
        echo $form->error($model, 'origen');
        ?>
    </div>
    <div id="div_destino" class="row">
        <?php
        echo $form->labelEx($model, 'destino');
        echo $form->textField($model, 'destino', array('size' => 45, 'maxlength' => 45));
        echo $form->error($model, 'destino');
        ?>
    </div>
    <div id="div_company" class="row">
        <?php
        echo $form->labelEx($model, 'company');
        echo $form->textField($model, 'company', array('size' => 45, 'maxlength' => 45));
        echo $form->error($model, 'company');
        ?>
    </div>
    <div id="div_motivo" class="row">
        <?php
        echo $form->labelEx($model, 'motivo');
        echo $form->textArea($model, 'motivo', array('cols' => 40, 'maxlength' => 1024));
        echo $form->error($model, 'motivo');
        ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'comentario'); ?>
<?php echo $form->textArea($model, 'comentario', array('cols' => 40, 'maxlength' => 1024)); ?>
        <?php echo $form->error($model, 'comentario'); ?>
    </div>
    <?php
        if( $isProjectManagerRole && isset($model->id)) {
    ?>
            <div class="row">
                <?php echo $form->labelEx( $model, 'status' ); ?>
                <?php echo $form->dropDownList( $model, 'status', TaskStatus::getDataForDropDown() ); ?>
            <?php echo $form->error( $model, 'status' ); ?>
            </div>
    <?php } ?>
        
    <div class="row buttons">
    <?php echo CHtml::submitButton($model->isNewRecord ? 'Crear' : 'Guardar' ); ?>
    </div>

<?php $this->endWidget(); ?>
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
    Yii::$app->controller->renderPartial('../userProjectTask/_projectsFromCustomerAutocomplete', [
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