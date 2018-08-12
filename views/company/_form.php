<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
?>
<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Empresa
                        </div>
                        <div class="panel-body">
                            <?php
                            $form = ActiveForm::begin([
                                'id' => 'company-form',
                                'fieldConfig' => [
                                    'template' => "{label}\n<div class=\"col-12\">{input}</div>\n<div class=\"col-12\">{error}</div>",
                                        //'error' => 'alert alert-danger',
                                   // 'labelOptions' => ['class' => 'form-control'],
                                ],
                                'enableClientValidation'=>false,
                                'validateOnSubmit' => true,
                            ]); ?>
                        <?php echo $form->errorSummary( $model  , array('class' => 'alert alert-danger'));?>
                        
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <?= $form->field($model, 'name')->textInput(['class'=>"form-control col-lg-6", array( 'size' => 60, 'maxlength' => 256 )])->label('Name *') ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <?= $form->field($model, 'email')->textInput(['class'=>"form-control col-lg-6", array( 'size' => 60, 'maxlength' => 256 )])->label('Email *') ?>
                                    </div>
                                </div>
                                <div class="col-lg-12" style="margin-top: 20px;">
                                        <?php echo Html::submitButton( $model->isNewRecord ? 'Crear' : 'Guardar' , ['class'=>'btn btn-success']); ?>
                                        <?php echo Html::a( 'Volver',['#'] , ["onclick"=> "history.back();return false;" , "class"=>"btn btn-danger"] ); ?>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
            <?php ActiveForm::end(); ?>
                            <!-- /.row (nested) -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
