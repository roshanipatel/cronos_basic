<?php
namespace app\controllers;

use Yii;
use app\components\CronosController;
use app\models\db\ProjectExpense;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProjectExpensesController
 *
 * @author cescribano
 */
class ProjectExpenseController extends CronosController {

    const MY_LOG_CATEGORY = 'controllers.ProjectExpensesController';

    //public $layout = '//layouts/top_menu';

    public function actionApproveExpenses() {
        
        if (isset($_POST['doApprove']) && ( $_POST['doApprove'] == 1 )
                && isset($_POST['toApprove'])
                && is_array($_POST['toApprove'])) {
            $upts = ServiceFactory::createProjectExpenseService();
            foreach ($_POST['toApprove'] as $expenseId) {
                $upts->approveCost($expenseId, Yii::$app->user);
            }
            // Reload w/o post data
            // NO!!! now search is included and must be kept
            // TODO: find a nice way to prevent repost
            //$this->refresh();
        }

        $expenseSearch = $this->getProjectExpensesModelForSearchFromRequest();
        $expenseSearchService = ServiceFactory::createExpenseSearchService();
        $expenseSearch->status = TaskStatus::TS_NEW;
        $expenseSearch->owner = Yii::$app->user->id;
        $providers = $expenseSearchService->getExpenseSearchFormProviders($expenseSearch);
        $projectsCriteria = new yii\db\Query();
        $projectsCriteria->select = 't.id, t.name';
        $projectsCriteria->order = 't.id desc';
        if (!empty($expenseSearch->companyId)) {
            $projectsProvider = ServiceFactory::createProjectService()
                    ->findProjectsFromCustomerByCustomerId($expenseSearch->companyId, Yii::$app->user, $projectsCriteria, true, $expenseSearch->dateIni, $expenseSearch->dateEnd);
        } else {
            $projectsProvider = array();
        }

        $this->render('projectExpenses', CMap::mergeArray($providers, array(
                    'model' => $expenseSearch,
                    'projectsProvider' => $projectsProvider,
                    'approveCost' => true,
                    'onlyManagedByUser' => true)
                ));
    }

    private function getProjectExpensesModelForSearchFromRequest() {
        $expenseSearch = new ExpenseSearch('search');
        $expenseSearch->unsetAttributes();  // clear any default values
        if (isset($_GET['ExpenseSearch'])) {
            $expenseSearch->attributes = $_GET['ExpenseSearch'];
        }
        //If it not a project manager or administrative
        if (!Yii::$app->user->hasProjectManagerPrivileges() && !Yii::$app->user->hasAdministrativePrivileges()) {
            $expenseSearch->worker = Yii::$app->user->id;
        }
        
        if (Yii::$app->user->isProjectManager()) {
            $expenseSearch->owner = Yii::$app->user->id;
        }
        
        // Add sort if it's in the request
        if (isset($_GET['sort'])) {
            $expenseSearch->sort = $_GET['sort'];
        }
        
        return $expenseSearch;
    }

    public function loadModel($id) {
        $model = ProjectExpense::FindOne((int) $id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new ProjectExpense();
        $model->user_id = Yii::$app->user->id;
        $this->createUpdateRefactor($model, 'create');
    }

    public function actionDownloadPdf() {
        $model = $this->loadModel($_GET['id']);
        // Vamos a mostrar un PDF
        
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="expense_'.$model->companyName.'.pdf"');
        echo ($model->pdffile);
    }

    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $this->createUpdateRefactor($model, 'update');
    }

    /**
     * 
     * @param ProjectExpense $model
     * @param type $renderView
     */
    private function createUpdateRefactor($model, $renderView) {
        
        if (isset($_POST['ProjectExpense']) && (!isset($_POST['new_select']) )) {
            $ps = ServiceFactory::createProjectExpenseService();

            if ($model->isNewRecord) {
                if ($ps->createProjectExpense($model, $_POST['ProjectExpense'])) {
                    // Set flash if operation was successfull
                    Yii::$app->user->setFlash(Constants::FLASH_OK_MESSAGE, "Gasto del proyecto ".$model->project->name ." guardado con éxito");
                    $this->redirect(array('update', 'id' => $model->id));
                }
            } else {
                if ($ps->updateProjectExpense($model, $_POST['ProjectExpense'])) {
                    Yii::$app->user->setFlash(Constants::FLASH_OK_MESSAGE, "Gasto del proyecto ".$model->project->name ." guardado con éxito");
                    $this->refresh();
                }
            }
        }
        
        $projects = array();
        if (isset($model->project_id)) {
            $projects = Project::find()->where(['id'=>$model->project_id])->all();
        }
        
        $this->render('/projectExpense/'.$renderView, array(
            'model' => $model,
            'projects' => $projects,
        ));
    }

    private function getTaskSearchFromRequest() {
        $taskSearch = new TaskSearch('search');
        $taskSearch->unsetAttributes();
        if (isset($_REQUEST['TaskSearch'])) {
            $taskSearch->attributes = $_REQUEST['TaskSearch'];
            // If no validated, then create a new search record
            if (!$taskSearch->validate()) {
                Yii::log("TaskSearch not validated", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY);
                Yii::log(print_r($_REQUEST['TaskSearch'], true), CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY);
                $taskSearch = new TaskSearch('search');
                $taskSearch->unsetAttributes();
            }
        }
        // Add sort if it's in the request
        if (isset($_REQUEST['sort'])) {
            $taskSearch->sort = $_REQUEST['sort'];
        }
        return $taskSearch;
    }
    
    public function actionDelete($id) {
        if (Yii::$app->request->isPostRequest) {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_POST['ajax'])) {
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin') );
            }
        } else {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionExpenses() {

        $expenseSearch = $this->getProjectExpensesModelForSearchFromRequest();
        $expenseSearchService = ServiceFactory::createExpenseSearchService();
        $providers = $expenseSearchService->getExpenseSearchFormProviders($expenseSearch, Yii::$app->user);
        $projectsCriteria = new yii\db\Query();
        $projectsCriteria->select = 't.id, t.name';
        $projectsCriteria->order = 't.id desc';
        if (!empty($expenseSearch->companyId)) {
            $projectsProvider = ServiceFactory::createProjectService()
                    ->findProjectsFromCustomerByCustomerId($expenseSearch->companyId, Yii::$app->user, $projectsCriteria, false, $expenseSearch->dateIni, $expenseSearch->dateEnd);
        } else {
            $projectsProvider = array();
        }
        $this->render('projectExpenses', CMap::mergeArray($providers, array(
                    'model' => $expenseSearch,
                    'projectsProvider' => $projectsProvider,
                    'approveCost' => false,
                    'onlyManagedByUser' => false)
                ));
    }
    
    /**
     * Sends a file in csv format with the results of the expense search
     */
    public function actionExportToCSV() {
        $expenseSearch = $this->getProjectExpensesModelForSearchFromRequest();
        $content = ServiceFactory::createExpenseSearchService()->getCSVContentFromSearch($expenseSearch);
        if (!$content) {
            $content = "No results";
        } else {
            // Convert to UTF-16 for working around excel bug (not handling utf-8 properly)
            // and remove BOM, that Excel seems not to like it either
            //$content = substr( iconv( 'UTF-8', 'UTF-8', $content ), 2 );
            $content = utf8_decode($content);
        }
        $filename = 'expenses_' . date('Ymd') . '.csv';
        Yii::$app->getRequest()->sendFile($filename, $content, "text/csv", true);
    }
}

?>
