<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
?>
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Tareas</h1>
    </div>
<!-- /.col-lg-12 -->
</div>
<div class="row" style="display:none;">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Tareas
            </div>
            <div class="panel-body">
                <?php
                $form = ActiveForm::begin([
                    'id' => 'company-form',
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-12\">{input}</div>\n<div class=\"col-12\">{error}</div>",
                       // 'labelOptions' => ['class' => 'form-control'],
                    ],
                    'enableClientValidation'=>false,
                    'validateOnSubmit' => true,
                ]); ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>
                    </div>
                </div>

    <?php echo $form->errorSummary( $model ); ?>

    <?php if( Yii::$app->session->getFlash('success')) 
    { ?>
        <div class="resultOk"><p><?= Yii::$app->session->getFlash('success') ?></p></div>       4
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