<h1>Actualizar Perfiles de trabajador</h1>

<div class="form">

    <?php
    $form = $this->beginWidget( 'CActiveForm', array(
                'id' => 'worker-profiles-form',
                'enableAjaxValidation' => false,
                    ) );
    ?>

    <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>

    <div class="row">
        <table style="padding: 3px;margin: 3px; width: 10%;border-collapse: collapse;">
            <?php foreach( $profiles as $profileId => $profilePrice )
            {
 ?>

            <tr>
                <td style="text-align: right;">
                    <label class="required" for="Profiles_<?php echo $profileId ?>">
                        <?php echo WorkerProfiles::toString( $profileId ) ?>:
                    </label>
                </td>
                <td>
                    <?php echo CHtml::textField( "Profiles[$profileId]", $profilePrice, array(
                        'class' => 'currency'
                    ) ); ?></td>
                </tr>
<?php } ?>
        </table>
    </div>
   <script type="text/javascript" src="<?php echo Yii::$app->request->baseUrl; ?>/js/plugins/jquery.numeric.js"></script>
   <script type="text/javascript">
       jQuery(".currency").numeric();
   </script>
    <div class="row buttons">
<?php echo CHtml::submitButton( 'Guardar' ); ?>
        </div>

<?php $this->endWidget(); ?>

</div><!-- form -->