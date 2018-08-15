<?php error_reporting(E_ALL); ini_set('display_errors', 1);
    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use app\components\utils\PHPUtils;
    use app\models\db\Company;
    use app\models\User;
    use app\models\enums\WorkerProfiles;
    use app\models\enums\Roles;
    use app\components\utils\DateTime;

 ?>

<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">Actualizar Perfiles de trabajador</h1>
  </div>
</div>
<div class="row" >
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Actualizar Perfiles de trabajador
            </div>
            <div class="panel-body">
                <?php
                    $form = ActiveForm::begin([
                        'id' => 'worker-profiles-form',
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-12\">{input}</div>\n<div class=\"col-12\">{error}</div>",
                           // 'labelOptions' => ['class' => 'form-control'],
                        ],
                        'enableClientValidation'=>false,
                    ]); ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-control">
                            <p class="note">Los campos con <span class="required">*</span> son obligatorios.</p>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-control">
                             <table style="padding: 3px;margin: 3px; width: 10%;border-collapse: collapse;">
                                <?php echo "<pre>";print_r($profiles);exit; foreach( $profiles as $profileId => $profilePrice )
                                    {
                                 ?>
                                 <tr>
                                    <td style="text-align: right;">
                                        <label class="required" for="Profiles_<?php echo $profileId ?>">
                                            <?php echo WorkerProfiles::toString( $profileId ) ?>:
                                        </label>
                                    </td>
                                    <td>
                                        <?php echo Html::textField( "Profiles[$profileId]", $profilePrice, array('class' => 'currency') ); ?>
                                    </td>
                               </tr>
                                <?php } ?>
                            </table> 
                        </div>
                    </div>
                </div>
                <script type="text/javascript" src="<?php echo Yii::$app->request->baseUrl; ?>/js/plugins/jquery.numeric.js"></script>
                <script type="text/javascript">
                    jQuery(".currency").numeric();
                </script>
                <div class="row">
                    <div class="form-control">
                        <?php echo Html::submitButton(  'Guardar' , ['class'=>'btn btn-success']); ?>
                        <?php echo Html::a( 'Volver',['#'] , ["onclick"=> "history.back();return false;" , "class"=>"btn btn-danger"] ); ?>
                    </div>
                </div>
                <?php $this->endWidget(); ?>
            </div>
        </div>
    </div>
</div>

