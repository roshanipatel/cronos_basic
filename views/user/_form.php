<div class="form">

    <?php
    
    $form = $this->beginWidget( 'CActiveForm', array(
                'id' => 'user-form',
                'enableAjaxValidation' => false,
                'focus' => array($model,'username'),
            ) );
    
    ?>

    <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>

    <?php echo $form->errorSummary( $model ); ?>

    <?php if( Yii::app()->user->hasFlash(Constants::FLASH_OK_MESSAGE) ) { ?>
        <div class="resultOk"><p><?php echo Yii::app()->user->getFlash(Constants::FLASH_OK_MESSAGE)?></p></div>
    <?php } ?>

    <div class="row">
        <?php echo $form->labelEx( $model, 'username' ); ?>
        <?php echo $form->textField( $model, 'username', array( 'size' => 60, 'maxlength' => 128 ) ); ?>
        <?php echo $form->error( $model, 'username' ); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx( $model, 'newPassword' ); ?>
        <?php echo $form->passwordField( $model, 'newPassword', array( 'size' => 60, 'maxlength' => 128 ) ); ?>
        <?php echo $form->error( $model, 'newPassword' ); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx( $model, 'newPasswordRepeat' ); ?>
        <?php echo $form->passwordField( $model, 'newPasswordRepeat', array( 'size' => 60, 'maxlength' => 128 ) ); ?>
        <?php echo $form->error( $model, 'newPasswordRepeat' ); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx( $model, 'name' ); ?>
        <?php echo $form->textField( $model, 'name', array( 'size' => 60, 'maxlength' => 256 ) ); ?>
        <?php echo $form->error( $model, 'name' ); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx( $model, 'email' ); ?>
        <?php echo $form->textField( $model, 'email', array( 'size' => 60, 'maxlength' => 128 ) ); ?>
        <?php echo $form->error( $model, 'email' ); ?>
    </div>
        
    <div class="row">
        <?php echo $form->labelEx( $model, 'imputacionanterior' ); ?>
        <?php echo $form->textField( $model, 'imputacionanterior', array('size' => 5, 'maxlength' => 5 ) ); ?>
        días
        <?php echo $form->error( $model, 'imputacionanterior' ); ?>
    </div>
        
    <div class="row">
        <?php echo $form->labelEx( $model, 'startcontract' ); ?>
        <?php 
        $model->startcontract = PHPUtils::removeHourPartFromDate($model->startcontract);
        echo $form->textField( $model, 'startcontract', array(
				'maxlength' => 16,
		 )); ?>
        &nbsp;&nbsp;&nbsp;
        <?php echo $form->error( $model, 'startcontract' ); ?>
    </div>
        
    <div class="row">
        <?php echo $form->labelEx( $model, 'endcontract' ); ?>
        <?php 
        $model->endcontract = PHPUtils::removeHourPartFromDate($model->endcontract);
        echo $form->textField( $model, 'endcontract', array(
				'maxlength' => 16,
			)); ?>
        &nbsp;&nbsp;&nbsp;
        <?php echo $form->error( $model, 'endcontract' ); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx( $model, 'company_id' ); ?>
        <?php echo $form->dropDownList( $model, 'company_id', CHtml::listData( Company::model()->findAll(), 'id', 'name' ) ); ?>
        &nbsp;&nbsp;&nbsp;
        <small><?php echo CHtml::link( '(Crear nueva empresa)', array( 'company/create' ) ); ?></small>
        <?php echo $form->error( $model, 'company_id' ); ?>
    </div>

	<div class="row">
		<?php echo $form->labelEx($model,'worker_dflt_profile'); ?>
        <?php echo $form->dropDownList( $model, 'worker_dflt_profile', WorkerProfiles::getDataForDropDown() ); ?>
		<?php echo $form->error($model,'worker_dflt_profile'); ?>
	</div>

    <div class="row">
        <?php echo $form->labelEx( $model, 'role' ); ?>
        <!--<div id="radioRole" style="padding: 3px;">-->
        <?php
         echo $form->dropDownList( $model, 'role', User::getPriorityUser(Yii::app()->user->role, $model->role), array(
            //'labelOptions' => array(
            //    'style' => 'display: inline; width: 100px',
            //    )
        ) );
        ?>
        <!--</div>-->
        <?php echo $form->error( $model, 'role' ); ?>
    </div>
    <div class="row">
                    <?php echo $form->labelEx( $model, 'hourcost' ); ?>
                    <?php echo $form->textField( $model, 'hourcost', array('size' => 5, 'maxlength' => 5 ) ); ?>
        &nbsp;&nbsp;&nbsp;
        <?php echo $form->error( $model, 'hourcost' ); ?>
    </div>
    <div class="row">
                    <?php echo $form->labelEx( $model, 'weeklyhours' ); ?>
                    <?php echo $form->textField( $model, 'weeklyhours', array('size' => 5, 'maxlength' => 5 ) ); ?>
        &nbsp;&nbsp;&nbsp;
        <?php echo $form->error( $model, 'weeklyhours' ); ?>
    </div>
        
    <script type="text/javascript">
	jQuery(document).ready((function() {
		jQuery( 'input[id^="User_startcontract"],input[id^="User_endcontract"]' )
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
                
                jQuery( 'input[id^="User_startcontract"]' )
		.attr('readonly', 'readonly');
		
		jQuery( "div.ui-datepicker" ).css("font-size", "80%");
	}));
    </script>
    <script type="text/javascript">
        jQuery(document).ready(function()
        {
            jQuery("#radioRole label").css( "display", "inline" );
            //jQuery( "#radioRole" ).buttonset();
        });
    </script>
    <br>

    <?php if( Yii::app()->user->role == Roles::UT_ADMIN ||
              (Yii::app()->user->role != Roles::UT_ADMIN && $model->role != Roles::UT_ADMIN)) { ?>
    <div class="row buttons">
        <?php echo CHtml::submitButton( $model->isNewRecord ? 'Crear' : 'Guardar' ); ?>
    </div>
    <?php } ?>

<?php $this->endWidget(); ?>

</div><!-- form -->