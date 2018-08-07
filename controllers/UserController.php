<?php
namespace app\controllers;

use Yii;
use app\components\CronosController;
use app\models\User;
use yii\data\Sort;
use yii\data\ActiveDataProvider;
class UserController extends CronosController {

    const MY_LOG_CATEGORY = 'controllers.UserController';

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    //public $layout = '/top_menu';

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
    public function actionView($id) {
        $this->render('view', array(
            'model' => $this->loadModel($id)
        ));
    }

    private function createUpdateRefactor($model, $renderView) {
        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['User'])) {
            $model->attributes = $_POST['User'];
            $transaction = Yii::$app->db->beginTransaction();
            try {
              
              if($model->validate()){

                  if ($model->save()) {
                      // We have the userid. Let's save the role
                      if (AuthAssignment::saveRoles($model->id, $model->role)) {
                          $transaction->commit();
                          Yii::$app->user->setFlash(Constants::FLASH_OK_MESSAGE, 'Usuario ' . $model->username . ' guardado con éxito');
                          Yii::$app->user->setFlash('oldUser', $model);
                          $this->refresh();
                      }
                  }
              }
                
            } catch (Exception $e) {
                Yii::log('Error saving User ' . $e, CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
                $transaction->rollback();
            }
        }

        $this->render($renderView, array(
            'model' => $model
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new User();
        $model->scenario = 'create';
        // Clean default values
       // $model->unsetAttributes();
        $this->createUpdateRefactor($model, 'create');
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $this->createUpdateRefactor($model, 'update');
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        if (Yii::$app->request->isPostRequest) {
            // we only allow deletion via POST request
            $model = $this->loadModel($id);
            if (!isset($model))
                throw new CHttpException(404, 'User not found');
            // Transaction required to delete roles
            $transaction = $model->dbConnection->beginTransaction();
            try {
                $model->delete();
                $transaction->commit();
            } catch (Exception $e) {
                Yii::log('Error deleting user ' . $model->id . ': ' . $e, CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
                $transaction->rollback();
                throw new CHttpException(500, 'Error deleting user');
            }
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin') );
        }
        else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new ActiveDataProvider('User', array(
                    'criteria' => array(
                        'with' => array('company'),
                    ),
                        ));
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        
        $filter = new User();
        $filter->scenario =  'search' ;
        //$filter->unsetAttributes();  // clear any default values
        
        $criteria = new yii\db\Query();
        if( isset( $_GET['User']['name'] ) )
            $criteria->compare('t.name', $_GET['User']['name'], true);
        if( isset( $_GET['User']['username'] ) )
            $criteria->compare('t.username', $_GET['User']['username'], true);
        if( isset( $_GET['User']['company_name'] ) )
            $criteria->compare('t.company_name', $_GET['User']['company_name'], true);
        
        $sort = new Sort();
		$sort->attributes = array(
			'name' => array(
				'asc' => 't.name ASC',
				'desc' => 't.name DESC',
			)
		);
        $oModel = new ActiveDataProvider([
                                'query' => $criteria->from('User'),
                                'pagination' => array(
                                    'pageSize' => Yii::$app->params['default_page_size'],
                                ),
                                'sort' => $sort,
                                ]);
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
    public function loadModel($id) {
        $model = User::model()->findByPk((int) $id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form') {
            echo CActiveForm::validate($model);
            Yii::$app->end();
        }
    }

}
