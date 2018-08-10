<?php 
    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Session;
    //use app\models\Constants;
    use app\components\utils\PHPUtils;
    use app\models\db\Company;
    use app\models\User;
    use app\models\enums\WorkerProfiles;
    use app\models\enums\Roles;
    use app\components\utils\DateTime;

    $this->registerJsFile(Yii::$app->request->BaseUrl .'/js/jquery-1.6.2.js',['position' => \yii\web\View::POS_HEAD]);
    $this->registerJsFile(Yii::$app->request->BaseUrl .'/js/jquery-ui-1.8.8.custom.js',['position' => \yii\web\View::POS_HEAD]);
?>
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Usuarios</h1>
    </div>
    <div class="row" >
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Usuarios
            </div>
            <div class="panel-body">
                <?php
                    $form = ActiveForm::begin([
                        'id' => 'user-form',
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-12\">{input}</div>\n<div class=\"col-12\">{error}</div>",
                           // 'labelOptions' => ['class' => 'form-control'],
                        ],
                        'enableClientValidation'=>false,
                        //'focus' => array($model,'username'),
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
                        <?php echo $form->errorSummary( $model ); ?>
                        <?php if( Yii::$app->session->getFlash('success')) { ?>
                            <div class="resultOk"><p><?= Yii::$app->session->getFlash('success') ?></p></div>
                        <?php } ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?= $form->field($model, 'username')->textInput(['class'=>"form-control col-lg-6", array( 'size' => 60, 'maxlength' => 128 )])->label('User Name *') ?>
                            </div>
                                <?php //Html::error($model,'username', ['class' => 'help-block']); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?= $form->field($model, 'newPassword')->textInput(['class'=>"form-control col-lg-6",'type'=>'password', array( 'size' => 60, 'maxlength' => 128 )])->label('Password *') ?>
                            </div>
                                <?php //Html::error($model,'newPassword', ['class' => 'help-block']); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?= $form->field($model, 'newPasswordRepeat')->textInput(['class'=>"form-control col-lg-6",'type'=>'password', array( 'size' => 60, 'maxlength' => 128 )])->label('Password Repita *') ?>
                            </div>
                                <?php //Html::error($model,'newPasswordRepeat', ['class' => 'help-block']); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?= $form->field($model, 'name')->textInput(['class'=>"form-control col-lg-6", array( 'size' => 60, 'maxlength' => 256 )])->label('Nombre *') ?>
                            </div>
                                <?php //Html::error($model,'name', ['class' => 'help-block']); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?= $form->field($model, 'email')->textInput(['class'=>"form-control col-lg-6", array( 'size' => 60, 'maxlength' => 128 )])->label('Email *') ?>
                            </div>
                                <?php //Html::error($model,'email', ['class' => 'help-block']); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?= $form->field($model, 'imputacionanterior')->textInput(['class'=>"form-control col-lg-6", array( 'size' => 5, 'maxlength' => 5 )])->label('Imputación anterior permitida') ?>
                            </div>
                                <?php //Html::error($model,'imputacionanterior', ['class' => 'help-block']); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                
                             <?php  $model->startcontract = PHPUtils::removeHourPartFromDate($model->startcontract); ?>
                                <?= $form->field($model, 'startcontract')->textInput(['class'=>" form-control col-lg-6",'id'=>'User_startcontract', array(  'maxlength' => 16 )])->label('Inicio contrato') ?>
                            </div>
                                <?php //Html::error($model,'startcontract', ['class' => 'help-block']); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                             <?php  $model->endcontract = PHPUtils::removeHourPartFromDate($model->endcontract); ?>
                                <?= $form->field($model, 'endcontract')->textInput(['class'=>" form-control col-lg-6",'id'=>'User_endcontract', array(  'maxlength' => 16 )])->label('Final contrato *') ?>
                            </div>
                                <?php //Html::error($model,'endcontract', ['class' => 'help-block']); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?= $form->field($model, 'company_id')->dropDownList( \yii\helpers\ArrayHelper::map(Company::find()->all(), 'id', 'name') , ['prompt'=>'Choose...'])->label('Empresa *');?>
                            </div>
                            <small><?php echo Html::a( '(Crear nueva empresa)', array( 'company/create' ) ); ?></small>
                            <?php //echo Html::error( $model, 'company_id', ['class' => 'help-block'] ); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?php //print_r(WorkerProfiles::getDataForDropDown());die; ?>
                                <?php // $form->field($model, 'worker_dflt_profile'); ?>
                                 <?= $form->field($model, 'worker_dflt_profile')->dropDownList(WorkerProfiles::getDataForDropDown(), ['prompt'=>'Choose...'])->label('Perfil por defecto *');?>
                            </div>
                        <?php //echo Html::error( $model, 'worker_dflt_profile', ['class' => 'help-block'] ); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                 <?= $form->field($model, 'role')->dropDownList(User::getPriorityUser(Yii::$app->user->identity->role, $model->role), ['prompt'=>'Choose...'])->label('Permisos *');?>
                            </div>
                        <?php //echo Html::error( $model, 'role', ['class' => 'help-block'] ); ?>
                        </div>
        
                        <div class="row">
                            <?php //echo $form->labelEx( $model, 'role' ); ?>
                            <!--<div id="radioRole" style="padding: 3px;">-->
                            <?php
                             //echo $form->dropDownList( $model, 'role', User::getPriorityUser(Yii::$app->user->role, $model->role), array(
                                //'labelOptions' => array(
                                //    'style' => 'display: inline; width: 100px',
                                //    )
                           // ) );
                            ?>
                            <!--</div>-->
                            <?php //echo $form->error( $model, 'role' ); ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                        <?php //echo $form->labelEx( $model, 'hourcost' ); ?>
                                        <?php echo $form->field( $model, 'hourcost')->textInput(['class'=>"form-control col-lg-6", array(  'size' => 5, 'maxlength' => 5 )]); ?>
                            &nbsp;&nbsp;&nbsp;
                            <?php //echo $form->error( $model, 'hourcost' ); ?>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                        <?php //echo $form->labelEx( $model, 'weeklyhours' ); ?>
                                        <?php echo $form->field( $model, 'weeklyhours')->textInput(['class'=>"form-control col-lg-6", array(  'size' => 5, 'maxlength' => 5 )]); ?>
                            &nbsp;&nbsp;&nbsp;
                            <?php //echo $form->error( $model, 'weeklyhours' ); ?>
                            </div>
                        </div>
            
                        <script type="text/javascript">
                            var $j = jQuery.noConflict();
                    	$j(document).ready((function() {
                    		$j( 'input[id^="User_startcontract"],input[id^="User_endcontract"]' ).datepicker(
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
                                    
                                    $j( 'input[id^="User_startcontract"]' )
                    		.attr('readonly', 'readonly');
                    		
                    		$j( "div.ui-datepicker" ).css("font-size", "80%");
                    	}));
                        </script>
                        <script type="text/javascript">
                            $j(document).ready(function()
                            {
                                $j("#radioRole label").css( "display", "inline" );
                                //jQuery( "#radioRole" ).buttonset();
                            });
                        </script>
                        </br>

                        <?php if( Yii::$app->user->role == Roles::UT_ADMIN ||
                                  (Yii::$app->user->role != Roles::UT_ADMIN && $model->role != Roles::UT_ADMIN)) { ?>
                        <div class="row buttons">
                            <?php echo Html::submitButton( $model->isNewRecord ? 'Crear' : 'Guardar' , ['class'=>'btn btn-success']); ?>
                        </div>
                        <?php } ?>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
 