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
                                   // 'labelOptions' => ['class' => 'form-control'],
                                ],
                                'enableClientValidation'=>false,
                                'validateOnSubmit' => true,
                            ]); ?>
                        <?php if($form->errorSummary( $model )) {?>
                            <div class="alert alert-danger">
                                <?php echo $form->errorSummary( $model );?>
                            </div>
                                <?php }?>
                        <?php if( Yii::$app->session->getFlash('success')) { ?>
                            <div class="alert alert-success">
                                <?= Yii::$app->session->getFlash('success') ?>
                            </div>
                        <?php } ?>
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
