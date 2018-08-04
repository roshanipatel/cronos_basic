<div class="form">

    <?php
    $form = $this->beginWidget( 'CActiveForm', array(
                'id' => 'worker-profiles-form',
                'enableAjaxValidation' => false,
            ) );
    ?>

	<p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>

    <div class="row">
        <table style="padding: 3px;margin: 3px; width: 10%; border: 2px solid">
<?php foreach( $profiles as $profileId => $profilePrice )
    { ?>

            <tr><td style="text-align: right"><?php echo WorkerProfiles::toString( $profileId ) ?></td>
                <td><?php echo CHtml::textField( $profileId, $profilePrice ); ?></td>
            </tr>
<?php } ?>
        </table>
    </div>

    <div class="row buttons">
    <?php echo CHtml::submitButton( 'Guardar' ); ?>
        </div>

<?php $this->endWidget(); ?>

</div><!-- form -->