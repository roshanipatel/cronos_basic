<?php
namespace app\controllers;

use Yii;
use app\components\CronosController;
use app\models\db\UserProjectTask;
use app\models\db\User;
use app\models\db\Company;
use app\models\LoginForm;
use app\models\form\TaskSearch;
use app\services\ServiceFactory;
use yii\data\ActiveDataProvider;
use app\models\db\Calendar;
class UserProjectTaskController extends CronosController {
   
    const MY_LOG_CATEGORY = 'controllers.UserProjectTaskController';
    const PARAM_SELECT_CUSTOMER = 'sel_customer';


    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    //public $layout = '/main';

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }
    
    public function actionCalendarUpload() {
        
        $sErrorMessage = "";
        if (isset($_FILES['calendarUploadFile'])) {
            if (
                !isset($_FILES['calendarUploadFile']['error']) ||
                is_array($_FILES['calendarUploadFile']['error'])
            ) {
                throw new RuntimeException('Invalid parameters.');
            }
            
            spl_autoload_unregister(array('YiiBase', 'autoload'));
            
            ini_set('include_path', ini_get('include_path') . ';/var/www/cronos-test.open3s.int/protected/extensions');
            
            /** PHPExcel */
            include 'PHPExcel.php';
            
            spl_autoload_register(array('YiiBase','autoload')); 
            
            $inputFileName = $_FILES['calendarUploadFile']['tmp_name'];
            
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $oPhpExcel = $objReader->load($inputFileName);
                
                //$oPhpExcel = new PHPExcel();
                $sheet = $oPhpExcel->getSheet(0); 
                $highestRow = 100; 
                
                for ($row = 1; $row <= $highestRow; $row++){ 
                    //  Read a row of data into an array
                    $sFecha = $sheet->getCell("A".$row)->getFormattedValue();
                    $sAction = $sheet->getCell("B".$row)->getValue();
                    
                    if ($sFecha == "" && $sAction == "") {
                        continue;
                    }
                    
                    $sDay = PHPUtils::convertStringToPHPDateTime($sFecha);
                    
                    if ($sAction == Calendar::FESTIVO_ELIMINAR) {
                            
                        $aCalendars = Calendar::find()->where("day = '".PHPUtils::convertPHPDateTimeToDBDateTime($sDay)."'")->all();
                        foreach($aCalendars as $oCalendar) {
                            $oCalendar->delete();
                        }
                        
                    } else {
                        
                        $model = new Calendar();
                        $model->day = PHPUtils::convertPHPDateTimeToDBDateTime($sDay);
                        $model->city = $sAction;
                        $model->save();
                    }
                }
                
            } catch(Exception $e) {
                $sErrorMessage = $e->getMessage();
            }
        }
        
        
        $model = new Calendar( );
        $model->scenario = 'search';
        //$model->unsetAttributes();  // clear any default values
        
        $this->render('/UserProjectTask/_calendarUpload', array('model' => $model, 'errorMessage' => $sErrorMessage));
    }

    public function actionCalendar($taskId = NULL) {
        if (empty($taskId)) { 
            $model = new UserProjectTask;
           // $model->unsetAttributes();
            $model->user_id = Yii::$app->user->id;
            $oUser = User::find()->where(['id'=>$model->user_id])->one();
            $model->profile_id = $oUser->worker_dflt_profile;
        } else {
            $model = $this->loadModel($taskId);
        }
        //$model = new LoginForm;
        /*echo "<pre/>";
        print_r($model);die();*/
        // Don't convert to int: not enough with 32 bits
        $date = ( isset($_REQUEST['timestamp']) && is_numeric($_REQUEST['timestamp'])) ? $_REQUEST['timestamp'] : null;
        $userId = ( isset($_REQUEST['user']) && User::isValidID($_REQUEST['user'])) ? (int) $_REQUEST['user'] : null;
        $isWorker = !Yii::$app->user->hasDirectorPrivileges();
        if ($isWorker && $userId !== NULL && $userId != Yii::$app->user->id) {
            throw new CHttpException(403, 'No tiene acceso a esta página');
        }

       return $this->render('/UserProjectTask/task_calendar',[
            'model' => $model,
            'isWorker' => $isWorker,
            'workers' => array_merge(array_merge(ServiceFactory::createUserService()->findCommercials(true), 
                                     ServiceFactory::createUserService()->findProjectWorkers(true)),
                                     ServiceFactory::createUserService()->findProjectManagers(true)),
            'customers' => Company::find()->orderBy('name asc')->all(),
            'showDate' => $date,
            'showUser' => $userId
        ]);
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete() {
        if (Yii::$app->request->isPostRequest) {
            $id = (int) $_REQUEST['id'];
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']) && !Yii::$app->request->isAjaxRequest) {
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin') );
            } else {
                $result['code'] = self::OP_RESULT_OK;
                $result['msg'] = CHtml::openTag('div', array('class' => 'resultOk-short'))
                        . 'Tarea borrada con éxito'
                        . CHtml::closeTag('div');
                echo json_encode($result);
                Yii::$app->end();
            }
        }
        else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new ActiveDataProvider('UserProjectTask', array(
                    'criteria' => array(
                        'with' => array('worker', 'project'),
                    ),
                ));
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionApproveTasks() {       
        if (isset($_POST['doApprove']) && ( $_POST['doApprove'] == 1 )
                && isset($_POST['toApprove'])
                && is_array($_POST['toApprove'])
                && is_array($_POST['pc'])
                && is_array($_POST['pr'])
                //&& is_array($_POST['comment'])
                && is_array($_POST['pj'])) {
            
            $upts = ServiceFactory::createUserProjectTaskService();
            foreach ($_POST['toApprove'] as $taskId) {
                if (empty($_POST['pr'][$taskId])
                        || empty($_POST['pj'][$taskId]))
                    throw new CHttpException(400, 'Invalid request', 400);
                $profile = $_POST['pr'][$taskId];
                $project = $_POST['pj'][$taskId];
                $company = $_POST['pc'][$taskId];
                $imputetype = $_POST['pti'][$taskId];
                $comment = "";
                if (isset($_POST['comment'][$taskId])) {
                    $comment = $_POST['comment'][$taskId];
                }
                
                $ticketId = "";
                if (isset($_POST['ticket'][$taskId])) {
                    $ticketId = $_POST['ticket'][$taskId];
                }
                
                $upts->approveTask($taskId, $profile, Yii::$app->user, $project, $comment, $ticketId, $imputetype);
            }
            // Reload w/o post data
            // NO!!! now search is included and must be kept
            // TODO: find a nice way to prevent repost
            //$this->refresh();
        }
        
        // Hide fields for approving
        $searchFieldsToHide = array(
            TaskSearch::FLD_STATUS,
            TaskSearch::FLD_PROJECT_STATUS,
            TaskSearch::FLD_PROJECT_STATUS_COM,
            TaskSearch::FLD_IS_BILLABLE,
        );

        $taskSearch = $this->getTaskSearchFromRequest();
        
        $_GET = $_REQUEST;
        
        $bFilterBlank = false;
        if (count($_GET) == 0) {
            $bFilterBlank = true;
        }
        
        // Search for new tasks and open projects
        $taskSearch->status = TaskStatus::TS_NEW;
        $taskSearch->projectStatusCom = ProjectStatus::PS_OPEN;
        // Get providers. Pass in a projects criteria with open projects
        $searchFlags = TaskSearchService::OP_APPROVE_TASKS;
        $searchFlags |= (Yii::$app->user->hasDirectorPrivileges()) ? TaskSearchService::SEARCH_AS_ADMIN : TaskSearchService::SEARCH_AS_MANAGER;
        
        $taskSearch->roleSearch = Yii::$app->user->role;
        if (Yii::$app->user->isProjectManager() && $bFilterBlank) {
            $taskSearch->owner = Yii::$app->user->id;
        } else if ($taskSearch->owner == "" && Yii::$app->user->isProjectManager()) {
            $taskSearch->creator = Yii::$app->user->id;
        } else if (Yii::$app->user->hasDirectorPrivileges() && $bFilterBlank) { 
            $taskSearch->owner = Yii::$app->user->id;
        }
        
        $providers = ServiceFactory::createTaskSearchService()
                ->getTaskSearchFormProvidersForProfile($taskSearch, Yii::$app->user, $searchFlags, true);
        $oImputetypeService = ServiceFactory::createImputetypeService();
        
        $this->render('approveTasks', CMap::mergeArray($providers, array(
                    'taskSearch' => $taskSearch,
                    'searchFieldsToHide' => $searchFieldsToHide,
                    'actionURL' => Yii::$app->urlManager->createUrl($this->route),
                    'onlyManagedByUser' => !Yii::$app->user->hasDirectorPrivileges(),
                    'projectImputetypes' => $oImputetypeService->findImputetypes(),
                    //'projectStatus' => ProjectStatus::PS_OPEN,
                    'projectStatusCom' => ProjectStatus::PS_OPEN                    
                )));
    }
    
    public function actionUpdateTasks() {
        
        if (isset($_POST['doApprove']) && ( $_POST['doApprove'] == 1 )
                && isset($_POST['toApprove'])
                && is_array($_POST['toApprove'])
                && is_array($_POST['pc'])
                && is_array($_POST['pr'])
                //&& is_array($_POST['comment'])
                && is_array($_POST['pj'])) {
            
            $upts = ServiceFactory::createUserProjectTaskService();
            foreach ($_POST['toApprove'] as $taskId) {
                if (empty($_POST['pr'][$taskId]) || empty($_POST['pj'][$taskId]))
                    throw new CHttpException(400, 'Invalid request', 400);
                $profile = $_POST['pr'][$taskId];
                $project = $_POST['pj'][$taskId];
                $company = $_POST['pc'][$taskId];
                $imputetype = $_POST['pti'][$taskId];
                $comment = "";
                if (isset($_POST['comment'][$taskId])) {
                    $comment = $_POST['comment'][$taskId];
                }
                
                $ticketId = "";
                if (isset($_POST['ticket'][$taskId])) {
                    $ticketId = $_POST['ticket'][$taskId];
                }
                
                $upts->updateTask($taskId, $profile, Yii::$app->user, $project, $comment, $ticketId, $imputetype);
            }
            // Reload w/o post data
            // NO!!! now search is included and must be kept
            // TODO: find a nice way to prevent repost
            //$this->refresh();
            
        }
        
        // Hide fields for approving
        $searchFieldsToHide = array(
            TaskSearch::FLD_PROJECT_STATUS,
            TaskSearch::FLD_PROJECT_STATUS_COM,
            TaskSearch::FLD_IS_BILLABLE
        );

        $taskSearch = $this->getTaskSearchFromRequest();
        
        $_GET = $_REQUEST;
        
        $bFilterBlank = false;
        if (count($_GET) == 0) {
            $bFilterBlank = true;
        }
        
        if (Yii::$app->user->hasDirectorPrivileges()) {
            $searchFlags = TaskSearchService::SEARCH_AS_ADMIN;
        } else if (Yii::$app->user->hasProjectManagerPrivileges()) {
            $searchFlags = TaskSearchService::SEARCH_AS_MANAGER;
        } else if (Yii::$app->user->hasCommercialPrivileges()) {
            $searchFlags = TaskSearchService::SEARCH_AS_COMMERCIAL;
        } else {
            $searchFlags = TaskSearchService::SEARCH_AS_WORKER;
        }
        
        $taskSearch->roleSearch = Yii::$app->user->role;
        if (Yii::$app->user->isProjectManager() && $bFilterBlank) {
            $taskSearch->owner = Yii::$app->user->id;
        } else if ($taskSearch->owner == "" && Yii::$app->user->isProjectManager()) {
            $taskSearch->creator = Yii::$app->user->id;
        } else if (Yii::$app->user->hasDirectorPrivileges() && $bFilterBlank) { 
            $taskSearch->owner = Yii::$app->user->id;
        }
        
        $providers = ServiceFactory::createTaskSearchService()
                ->getTaskSearchFormProvidersForProfile($taskSearch, Yii::$app->user, $searchFlags);
        
        $oImputetypeService = ServiceFactory::createImputetypeService();
        
        $this->render('updateTasks', CMap::mergeArray($providers, array(
                    'taskSearch' => $taskSearch,
                    'searchFieldsToHide' => $searchFieldsToHide,
                    'actionURL' => Yii::$app->urlManager->createUrl($this->route),
                    'onlyManagedByUser' => !Yii::$app->user->hasDirectorPrivileges(),
                    'projectImputetypes' => $oImputetypeService->findImputetypes()
                )));
    }

    private function getTaskSearchFromRequest() {
        $taskSearch = new TaskSearch();
        $taskSearch->scenario = 'search';
        //$taskSearch->unsetAttributes();

        if (isset($_REQUEST['TaskSearch'])) {
            if (isset($_REQUEST['pr'])) {
                unset($_REQUEST['pr']);
            }
            if (isset($_REQUEST['pj'])) {
                unset($_REQUEST['pj']);
            }
            if (isset($_REQUEST['pc'])) {
                unset($_REQUEST['pc']);
            }
            
            $taskSearch->attributes = $_REQUEST['TaskSearch'];
            // If no validated, then create a new search record
            if (!$taskSearch->validate()) {
                Yii::log("TaskSearch not validated", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY);
                Yii::log(print_r($_REQUEST['TaskSearch'], true), CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY);
                $taskSearch = new TaskSearch();
                $taskSearch->scenario = 'search';
                //$taskSearch->unsetAttributes();
            }
        }
        
        // Add sort if it's in the request
        if (isset($_REQUEST['sort'])) {
            $taskSearch->sort = $_REQUEST['sort'];
        }
        return $taskSearch;
    }

    /**
     *
     * @param int $searchAs @see TaskSearchService::SEARCH_AS_*
     * @param bool $showExportButton
     * @param bool $onlyManagedByUser show only projects managed by the user
     * @param bool $showWorker shows the worker that created the flag
     * @param int[] $searchFieldsToHide array of fields to hide in the search form
     */
    private function searchTasksOfProject($searchAs, $showExportButton, $onlyManagedByUser, 
            $showWorker = TRUE, 
            $searchFieldsToHide = array()) {
        $taskSearch = $this->getTaskSearchFromRequest();
        
        if (!isset($taskSearch->imputetype)) {
            $taskSearch->imputetype = Imputetype::getDefaultImputetypesFilter();
        }
        
        $bFilterBlank = false;
        if (count($_GET) == 0) {
            $bFilterBlank = true;
        }
        
        $taskSearch->roleSearch = Yii::$app->user->role;
        if (Yii::$app->user->isProjectManager() && $bFilterBlank) {
            $taskSearch->owner = Yii::$app->user->id;
        } else if ($taskSearch->owner == "" && Yii::$app->user->isProjectManager()) {
            $taskSearch->creator = Yii::$app->user->id;
        } else if (Yii::$app->user->hasDirectorPrivileges() && $bFilterBlank) { 
            $taskSearch->owner = Yii::$app->user->id;
        }
        
        // Get providers
        $providers = ServiceFactory::createTaskSearchService()->getTaskSearchFormProvidersForProfile($taskSearch, Yii::$app->user, $searchAs);
        // Return project total hours and cost
        $projectCost = ServiceFactory::createUserProjectTaskService()->getTasksCost($taskSearch);
        $oImputetypeService = ServiceFactory::createImputetypeService();
        
        $aProjectSummary = ServiceFactory::createUserProjectTaskService()->getCriteriaFromTaskSearchSummaryProject($taskSearch);
        
        $aProjectSummarized = array();
        foreach ($aProjectSummary as $oProjectSummary) {
            $aProjectSummarized[Project::findOne($oProjectSummary->project_id)->name] = $oProjectSummary;
        }
        
        $renderView = 'searchTasksOfProjects';
        $this->render($renderView, CMap::mergeArray($providers, array(
                    'taskSearch' => $taskSearch,
                    'searchFieldsToHide' => $searchFieldsToHide,
                    'projectImputetypes' => $oImputetypeService->findImputetypes(),
                    'projectHours' => $projectCost['hours'],
                    'projectPrice' => $projectCost['price'],
                    'showExportButton' => $showExportButton,
                    'actionURL' => Yii::$app->urlManager->createUrl($this->route),
                    'onlyManagedByUser' => $onlyManagedByUser,
                    'showWorker' => $showWorker,
                    'projectSummarized' => $aProjectSummarized
                )));
    }

    public function actionSearchTasksAdmin() {
        assert(Yii::$app->user->hasDirectorPrivileges());
        $this->searchTasksOfProject(TaskSearchService::SEARCH_AS_ADMIN, TRUE, FALSE, TRUE, array(), false);
    }

    public function actionSearchTasksManager() {
        $this->searchTasksOfProject(TaskSearchService::SEARCH_AS_MANAGER, TRUE, TRUE, TRUE, array(), false);
    }

    public function actionSearchTasksWorker() {
        $this->searchTasksOfProject(TaskSearchService::SEARCH_AS_WORKER, FALSE, FALSE, FALSE, array(
            TaskSearch::FLD_CREATOR,
            TaskSearch::FLD_OWNER,
            TaskSearch::FLD_STATUS,
            TaskSearch::FLD_PROFILE,
            TaskSearch::FLD_PROJECT_STATUS,
            TaskSearch::FLD_PROJECT_STATUS_COM,
            TaskSearch::FLD_PROJECT_CATEGORY,
            TaskSearch::FLD_IS_BILLABLE,
                ), false);
    }
    
    public function actionSearchTasksCommercial() {
        $this->searchTasksOfProject(TaskSearchService::SEARCH_AS_COMMERCIAL, true, false, FALSE, array(
            TaskSearch::FLD_CREATOR,
            TaskSearch::FLD_OWNER,
            TaskSearch::FLD_STATUS,
            TaskSearch::FLD_PROFILE,
            TaskSearch::FLD_PROJECT_STATUS,
            TaskSearch::FLD_PROJECT_STATUS_COM,
            TaskSearch::FLD_PROJECT_CATEGORY,
            TaskSearch::FLD_IS_BILLABLE,
                ), true);
    }

    public function actionSearchTasksCustomer() {
        $taskSearch = $this->getTaskSearchFromRequest();
        // Get providers
        $providers = ServiceFactory::createTaskSearchService()
                ->getTaskSearchFormProvidersForProfile($taskSearch, Yii::$app->user, TaskSearchService::SEARCH_AS_CUSTOMER, false);
        $searchFieldsToHide = array(
            TaskSearch::FLD_STATUS,
            TaskSearch::FLD_CREATOR,
            TaskSearch::FLD_OWNER,
            TaskSearch::FLD_CUSTOMER,
            TaskSearch::FLD_IS_EXTRA,
            TaskSearch::FLD_IS_BILLABLE,
        );
        $renderView = 'searchCustomer';
        $projectCost = ServiceFactory::createUserProjectTaskService()->getTasksCost($taskSearch);
        $this->render($renderView, CMap::mergeArray($providers, array(
                    'taskSearch' => $taskSearch,
                    'searchFieldsToHide' => $searchFieldsToHide,
                    'projectHours' => $projectCost['hours'],
                    'projectPrice' => $projectCost['price'],
                    'showExportButton' => Yii::$app->user->hasDirectorPrivileges(),
                    'actionURL' => Yii::$app->urlManager->createUrl($this->route)
                )));
    }
    
    public function actionReport() {
        
        $taskSarch = new TaskSearch('search');
        $taskSarch->unsetAttributes();  // clear any default values
        $aParam = array(
            "dateIni" => $_GET['open_time'], 
            "dateEnd" => $_GET['close_time'], 
            "projectId" => $_GET['project']
            );
        
        if ($_GET['h'] != md5($_GET['project']."TOKEN")) {
            echo "Wrong request... You are a bad boy :(";
            die();
        }
        
        //Array ( [ExpenseSearch] => Array ( [dateIni] => [dateEnd] => [companyId] => 4 [companyName] => abertis airports [projectId] => 2 [worker] => [costtype] => [paymentMethod] => ) [yt0] => Buscar [yt2] => Exportar a CSV ) 
        $taskSarch->attributes = $aParam;
        $content = ServiceFactory::createUserProjectTaskService()->getCSVContentFromTaskSearch($taskSarch, Yii::$app->user);
        if (!$content) {
            $content = "No results";
        } else {
            // Convert to UTF-16 for working around excel bug (not handling utf-8 properly)
            // and remove BOM, that Excel seems not to like it either
            //$content = substr( iconv( 'UTF-8', 'UTF-8', $content ), 2 );
            $content = utf8_decode($content);
        }
        $filename = 'tasks_' . date('Ymd') . '.csv';
        Yii::$app->getRequest()->sendFile($filename, $content, "text/csv", true);
    }

    /**
     * Sends a file in csv format with the results of the task search
     */
    public function actionExportToCSV() {
        
        $taskSearch = $this->getTaskSearchFromRequest();
        $content = ServiceFactory::createUserProjectTaskService()->getCSVContentFromTaskSearch($taskSearch, Yii::$app->user);
        if (!$content) {
            $content = "No results";
        } else {
            // Convert to UTF-16 for working around excel bug (not handling utf-8 properly)
            // and remove BOM, that Excel seems not to like it either
            //$content = substr( iconv( 'UTF-8', 'UTF-8', $content ), 2 );
            $content = utf8_decode($content);
        }
        $filename = 'tasks_' . date('Ymd') . '.csv';
        Yii::$app->getRequest()->sendFile($filename, $content, "text/csv", true);
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new UserProjectTask();
        $model->scenario = 'search';

        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['UserProjectTask']))
            $model->attributes = $_GET['UserProjectTask'];

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     * @return UserProjectTask
     */
    public function loadModel($id) {
        $model = UserProjectTask::findOne((int) $id);
        if ($model === null)
            throw new HttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-project-task-form') {
            echo ActiveForm::validate($model);
            Yii::$app->end();
        }
    }

    /**
     * Action for refusing a task.
     *
     * A customer may refuse a task, adding a new entry to the task
     * history table for that task
     *
     * @param int $taskId
     * @param string $motive
     */
    public function actionRefuseTask() {
        if (!Yii::$app->request->isPostRequest)
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        // Call func
        $taskId = (int) $_POST['taskId'];
        $motive = (string) $_POST['motive'];
        $upts = ServiceFactory::createUserProjectTaskService();
        if ($upts->refuseTask(Yii::$app->user, $taskId, $motive) === false)
            Yii::$app->end();
        echo "refused";
    }

    const OP_RESULT_OK = 'ok';
    const OP_RESULT_ERROR = 'error';
    const OP_RESULT_NOTHING = 'nop';
    const ERROR_CODE_TASK_NOT_EDITABLE = -125;

    
    public function actionAutomaticSaveTask() {

        if (!Yii::$app->request->isPostRequest) {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
        
        if (!isset($_POST['UserProjectTask'])) {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
        
        $result['code'] = self::OP_RESULT_NOTHING;
        try {        
            
            $userInfo = Yii::$app->user;
            $oCurrentUser = User::find()->where( array( "id" => $userInfo->id ) )->all();
            $aCurrentDay = mb_split("/", date("d/m/Y"));
            $nexttime = mktime(0,0,0,$aCurrentDay[1],$aCurrentDay[0] - $oCurrentUser->imputacionanterior, $aCurrentDay[2]);
            $maximumAllowedTimeToImpute = DateTime::createFromFormat( PHPUtils::DATE_TIME_FORMAT_ON_CONVERSION . ':s',  date("d/m/Y", $nexttime)." 0:0:0");
            
            $ts_ini = PHPUtils::convertStringToPHPDateTime($_POST['UserProjectTask']['frm_date_ini'] . ' ' . $_POST['UserProjectTask']['frm_hour_ini']);
            
            // Update task
            $id = (int) $_POST['UserProjectTask']['id'];
            $model = $this->loadModel($id);
            $this->ensureUserCanUpdateTask($model);
            
            //Check the operation to perform if it is allowed
            if ($model->frm_date_ini != $_POST['UserProjectTask']['frm_date_ini'] || 
                $model->frm_date_end != $_POST['UserProjectTask']['frm_date_end'] || 
                $model->frm_hour_ini != $_POST['UserProjectTask']['frm_hour_ini'] || 
                $model->frm_hour_end != $_POST['UserProjectTask']['frm_hour_end']) {               
                
                if ($ts_ini < $maximumAllowedTimeToImpute) {
                    $result['code'] = self::OP_RESULT_ERROR;
                    $resultMsg = 'No se puede modificar las horas imputadas más allá del periodo permitido. Por favor, consulte con el Administrador.';
                    $result['msg'] = CHtml::openTag('div', array('class' => 'errorSummary-short'))
                        . $resultMsg
                        . CHtml::closeTag('div');
                }                    
            }
                      
            if ($result['code'] == self::OP_RESULT_NOTHING) {
                $model->attributes = $_POST['UserProjectTask'];
                if (ServiceFactory::createUserProjectTaskService()->saveNewTask($model, Yii::$app->user)) {
                    // Return
                    $result['code'] = self::OP_RESULT_OK;
                    $result['msg'] = CHtml::openTag('div', array('class' => 'resultOk-short'))
                            . 'Tarea guardada con éxito'
                            . CHtml::closeTag('div');
                } else {
                    $result['code'] = self::OP_RESULT_ERROR;
                    $result['msg'] = CHtml::errorSummary($model, NULL, NULL, array('class' => 'errorSummary-short'));
                }
            }
        } catch (Exception $e) {
            Yii::log("Exception saving task: " . $e->getMessage(), CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
            $result['code'] = self::OP_RESULT_ERROR;
            if ($e->getCode() == self::ERROR_CODE_TASK_NOT_EDITABLE) {
                $resultMsg = 'No se puede editar la tarea. Refresque la página para actualizar estado.';
            } else {
                $resultMsg = 'Error interno del servidor';
            }
            $result['msg'] = CHtml::openTag('div', array('class' => 'errorSummary-short'))
                    . $resultMsg
                    . CHtml::closeTag('div');
        }
        echo json_encode($result);
        Yii::$app->end();
    }
    
    public function actionSaveTask() {       
        
        if (!Yii::$app->request->isPostRequest) {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
        
        if (!isset($_POST['UserProjectTask'])) {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
        $result['code'] = self::OP_RESULT_NOTHING;
        try {
            
            $userInfo = Yii::$app->user;
            $oCurrentUser = User::find()->where( array( "id" => $userInfo->id ) )->all();
            $aCurrentDay = mb_split("/", date("d/m/Y"));
            $nexttime = mktime(0,0,0,$aCurrentDay[1],$aCurrentDay[0] - $oCurrentUser->imputacionanterior, $aCurrentDay[2]);
            $maximumAllowedTimeToImpute = DateTime::createFromFormat( PHPUtils::DATE_TIME_FORMAT_ON_CONVERSION . ':s',  date("d/m/Y", $nexttime)." 0:0:0");
            $ts_ini = PHPUtils::convertStringToPHPDateTime($_POST['UserProjectTask']['frm_date_ini'] . ' ' . $_POST['UserProjectTask']['frm_hour_ini']);
            
            if (!is_numeric($_POST['UserProjectTask']['id'])) {
                // New task
                $model = new UserProjectTask;
               // $model->unsetAttributes();
                
                $oProject = Project::findOne($_POST['UserProjectTask']['project_id']);
                
                //The project is closed operative, no new tasks can be added.
                if ($oProject->status == ProjectStatus::PS_CLOSED) {
                    $result['code'] = self::OP_RESULT_ERROR;
                    $resultMsg = 'No se puede añadir una nueva tarea al proyecto '.$oProject->name.' si esta cerrado operativamente.';
                    $result['msg'] = CHtml::openTag('div', array('class' => 'errorSummary-short'))
                            . $resultMsg
                            . CHtml::closeTag('div');
                }

                // Check semantics: is end hour after ini hour?
                if ($ts_ini < $maximumAllowedTimeToImpute) {
                    $result['code'] = self::OP_RESULT_ERROR;
                    $resultMsg = 'Las horas que está imputando son demasiado antiguas. Hable con el administrador si quiere cambiar esta condición';
                    $result['msg'] = CHtml::openTag('div', array('class' => 'errorSummary-short'))
                            . $resultMsg
                            . CHtml::closeTag('div');
                }
                
            } else {
                // Update task
                
                $id = (int) $_POST['UserProjectTask']['id'];
                $model = $this->loadModel($id);
                $this->ensureUserCanUpdateTask($model);
                
                //Check the operation to perform if it is allowed
                if ($model->frm_date_ini != $_POST['UserProjectTask']['frm_date_ini'] || 
                    $model->frm_date_end != $_POST['UserProjectTask']['frm_date_end'] || 
                    $model->frm_hour_ini != $_POST['UserProjectTask']['frm_hour_ini'] || 
                    $model->frm_hour_end != $_POST['UserProjectTask']['frm_hour_end']) {
                    
                    if ($ts_ini < $maximumAllowedTimeToImpute) {
                        $result['code'] = self::OP_RESULT_ERROR;
                        $resultMsg = 'No se puede modificar las horas imputadas más allá del periodo permitido. Por favor, consulte con el Administrador.';
                        $result['msg'] = CHtml::openTag('div', array('class' => 'errorSummary-short'))
                                . $resultMsg
                                . CHtml::closeTag('div');
                    }                    
                }
            }
            
            if ($result['code'] == self::OP_RESULT_NOTHING) {
                $model->attributes = $_POST['UserProjectTask'];
                
                if (ServiceFactory::createUserProjectTaskService()->saveNewTask($model, Yii::$app->user)) {
                    // Return
                    $result['code'] = self::OP_RESULT_OK;
                    $result['msg'] = CHtml::openTag('div', array('class' => 'resultOk-short'))
                            . 'Tarea guardada con éxito'
                            . CHtml::closeTag('div');

                } else {
                    $result['code'] = self::OP_RESULT_ERROR;
                    $result['msg'] = CHtml::errorSummary($model, NULL, NULL, array('class' => 'errorSummary-short'));
                }
            }
        } catch (Exception $e) {
            Yii::log("Exception saving task: " . $e->getMessage(), CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
            $result['code'] = self::OP_RESULT_ERROR;
            if ($e->getCode() == self::ERROR_CODE_TASK_NOT_EDITABLE) {
                $resultMsg = 'No se puede editar la tarea. Refresque la página para actualizar estado.';
            } else {
                $resultMsg = 'Error interno del servidor';
            }
            $result['msg'] = CHtml::openTag('div', array('class' => 'errorSummary-short'))
                    . $resultMsg
                    . CHtml::closeTag('div');
        }
        echo json_encode($result);
        Yii::$app->end();
    }

    private function ensureUserCanUpdateTask(UserProjectTask $model) {
        $user = Yii::$app->user;
        $isWorker = !$user->hasDirectorPrivileges();
        // Check task access & task status for user
        if ($isWorker) {
            // Access
            if ($model->user_id != $user->id) {
                Yii::log("$user->id trying to update task $model->id which does not own", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
                throw new CHttpException(403, 'No tiene acceso');
            }
            // Status
            if ($model->status != TaskStatus::TS_NEW) {
                Yii::log("$user->id trying to update a not new task", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
                throw new Exception('No se puede editar la tarea', self::ERROR_CODE_TASK_NOT_EDITABLE);
            }
        }
    }

}
