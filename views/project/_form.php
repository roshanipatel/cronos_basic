<div class="form">

    <?php
    $form = $this->beginWidget( 'CActiveForm', array(
                'id' => 'project-form',
                'enableAjaxValidation' => false,
                'focus' => array($model,'name'),
                    ) );
    ?>

    <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>

    <?php echo $form->errorSummary( $model ); ?>
    <?php if( Yii::$app->user->hasFlash(Constants::FLASH_OK_MESSAGE) ) { ?>
        <div class="resultOk"><p><?php echo Yii::$app->user->getFlash(Constants::FLASH_OK_MESSAGE)?></p></div>
    <?php } 
    
    $aAttributes = array();
    if( Yii::$app->user->hasCommercialPrivileges() ) { 
        $aAttributes = array('readonly'=>"readonly");
    }
    ?>
    <div class="row">
        <?php 
        echo $form->labelEx( $model, 'name' ); 
        echo $form->textField( $model, 'name', array_merge($aAttributes, array( 'size' => 45, 'maxlength' => 45 ))); 
        echo $form->error( $model, 'name' ); 
        ?>
    </div>
        <?php 
        if( Yii::$app->user->hasCommercialPrivileges() ) { 
                echo $form->hiddenField( $model, 'company_id'); 
            } else {
                ?>
        <div class="row">
            <?php
                echo $form->labelEx( $model, 'company_id' ); 
                echo $form->dropDownList( $model, 'company_id',
                    CHtml::listData( $companies, 'id', 'name' ),
                    array_merge($aAttributes, array(  'submit' => '',
                            'params' => array( 'new_select' => 1 )     
                        ))
                    );
            }
            ?>
        </div>
        &nbsp;&nbsp;&nbsp;
        <small><?php 
            if( !Yii::$app->user->hasCommercialPrivileges() ) { 
            echo CHtml::link( '(Crear nueva empresa)', array( 'company/create' ) ); 
            } ?></small>
        <?php echo $form->error( $model, 'company_id' ); ?>
    <?php if( !Yii::$app->user->hasCommercialPrivileges() ) {  ?>
    <div class="row">
        <?php 
        echo $form->labelEx( $model, 'status' ); 
        echo $form->dropDownList( $model, 'status', ProjectStatus::getDataForDropDown(), $aAttributes );
        echo $form->error( $model, 'status' ); 
        ?>
    </div>
    <?php } else {  
        echo $form->hiddenField( $model, 'status'); 
        } ?>
    <div class="row">
        <?php echo $form->labelEx( $model, 'statuscommercial' ); ?>
        <?php echo $form->dropDownList( $model, 'statuscommercial', ProjectStatus::getDataForDropDown() ); ?>
        <?php echo $form->error( $model, 'statuscommercial' ); ?>
    </div>    
    <?php if( !Yii::$app->user->hasCommercialPrivileges() ) { ?>
    <div class="row">
        <?php echo $form->labelEx( $model, 'cat_type' ); ?>
        <?php echo $form->dropDownList( $model, 'cat_type', ProjectCategories::getDataForDropDown(),
                    array_merge($aAttributes, array( 'prompt' => 'Sin categoría' ))); ?>
        <?php echo $form->error( $model, 'cat_type' ); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'open_time'); ?>
        <?php
        if ($model->open_time == "") {
            $model->open_time = date("d/m/Y");
        }
        $model->open_time = PHPUtils::removeHourPartFromDate($model->open_time);
        echo $form->textField($model, 'open_time', array(
            'maxlength' => 20
        ));
        ?>
        <?php echo $form->error($model, 'open_time'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'close_time'); ?>
        <?php
        if ($model->close_time != "") {
            $model->close_time = PHPUtils::removeHourPartFromDate($model->close_time);
        }
        echo $form->textField($model, 'close_time', array(
            'maxlength' => 20
        ));
        ?>
        <?php echo $form->error($model, 'open_time'); ?>
    </div>
    <?php if( Yii::$app->user->isAdmin() ) { ?>
    <div class="row">
        <label for="project_profiles_prices" style="margin-bottom: 3px">Precios por perfil
                &nbsp;&nbsp;&nbsp;
        <small><?php echo CHtml::link( '(Modificar precios por defecto)', array( 'workerProfiles/update' ), array('style'=>'font-weight: normal') ); ?></small>
        </label>

        <?php echo $form->error( $model, 'workerProfiles' ); ?>
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
                        echo $form->textField( $model, "workerProfiles[$profileId][price]", array(
                            'class' => 'currency',
                            'value' => $profilePrice,
                            ) ); ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <?php } ?>
        <div class="row">
        <?php echo $form->labelEx( $model, 'reporting' ); ?>
        <?php echo $form->dropDownList( $model, 'reporting', ReportingFreq::getDataForDropDown() ); ?>
        <?php echo $form->error( $model, 'reporting' ); ?>
    </div>    
            <div class="row">
        <?php echo $form->labelEx( $model, 'reportingtarget' ); ?>
        <?php
        echo $form->dropDownList( $model, 'reportingtarget',
                        CHtml::listData( $projectTargets, 'id', 'name' ),
                        array( 'style' => 'width: 450px; height: 150px;',
                            'multiple' => 'multiple',
                            'class' => 'multiselect_plugin', ) );
        ?>
        <?php echo $form->error( $model, 'reportingtarget' ); ?>
            </div>
            <div class="row">
        <?php 
        echo $form->labelEx( $model, 'reportingtargetcustom' ); 
        echo $form->textField( $model, 'reportingtargetcustom', array_merge($aAttributes, array( 'size' => 45, 'maxlength' => 45 ))); 
        echo $form->error( $model, 'reportingtargetcustom' ); 
        ?>
    </div>
        
        
        <div class="row">
    <?php echo $form->labelEx( $model, 'imputetypes' ); ?>
    <?php
    echo $form->dropDownList( $model, 'imputetypes',
                    CHtml::listData( $projectImputetypes, 'id', 'name' ),
                    array( 'style' => 'width: 450px; height: 150px;',
                        'multiple' => 'multiple',
                        'class' => 'multiselect_plugin', ) );
    ?>
    <?php echo $form->error( $model, 'imputetypes' ); ?>
    </div>
    <?php if( !Yii::$app->user->hasCommercialPrivileges() ) { ?>
        <div class="row">
        <?php echo $form->labelEx( $model, 'commercials' ); ?>
        <?php
                echo $form->dropDownList( $model, 'commercials',
                        CHtml::listData( $projectCommercials, 'id', 'name' ),
                        array( 'style' => 'width: 450px; height: 150px;',
                            'multiple' => 'multiple',
                            'class' => 'multiselect_plugin', ) );
        ?>
        <?php echo $form->error( $model, 'commercials' ); ?>
            </div>
        
    <div class="row">
        <?php echo $form->labelEx( $model, 'managers' ); ?>
        <?php
                echo $form->dropDownList( $model, 'managers',
                        CHtml::listData( $projectManagers, 'id', 'name' ),
                        array( 'style' => 'width: 450px; height: 150px;',
                            'multiple' => 'multiple',
                            'class' => 'multiselect_plugin', ) );
        ?>
        <?php echo $form->error( $model, 'managers' ); ?>
            </div>
            <div class="row">
        <?php echo $form->labelEx( $model, 'workers' ); ?>
        <?php
                echo $form->dropDownList( $model, 'workers',
                        CHtml::listData( $projectWorkers, 'id', 'name' ),
                        array( 'style' => 'width: 450px; height: 150px;',
                            'multiple' => 'multiple',
                            'class' => 'multiselect_plugin', ) );
        ?>
        <?php echo $form->error( $model, 'workers' ); ?>
            </div>
            <div class="row">
        <?php echo $form->labelEx( $model, 'customers' ); ?>
        <?php
                echo $form->dropDownList( $model, 'customers',
                        CHtml::listData( $projectCustomers, 'id', 'name' ),
                        array( 'style' => 'width: 450px; height: 150px;',
                            'multiple' => 'multiple',
                            'class' => 'multiselect_plugin', ) );
        ?>
        <?php echo $form->error( $model, 'customers' ); ?>
            </div>
            <br>

    <?php 
    }
    ?>

                  <div class="row">
                       <label for="project_max_hours" style="margin-bottom: 3px">Máximo de horas
                        &nbsp;&nbsp;&nbsp;<small style="font-weight:normal">(Dejar vacío ó 0 para no definir máximo)</small>
                        </label>
                      <?php echo $form->textField($model,'max_hours',
                              array_merge($aAttributes, array(
                                'maxlength' => '12',
                                'class' => 'currency',
                                ))); ?>
                      <?php echo $form->error($model,'max_hours'); ?>
                  </div>

                  <div class="row">
                       <label for="project_hours_warn_threshold" style="margin-bottom: 3px">Umbral de aviso
                        &nbsp;&nbsp;&nbsp;<small style="font-weight:normal">(Dejar vacío ó 0 para deshabilitar notificación)</small>
                        </label>
                  <?php echo $form->textField($model,'hours_warn_threshold',
                          array_merge($aAttributes, array(
                            'maxlength' => '12',
                            'class' => 'currency',
                            ))); ?>
                  <?php echo $form->error($model,'hours_warn_threshold'); ?>
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
    <div class="row buttons">
        <?php echo CHtml::submitButton( $model->isNewRecord ? 'Crear' : 'Guardar' ); ?>
    </div>

<?php $this->endWidget(); ?>
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

</div><!-- form -->