<div class="form">

    <?php
    $form = $this->beginWidget( 'CActiveForm', array(
                'id' => 'company-form',
                'enableAjaxValidation' => false,
                'focus' => array( $model, 'name' ),
            ) );
    ?>

    <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>

    <?php echo $form->errorSummary( $model ); ?>

    <?php if( Yii::app()->user->hasFlash( Constants::FLASH_OK_MESSAGE ) )
    { ?>
        <div class="resultOk"><p><?php echo Yii::app()->user->getFlash( Constants::FLASH_OK_MESSAGE ) ?></p></div>
        <?php } ?>

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
    <br>

    <div class="row buttons">
    <?php echo CHtml::submitButton( $model->isNewRecord ? 'Crear' : 'Guardar' ); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->