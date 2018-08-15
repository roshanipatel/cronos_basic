<?php
namespace app\controllers;

use Yii;
use app\components\CronosController;
use yii\web\HttpException;

class WorkerProfilesController extends CronosController
{

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = 'main';

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate()
    {
        if( isset( $_POST['Profiles'] ) )
        {
            $profiles = $_POST['Profiles'];
            if( !is_array( $profiles ) )
                throw new \yii\web\HttpException( 400, 'Invalid request. Please do not repeat this request again.' );
            foreach( $profiles as $profileId => $profilePrice )
            {
                $model = WorkerProfile::findOne( $profileId );
                if( $model === null )
                    throw new \yii\web\HttpException( 400, 'Invalid request. Please do not repeat this request again.' );
                $model->dflt_price = (float)$profilePrice;
                $model->save();
            }
            Yii::$app->user->setFlash( Constants::FLASH_OK_MESSAGE, 'Valores guardados con éxito' );
            $this->refresh();
        }
        $dbValues = WorkerProfile::find()->all();
        $profiles = array( );
        foreach( $dbValues as $profile )
        {
            $profiles[$profile['id']] = $profile['dflt_price'];
        }
        /*
          if(isset($_POST['WorkerProfiles']))
          {
          $model->attributes=$_POST['WorkerProfiles'];
          if($model->save())
          $this->redirect(array('view','id'=>$model->id));
          } */

        $this->render( 'update', array(
            'profiles' => $profiles,
        ) );
    }

}
