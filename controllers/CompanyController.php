<?php
namespace app\controllers;

use Yii;
use app\components\CronosController;
use yii\data\Sort;
use yii\data\ActiveDataProvider;

class CompanyController extends CronosController
{

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/top_menu';

    /**
     * @return array action filters
      public function filters()
      {
      return array(
      'accessControl', // perform access control for CRUD operations
      );
      }
     */

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView( $id )
    {
        $this->render( 'view', array(
            'model' => $this->loadModel( $id ),
                ) );
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model = new Company;
        // Clean
//        $model->unsetAttributes();
        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if( isset( $_POST['Company'] ) )
        {
            $model->attributes = $_POST['Company'];
            if( $model->save() )
            {
                Yii::$app->user->setFlash( Constants::FLASH_OK_MESSAGE, 'Empresa ' . $model->name . ' guardada con éxito' );
                $this->redirect( array( 'update', 'id' => $model->id ) );
            }
        }

        $this->render( 'create', array(
            'model' => $model,
                ) );
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate( $id )
    {
        $model = $this->loadModel( $id );

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if( isset( $_POST['Company'] ) )
        {
            $model->attributes = $_POST['Company'];
            if( $model->save() )
            {
                Yii::$app->user->setFlash( Constants::FLASH_OK_MESSAGE, 'Empresa ' . $model->name . ' guardada con éxito' );
                $this->redirect( array( 'update', 'id' => $model->id ) );
            }
        }

        $this->render( 'update', array(
            'model' => $model,
                ) );
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete( $id )
    {
        if( Yii::$app->request->isPostRequest )
        {
            // we only allow deletion via POST request
            $this->loadModel( $id )->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if( !isset( $_GET['ajax'] ) )
                $this->redirect( isset( $_POST['returnUrl'] ) ? $_POST['returnUrl'] : array( 'admin' ) );
        }
        else
            throw new CHttpException( 400, 'Invalid request. Please do not repeat this request again.' );
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider( 'Company' );
        $this->render( 'index', array(
            'dataProvider' => $dataProvider,
                ) );
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $filter = new Company( 'search' );
        $filter->unsetAttributes();  // clear any default values
        
        $criteria = new yii\db\Query();
        if( isset( $_GET['Company']['name'] ) )
            $criteria->compare('t.name', $_GET['Company']['name']);
        
        $sort = new Sort();
		$sort->attributes = array(
			'name' => array(
				'asc' => 't.name ASC',
				'desc' => 't.name DESC',
			)
		);

        $oModel = new ActiveDataProvider(
						array(
							'query' => $criteria->from('User'),
							'pagination' => array(
								'pageSize' => Yii::$app->params->default_page_size,
							),
							'sort' => $sort,
				));

        $this->render( 'admin', array(
            'model' => $oModel,
            'filter' => $filter,
                ) );
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel( $id )
    {
        $model = Company::model()->findByPk( (int)$id );
        if( $model === null )
            throw new CHttpException( 404, 'The requested page does not exist.' );
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation( $model )
    {
        if( isset( $_POST['ajax'] ) && $_POST['ajax'] === 'company-form' )
        {
            echo CActiveForm::validate( $model );
            Yii::$app->end();
        }
    }

}
