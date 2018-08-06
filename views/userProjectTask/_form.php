<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\enums\TaskStatus;
use app\models\enums\WorkerProfiles;
?>
<div id="task-calendar"></div>

<div class="row" style="display:none;">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Tareas
            </div>
            <div class="panel-body">
                <?php
                $form = ActiveForm::begin([
                    'id' => 'user-project-task-form',
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-12\">{input}</div>\n<div class=\"col-12\">{error}</div>",
                       // 'labelOptions' => ['class' => 'form-control'],
                    ],
                    'enableClientValidation'=>false,
                    'validateOnSubmit' => true,
                ]); 
                /* $form = ActiveForm::begin([
                    'id' => 'ride-form',
                     // this is redundant because it's true by default
                ]);*/
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <div id="user-task-result"></div>
                        </div>
                    </div>
                <?php echo $form->field($model, 'id')->hiddenInput()->label(false); ?>
                <?php
                    if($model->id){
                        echo $form->field($model, 'user_id')->hiddenInput()->label(false);  
                    }   
                ?>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?= $form->field($model, 'frm_customer_name')->textInput(['class'=>"form-control col-lg-6",'id' => 'company_name','value'=>$model->frm_customer_name]) ?>
                        </div>
                        <?php if( !$isWorker ) { ?>
                            <small><?php echo Html::a( '(Crear nueva empresa)', array( 'company/create' ) ); ?></small>
                        <?php } ?>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?= $form->field($model, 'project_id')->dropDownList(array(), ['prompt'=>'Choose...']);?>
                        </div>
                       <span id="loadingProjects"></span>
                       <?php if( !$isWorker ) { ?>
                            <small><?php echo Html::a( '(Crear nuevo proyecto)', array( 'project/create' ) ); ?></small>
                       <?php } ?>
                       <?php Html::error($model,'project_id', ['class' => 'help-block']); ?>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                             <?= $form->field($model, 'imputetype_id')->dropDownList(array(), ['prompt'=>'Choose...']);?>
                        </div>
                       <span id="loadingImputetypes"></span>
                       <?php Html::error($model,'imputetype_id', ['class' => 'help-block']); ?>
                    </div>           
                    <?php echo $form->field($model, 'frm_date_ini')->hiddenInput()->label(false); ?>
                    <?php echo $form->field($model, 'frm_date_end')->hiddenInput()->label(false); ?>
                    <div class="col-lg-6">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Horas <span>*</span></label>
                                <?php $aDateProps = array('size' => 12,'maxlength' => 10,'class'=>'form-control');
                                  echo Html::textInput('frm_date_ini2', '', $aDateProps); ?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <?php echo $form->field( $model, 'frm_hour_ini',array('options'=>array('size'=>'6','maxlength'=>'10','onchange' => 'checkHourSemantics(\'hour_ini\')')))->textInput()->label(false); ?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                 <?php echo $form->field( $model, 'frm_hour_end', array('options'=>array('size' => 6,'maxlength' => 10,'onchange' => 'checkHourSemantics(\'hour_end\')')))->textInput()->label(false); ?>
                            </div>
                        </div>
                        <?php Html::error($model,'imputetype_id', ['class' => 'help-block']); ?>
                        <?php echo Html::error($model,'frm_date_ini', ['class' => 'help-block']);?>
                        <?php echo Html::error($model,'frm_hour_ini', ['class' => 'help-block']);?>
                        <?php echo Html::error($model,'frm_hour_end', ['class' => 'help-block']);?>
                        <?php echo Html::error($model,'frm_date_end', ['class' => 'help-block']);?>
                    </div>
                    <?php if( !$isWorker ) { ?>
                    <div class="col-lg-6">
                        <div class="form-group">
                             <?= $form->field($model, 'status')->dropDownList(TaskStatus::getDataForDropDown(), ['prompt'=>'Choose...']);?>
                        </div>
                       <?php Html::error($model,'status', ['class' => 'help-block']); ?>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                             <?= $form->field($model, 'profile_id')->dropDownList(WorkerProfiles::getDataForDropDown(), ['prompt'=>'Choose...']);?>
                        </div>
                    <?php echo Html::error( $model, 'profile_id', ['class' => 'help-block'] ); ?>
                    </div>
                    <?php } ?>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?= $form->field($model, 'task_description')->textarea(['cols' => '40', 'maxlength' => '1024'])->textarea()->label('Task Description') ?>
                        </div>
                        <?php echo Html::error( $model, 'task_description', ['class' => 'help-block']); ?>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?= $form->field($model, 'ticket_id',array('options'=>array('size' => '40', 'maxlength' => '128')))->textInput()->label('Ticket') ?>
                        </div>
                        <?php echo Html::error( $model, 'task_description', ['class' => 'help-block']); ?>
                        <small><?php echo Html::a( '(Previsualizar ticket)', 'javascript:testTicket()' ); ?></small>
                        <?php echo Html::error( $model, 'ticket_id', ['class' => 'help-block'] ); ?>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'is_extra')->checkBox(['label' => 'is_extra', 'uncheck' => null, 'selected' => true,'style' => 'display: inline; position: relative; top: -2px']);  ?>
                        </div>
                        <?php echo Html::error( $model, 'is_extra', ['class' => 'help-block'] ); ?>
                    </div>
                    <?php if( Yii::$app->user->isAdmin() ) { ?>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'is_billable')->checkBox(['label' => 'is_billable', 'uncheck' => null, 'selected' => true,'style' => 'display: inline; position: relative; top: -2px']);  ?>
                        </div>
                        <?php echo Html::error( $model, 'is_billable', ['class' => 'help-block'] ); ?>
                    </div>
                    <?php } ?>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <span id="savigTasks"></span>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
       </div><!-- form -->
    </div>
</div>    
<?= $this->render('/userProjectTask/_form_js',[
    'customers' => $customers,
]);
?>
<?= $this->render('/userProjectTask/_calendar_js',[
    'model' => $model,
    'workers' => $workers,
    'customers' => $customers,
    'showExtendedFields' => ! $isWorker,
    'isWorker' => $isWorker,
    'showDate' => $showDate,
    'showUser' => $showUser,
]);
?>
