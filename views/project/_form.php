<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Constants;
use app\models\enums\ProjectStatus;
use app\components\utils\PHPUtils;
use app\models\enums\WorkerProfiles;
use  app\models\enums\ReportingFreq;
?>
<div class="form">

    <?php
        $form = ActiveForm::begin([
            'id' => 'project-form',
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-12\">{input}</div>\n<div class=\"col-12\">{error}</div>",
            ],
            'enableClientValidation'=>false,
            'validateOnSubmit' => true,
        ]); ?>

    <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>

    <?php //echo $form->errorSummary( $model ); ?>
    <?php if( Yii::$app->session->hasFlash(Constants::FLASH_OK_MESSAGE) ) { ?>
        <div class="resultOk"><p><?php echo Yii::$app->session->getFlash(Constants::FLASH_OK_MESSAGE)?></p></div>
    <?php } 
    
    $aAttributes = array();
    $aAttributes['class'] ="form-control";
    if( Yii::$app->user->hasCommercialPrivileges() ) { 
        $aAttributes['readonly'] = "readonly";
    }

    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <?php 
                echo $form->field( $model, 'name')->textInput(array_merge($aAttributes, array( 'size' => 45, 'maxlength' => 45 ))); 
                ?>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
                <?php 
                if( Yii::$app->user->hasCommercialPrivileges() ) { 
                        echo $form->hiddenField( $model, 'company_id'); 
                } else {
                $aAttributes['prompt'] = 'Choose...' ;
                ?>
                    <div class="row">
                        <?= $form->field($model, 'company_id')->dropDownList( \yii\helpers\ArrayHelper::map($companies, 'id', 'name') ,array_merge($aAttributes, array(  'submit' => '',
                                        'params' => array( 'new_select' => 1 ))))->label('Empresa *'); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="col-lg-12">
            &nbsp;&nbsp;&nbsp;
            <small>
                <?php 
                if( !Yii::$app->user->hasCommercialPrivileges() ) { 
                echo Html::a( '(Crear nueva empresa)', array( 'company/create' ) ); 
                } ?>
            </small>
        <div>
        <?php if( !Yii::$app->user->hasCommercialPrivileges() ) {  ?>
            <div class="col-lg-12">
                <div class="form-group">
                    <?= $form->field($model, 'status')->dropDownList( ProjectStatus::getDataForDropDown() ,$aAttributes);//->label('Empresa *'); ?>
                </div>
            </div>
        <?php } else {  
            echo $form->hiddenField( $model, 'status'); 
            } ?>
        <div class="col-lg-12">
            <div class="form-group">
                <?= $form->field($model, 'statuscommercial')->dropDownList( ProjectStatus::getDataForDropDown() ,$aAttributes);//->label('Empresa *'); ?>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
            </div>
        </div>
        <?php if( !Yii::$app->user->hasCommercialPrivileges() ) { ?>
            <div class="col-lg-12">
                <div class="form-group">
                    <?= $form->field($model, 'cat_type')->dropDownList( ProjectStatus::getDataForDropDown() ,array_merge($aAttributes, array( 'prompt' => 'Sin categoría' )));//->label('Empresa *'); ?>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-group">
                    <?php //echo $form->labelEx($model, 'open_time'); ?>
                    <?php
                        if ($model->open_time == "") {
                            $model->open_time = date("d/m/Y");
                        }
                        $model->open_time = PHPUtils::removeHourPartFromDate($model->open_time);
                        echo $form->field($model, 'open_time')->textInput(array(
                            'maxlength' => 20
                        ));
                    ?>
                    <?php //echo $form->error($model, 'open_time'); ?>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="form-group">
                    <?php //echo $form->labelEx($model, 'close_time'); ?>
                    <?php
                    if ($model->close_time != "") {
                        $model->close_time = PHPUtils::removeHourPartFromDate($model->close_time);
                    }
                    echo $form->field($model, 'close_time')->textInput(array(
                        'maxlength' => 20
                    ));
                    ?>
                    <?php //echo $form->error($model, 'open_time'); ?>
                </div>
            </div>
            <?php if( Yii::$app->user->isAdmin() ) { ?>
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="project_profiles_prices" style="margin-bottom: 3px">Precios por perfil
                                &nbsp;&nbsp;&nbsp;
                        <small><?php echo Html::a( '(Modificar precios por defecto)', array( 'workerProfiles/update' ), array('style'=>'font-weight: normal') ); ?></small>
                        </label>

                        <?php //echo $form->error( $model, 'workerProfiles' ); ?>
                        <table style="padding: 3px;margin: 3px; width: 10%;border-collapse: collapse;">
                            <?php
                            //foreach( $model->workerProfiles as $profilePriceForProject )
                            for( $i = 0; $i < count( $model->workerProfiles ); $i++ )
                            {
                                $profileId = $model->workerProfiles[$i]->worker_profile_id;
                                $profilePrice = $model->workerProfiles[$i]->price;
                            ?>
                                <tr>
                                    <td style="text-align: right; padding: 0px 5px 0px 0px">
                                        <label class="required" for="Profiles_<?php echo $profileId ?>">
                                            <?php echo WorkerProfiles::toString( $profileId ) ?>:
                                        </label>
                                    </td>
                                    <td style="padding: 0px 0px 0px 2px">
                                        <?php
                                        echo $form->field( $model, "workerProfiles[$profileId]")->textInput( array(
                                            'class' => 'currency',
                                            'value' => $profilePrice,
                                            ) ); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            <?php } ?>
            <div class="col-lg-12">
                <div class="form-group">
                    <?php //echo $form->labelEx( $model, 'reporting' ); ?>
                    <?php echo $form->field( $model, 'reporting')->dropDownList(ReportingFreq::getDataForDropDown() ); ?>
                    <?php //echo $form->error( $model, 'reporting' ); ?>
                </div>
            </div>    
            <div class="col-lg-12">
                <div class="form-group">
                    <?php //echo $form->labelEx( $model, 'reportingtarget' ); ?>
                    <?php
                    echo $form->field( $model, 'reportingtarget')->dropDownList(
                                    \yii\helpers\ArrayHelper::map( $projectTargets, 'id', 'name' ),
                                    array( 'style' => 'width: 450px; height: 150px;',
                                        'multiple' => 'multiple',
                                        'class' => 'multiselect_plugin', ) );
                    ?>
                    <?php //echo $form->error( $model, 'reportingtarget' ); ?>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-group">
                   <?php 
                    //echo $form->labelEx( $model, 'reportingtargetcustom' ); 
                    echo $form->field( $model, 'reportingtargetcustom')->textInput(array_merge($aAttributes, array( 'size' => 45, 'maxlength' => 45 ))); 
                    //echo $form->error( $model, 'reportingtargetcustom' ); 
                    ?>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-group">
                    <?php //echo $form->labelEx( $model, 'imputetypes' ); ?>
                    <?php
                    echo $form->field( $model, 'imputetypes')->dropDownList(
                                    \yii\helpers\ArrayHelper::map( $projectImputetypes, 'id', 'name' ),
                                    array( 'style' => 'width: 450px; height: 150px;',
                                        'multiple' => 'multiple',
                                        'class' => 'multiselect_plugin', ) );
                    ?>
                    <?php //echo $form->error( $model, 'imputetypes' ); ?>
                </div>
            </div>
            <?php if( !Yii::$app->user->hasCommercialPrivileges() ) { ?>
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php //echo $form->labelEx( $model, 'commercials' ); ?>
                        <?php
                                echo $form->field( $model, 'commercials')->dropDownList(
                                        \yii\helpers\ArrayHelper::map( $projectCommercials, 'id', 'name' ),
                                        array( 'style' => 'width: 450px; height: 150px;',
                                            'multiple' => 'multiple',
                                            'class' => 'multiselect_plugin', ) );
                        ?>
                        <?php //echo $form->error( $model, 'commercials' ); ?>
                    </div>
                </div>
            
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php //echo $form->labelEx( $model, 'managers' ); ?>
                        <?php
                                echo $form->field( $model, 'managers')->dropDownList(
                                        \yii\helpers\ArrayHelper::map( $projectManagers, 'id', 'name' ),
                                        array( 'style' => 'width: 450px; height: 150px;',
                                            'multiple' => 'multiple',
                                            'class' => 'multiselect_plugin', ) );
                        ?>
                        <?php //echo $form->error( $model, 'managers' ); ?>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php //echo $form->labelEx( $model, 'workers' ); ?>
                        <?php
                                echo $form->field( $model, 'workers')->dropDownList(
                                        \yii\helpers\ArrayHelper::map( $projectWorkers, 'id', 'name' ),
                                        array( 'style' => 'width: 450px; height: 150px;',
                                            'multiple' => 'multiple',
                                            'class' => 'multiselect_plugin', ) );
                        ?>
                        <?php //echo $form->error( $model, 'workers' ); ?>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php //echo $form->labelEx( $model, 'customers' ); ?>
                        <?php
                                echo $form->field( $model, 'customers')->dropDownList(
                                        \yii\helpers\ArrayHelper::map( $projectCustomers, 'id', 'name' ),
                                        array( 'style' => 'width: 450px; height: 150px;',
                                            'multiple' => 'multiple',
                                            'class' => 'multiselect_plugin', ) );
                        ?>
                        <?php //echo $form->error( $model, 'customers' ); ?>
                    </div>
                </div>
                <br>
            <?php 
            }
            ?>
            <div class="col-lg-12">
                <div class="form-group">
                   <label for="project_max_hours" style="margin-bottom: 3px">Máximo de horas
                    &nbsp;&nbsp;&nbsp;<small style="font-weight:normal">(Dejar vacío ó 0 para no definir máximo)</small>
                    </label>
                      <?php echo $form->field($model,'max_hours')->textInput(                            array_merge($aAttributes, array(
                                'maxlength' => '12',
                                'class' => 'currency',
                                ))); ?>
                      <?php //echo $form->error($model,'max_hours'); ?>
                </div>    
            </div>
            <div class="col-lg-12">
                <div class="form-group">>
                       <label for="project_hours_warn_threshold" style="margin-bottom: 3px">Umbral de aviso
                        &nbsp;&nbsp;&nbsp;<small style="font-weight:normal">(Dejar vacío ó 0 para deshabilitar notificación)</small>
                        </label>
                  <?php echo $form->field($model,'hours_warn_threshold')->textInput(                        array_merge($aAttributes, array(
                            'maxlength' => '12',
                            'class' => 'currency',
                            ))); ?>
                  <?php //echo $form->error($model,'hours_warn_threshold'); ?>
                </div>
            </div>
            <!-- Multiselect -->
            <script type="text/javascript" src="<?php echo Yii::$app->request->baseUrl; ?>/js/multiselect/plugins/localisation/jquery.localisation-min.js"></script>
            <script type="text/javascript" src="<?php echo Yii::$app->request->baseUrl; ?>/js/multiselect/plugins/tmpl/jquery.tmpl.1.1.1.js"></script>
            <script type="text/javascript" src="<?php echo Yii::$app->request->baseUrl; ?>/js/multiselect/plugins/blockUI/jquery.blockUI.js"></script>
            <script type="text/javascript" src="<?php echo Yii::$app->request->baseUrl; ?>/js/multiselect/ui-multiselect.js"></script>
            <script type="text/javascript" src="<?php echo Yii::$app->request->baseUrl; ?>/js/multiselect/locale/ui-multiselect-es.js"></script>
            <script type="text/javascript" src="<?php echo Yii::$app->request->baseUrl; ?>/js/plugins/jquery.numeric.js"></script>
            <script type="text/javascript">
                function makeMultiselect( selector )
                {
                    // Apply multiselect plugin
                    jQuery(selector).multiselect(
                    {

                        sortable: false,
                        dividerLocation: 0.5,
                        droppable: 'none'
                    })
                };
                jQuery(document).ready(function(){
                    // Translate to spanish
                    jQuery.localise(
                    'ui-multiselect',
                    'es',
                    true,
                    ['<?php echo Yii::$app->request->baseUrl; ?>/js/multiselect/',
                        '<?php echo Yii::$app->request->baseUrl; ?>/js/multiselect/locale/']
                    );
                    makeMultiselect( "#Project_managers" );
                    makeMultiselect( "#Project_customers" );
                    makeMultiselect( "#Project_workers" );
                    makeMultiselect( "#Project_commercials" );
                    makeMultiselect( "#Project_imputetypes" );
                    makeMultiselect( "#Project_reportingtarget" );
                    jQuery(".currency").numeric();
                    jQuery(".integer").numeric(false);
                });
            </script>
     <?php } ?>
     <div class="col-lg-12">
        <?php echo Html::submitButton( $model->isNewRecord ? 'Crear' : 'Guardar' , ['class'=>'btn btn-success']); ?>
    </div>

 <?php ActiveForm::end(); ?>
    <script type="text/javascript">
        jQuery(document).ready((function() {
            jQuery( 'input[id^="Project_open_time"]' )
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
            jQuery( 'input[id^="Project_close_time"]' )
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
    </div>
</div><!-- form -->