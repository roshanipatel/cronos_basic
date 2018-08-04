<div id="task-calendar"></div>

<div id="task-input-form" style="display:none">
    <div class="form">

        <?php
        $form = $this->beginWidget( 'CActiveForm', array(
                    'id' => 'user-project-task-form',
                    'focus' => array( $model, 'frm_customer_name' ),
                ) );
        ?>

        <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>

        <div id="user-task-result"></div>

        <?php echo $form->hiddenField($model, 'id' ); ?>
        <?php echo $form->hiddenField($model, 'user_id' ) ?>
        <div class="row">
            <?php echo $form->labelEx( $model, 'frm_customer_name' ); ?>
            <?php
            echo $form->textField( $model, 'frm_customer_name', array(
                'id' => 'company_name'
            ) );
            ?>
<?php
if( !$isWorker )
{
    ?>
                &nbsp;&nbsp;&nbsp;
                <small><?php echo CHtml::link( '(Crear nueva empresa)', array( 'company/create' ) ); ?></small>
            <?php } ?>
            <?php echo $form->error( $model, 'frm_customer_name' ); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx( $model, 'project_id' ); ?>
<?php
echo $form->dropDownList( $model, 'project_id', CHtml::listData( array(), 'id', 'name' ), array(
    'id' => 'customer_projects',
    'prompt' => 'Seleccione cliente',
) );
?>
            <!-- Loading image placeholder -->
            <span id="loadingProjects"></span>
            <?php
            if( !$isWorker )
            {
                ?>
                &nbsp;&nbsp;&nbsp;
                <small><?php echo CHtml::link( '(Crear nuevo proyecto)', array( 'project/create' ) ); ?></small>
        <?php } ?>
        <?php echo $form->error( $model, 'project_id' ); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx( $model, 'imputetype_id' ); ?>
            <?php
            echo $form->dropDownList( $model, 'imputetype_id', CHtml::listData( array(), 'id', 'name' ), array(
                'id' => 'imputetype_projects',
                'prompt' => 'Seleccione un tipo de imputación',
            ) );
            ?>
            <!-- Loading image placeholder -->
            <span id="loadingImputetypes"></span>
        <?php echo $form->error( $model, 'imputetype_id' ); ?>
        </div>

    <div class="row">
        <?php echo $form->hiddenField($model, 'frm_date_ini' ) ?>
        <?php echo $form->hiddenField($model, 'frm_date_end' ) ?>
        <label class="required">Horas <span class="required">*</span></label>
        <?php 
            $aDateProps = array(
                'size' => 12,
                'maxlength' => 10
                );
            
            echo CHtml::textField('frm_date_ini2', '', $aDateProps); ?>
        <?php echo $form->textField( $model, 'frm_hour_ini', array(
            'size' => 6,
            'maxlength' => 10,
            'onchange' => 'checkHourSemantics("hour_ini")'
            ) ); ?>
        a
        <?php echo $form->textField( $model, 'frm_hour_end', array(
            'size' => 6,
            'maxlength' => 10,
            'onchange' => 'checkHourSemantics("hour_end")'
            ) ); ?>

        <?php echo $form->error( $model, 'frm_date_ini' ); ?>
        <?php echo $form->error( $model, 'frm_hour_ini' ); ?>
        <?php echo $form->error( $model, 'frm_hour_end' ); ?>
        <?php echo $form->error( $model, 'frm_date_end' ); ?>
    </div>
            <?php
            if( !$isWorker )
            {
                ?>
            <div class="row">
                <?php echo $form->labelEx( $model, 'status' ); ?>
                <?php echo $form->dropDownList( $model, 'status', TaskStatus::getDataForDropDown() ); ?>
            <?php echo $form->error( $model, 'status' ); ?>
            </div>
            <div class="row">
                <?php echo $form->labelEx( $model, 'profile_id' ); ?>
                <?php echo $form->dropDownList( $model, 'profile_id', WorkerProfiles::getDataForDropDown() ); ?>
                <?php echo $form->error( $model, 'profile_id' ); ?>
            </div>
<?php } ?>

        <div class="row">
            <?php echo $form->labelEx( $model, 'task_description' ); ?>
            <?php echo $form->textArea( $model, 'task_description', array( 'cols' => 40, 'maxlength' => 1024 ) ); ?>
<?php echo $form->error( $model, 'task_description' ); ?>
        </div>

        <div class="row">
			<?php echo $form->labelEx( $model, 'ticket_id' ); ?>
			<?php echo $form->textField( $model, 'ticket_id', array( 'size' => 40, 'maxlength' => 128 ) ); ?>
            &nbsp;&nbsp;&nbsp;
            <small><?php echo CHtml::link( '(Previsualizar ticket)', 'javascript:testTicket()' ); ?></small>
            <?php echo $form->error( $model, 'ticket_id' ); ?>
        </div>
        <div class="row">
			<?php echo $form->checkBox( $model, 'is_extra' ); ?>
			<?php echo $form->labelEx( $model, 'is_extra', array(
				'style' => 'display: inline; position: relative; top: -2px'
			) ); ?>
            <?php echo $form->error( $model, 'is_extra' ); ?>
        </div>
<?php if( Yii::app()->user->isAdmin() ) { ?>
        <div class="row">
			<?php echo $form->checkBox( $model, 'is_billable' ); ?>
			<?php echo $form->labelEx( $model, 'is_billable', array(
				'style' => 'display: inline; position: relative; top: -2px'
			) ); ?>
            <?php echo $form->error( $model, 'is_billable' ); ?>
        </div>
<?php } ?>


        <div class="row">
            <span id="savingTasks"></span>
        </div>

        <br>
<?php $this->endWidget(); ?>
    </div>
</div><!-- form -->

<?php
$this->renderPartial('_form_js', array(
    'customers' => $customers,
));
?>
<?php
$this->renderPartial('_calendar_js', array(
    'model' => $model,
    'workers' => $workers,
    'customers' => $customers,
    'showExtendedFields' => ! $isWorker,
    'isWorker' => $isWorker,
    'showDate' => $showDate,
    'showUser' => $showUser,
));
?>