<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?= $this->title ?></h3>
                    </div>
                    <div class="panel-body">
                        <?php $form = ActiveForm::begin([
                                            'id' => 'login-form',
                                            //'layout' => 'horizontal',
                                            'fieldConfig' => [
                                                'template' => "{label}\n<div class=\"col-12\">{input}</div>\n<div class=\"col-12\">{error}</div>",
                                               // 'labelOptions' => ['class' => 'form-control'],
                                            ],
                                        ]); ?>

                            <fieldset>
                                <div class="form-group">
                                    <?= $form->field($model, 'username')->textInput(['autofocus' => true,'class'=>"form-control"]) ?>
                                    
                                </div>
                                <div class="form-group">
                                    <?= $form->field($model, 'password')->textInput(['autofocus' => true,'class'=>"form-control",'type'=>'password']) ?>
                                </div>
                                <div class="form-group">
                                    <div class="buttons">
                                        <?= Html::submitButton('Login', ['class' => 'btn btn-lg btn-success btn-block', 'name' => 'login-button']) ?>
                                    </div>
                                </div>

                                <!-- Change this to a button or input when using this as a form -->
                            </fieldset>
                            <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>