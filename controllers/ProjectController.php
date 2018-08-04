<?php

class ProjectController extends CronosController {

    const MY_LOG_CATEGORY = 'controllers.ProjectController';

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/top_menu';

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionPrint($id) {
        ServiceFactory::createProjectService()->sendProjectStatusReport($id, true);
        $this->render('print', array());
    }


    private function createUpdateRefactor($model, $renderView) {

        $sPreviousOperationalStatus = $model->status;
        $sPreviousCommercialStatus = $model->statuscommercial;

        if (isset($_POST['Project']) && (!isset($_POST['new_select']) )) {
            $ps = ServiceFactory::createProjectService();

            if ($model->isNewRecord) {
                if ($ps->createProject($model, $_POST['Project'])) {

                    //Cambiamos el proyecto a cerrado a nivel operacional.
                    if ($_POST['Project']['company_id'] != Company::OPEN3S_ID &&
                            (
                            ($sPreviousOperationalStatus != "" &&
                            $sPreviousOperationalStatus != $_POST['Project']['status'] &&
                            $_POST['Project']['status'] == ProjectStatus::PS_CLOSED)
                            ||
                            ($sPreviousOperationalStatus != "" &&
                            $sPreviousOperationalStatus != $_POST['Project']['status'] &&
                            $_POST['Project']['status'] == ProjectStatus::PS_OPEN)
                            )) {

                        $sAlert = "";
                        $aCommercials = array();
                        if ($_POST['Project']['status'] == ProjectStatus::PS_CLOSED) {
                            $sAlert = Alerts::PROJECT_CLOSED_OPERATIONAL;
                            $aCommercials = ServiceFactory::createProjectService()->getMailCommercialProject($model->id);
                        } else if ($_POST['Project']['status'] == ProjectStatus::PS_OPEN) {
                            $sAlert = Alerts::PROJECT_OPENED_OPERATIONAL;
                        }
                        
                        $aWorkers = ServiceFactory::createProjectService()->getMailWorkersProject($model->id);
                        $as = ServiceFactory::createAlertService();
                        $as->notify($sAlert, array(
                            Alerts::MESSAGE_REPLACEMENTS => array(
                                'project.name' => $model->name,
                                'customer.name' => $model->company->name
                            ),
                            EmailNotifier::NOTIFICATION_RECEIVERS => array_merge($aWorkers, $aCommercials),
                        ));
                        //Cambiamos el proyecto a cerrado a nivel comercial.
                    }

                    if ($_POST['Project']['company_id'] != Company::OPEN3S_ID && 
                            (
                            ($sPreviousCommercialStatus != "" && 
                            $sPreviousCommercialStatus != $_POST['Project']['statuscommercial'] &&
                            $_POST['Project']['statuscommercial'] == ProjectStatus::PS_CLOSED) ||
                            (
                            $sPreviousCommercialStatus != "" &&
                            $sPreviousCommercialStatus != $_POST['Project']['statuscommercial'] &&
                            $_POST['Project']['statuscommercial'] == ProjectStatus::PS_OPEN))) {

                        $sAlert = "";
                        $managerMails = array();
                        if ($_POST['Project']['statuscommercial'] == ProjectStatus::PS_CLOSED) {
                            $sAlert = Alerts::PROJECT_CLOSED_COMMERCIAL;
                        } else if ($_POST['Project']['statuscommercial'] == ProjectStatus::PS_OPEN) {
                            $sAlert = Alerts::PROJECT_OPENED_COMMERCIAL;
                            $managerMails = ServiceFactory::createProjectService()->getMailPmsProject($model->id);
                        }
                        
                        $workerMails = ServiceFactory::createProjectService()->getMailAdministrative();
                        $as = ServiceFactory::createAlertService();
                        $as->notify($sAlert, array(
                            Alerts::MESSAGE_REPLACEMENTS => array(
                                'project.name' => $model->name,
                                'customer.name' => $model->company->name
                            ),
                            EmailNotifier::NOTIFICATION_RECEIVERS => array_merge($workerMails, $managerMails),
                        ));
                    }

                    // Set flash if operation was successfull
                    Yii::$app->user->setFlash(Constants::FLASH_OK_MESSAGE, "Proyecto $model->name guardado con éxito");
                    $this->redirect(array('update', 'id' => $model->id));
                }
            } else {
                if ($ps->updateProject($model, $_POST['Project'])) {

                    //Cambiamos el proyecto a cerrado a nivel operacional.
                    if ($_POST['Project']['company_id'] != Company::OPEN3S_ID &&
                            (
                            ($sPreviousOperationalStatus != "" &&
                            $sPreviousOperationalStatus != $_POST['Project']['status'] &&
                            $_POST['Project']['status'] == ProjectStatus::PS_CLOSED)
                            ||
                            ($sPreviousOperationalStatus != "" &&
                            $sPreviousOperationalStatus != $_POST['Project']['status'] &&
                            $_POST['Project']['status'] == ProjectStatus::PS_OPEN)
                            )) {

                        $sAlert = "";
                        $aCommercials = array();
                        if ($_POST['Project']['status'] == ProjectStatus::PS_CLOSED) {
                            $sAlert = Alerts::PROJECT_CLOSED_OPERATIONAL;
                            $aCommercials = ServiceFactory::createProjectService()->getMailCommercialProject($model->id);
                        } else if ($_POST['Project']['status'] == ProjectStatus::PS_OPEN) {
                            $sAlert = Alerts::PROJECT_OPENED_OPERATIONAL;
                        }
                        
                        $aWorkers = ServiceFactory::createProjectService()->getMailWorkersProject($model->id);
                        $as = ServiceFactory::createAlertService();
                        $as->notify($sAlert, array(
                            Alerts::MESSAGE_REPLACEMENTS => array(
                                'project.name' => $model->name,
                                'customer.name' => $model->company->name
                            ),
                            EmailNotifier::NOTIFICATION_RECEIVERS => array_merge($aWorkers, $aCommercials),
                        ));
                        //Cambiamos el proyecto a cerrado a nivel comercial.
                    }

                    if ($_POST['Project']['company_id'] != Company::OPEN3S_ID && 
                            (
                            ($sPreviousCommercialStatus != "" &&
                            $sPreviousCommercialStatus != $_POST['Project']['statuscommercial'] &&
                            $_POST['Project']['statuscommercial'] == ProjectStatus::PS_CLOSED) ||
                            ($sPreviousCommercialStatus != "" &&
                            $sPreviousCommercialStatus != $_POST['Project']['statuscommercial'] &&
                            $_POST['Project']['statuscommercial'] == ProjectStatus::PS_OPEN)
                            )) {
                        
                        $sAlert = "";
                        $managerMails = array();
                        if ($_POST['Project']['statuscommercial'] == ProjectStatus::PS_CLOSED) {
                            $sAlert = Alerts::PROJECT_CLOSED_COMMERCIAL;
                        } else if ($_POST['Project']['statuscommercial'] == ProjectStatus::PS_OPEN) {
                            $sAlert = Alerts::PROJECT_OPENED_COMMERCIAL;
                            $managerMails = ServiceFactory::createProjectService()->getMailPmsProject($model->id);
                        }
                        
                        $workerMails = ServiceFactory::createProjectService()->getMailAdministrative();
                        $as = ServiceFactory::createAlertService();
                        $as->notify($sAlert, array(
                            Alerts::MESSAGE_REPLACEMENTS => array(
                                'project.name' => $model->name,
                                'customer.name' => $model->company->name
                            ),
                            EmailNotifier::NOTIFICATION_RECEIVERS => array_merge($workerMails, $managerMails),
                        ));
                    }

                    Yii::$app->user->setFlash(Constants::FLASH_OK_MESSAGE, "Proyecto $model->name guardado con éxito");
                    $this->refresh();
                }
            }
        }
        if (isset($_POST['new_select'])) {
            $model->attributes = $_POST['Project'];
        }
        
        $criteria = new CDbCriteria(array(
                    'order' => 't.name asc',
                ));
        $companies = Company::model()->findAll($criteria);
        if (!empty($model->company_id)) {
            // UPDATING PROJECT
            $companyToLoad = $model->company_id;
        } else {
            // NEW PROJECT
            // There must be 1 company at least but...
            if (count($companies) > 0)
                $companyToLoad = $companies[0]->id;
        }
        // Check if project was loaded with profiles. If not, assign defaults
        if (count($model->workerProfiles) == 0) {
            $model->workerProfiles = ServiceFactory::createWorkerProfilesService()->getDefaultArrayOfPricePerProjectAndProfileModels();
        }
        
        // Find managers and customers
        $us = ServiceFactory::createUserService();
        $oImputetypeService = ServiceFactory::createImputetypeService();
        
        $this->render($renderView, array(
            'model' => $model,
            'companies' => $companies,
            'projectManagers' => $us->findProjectManagers(true, $model->id),
            'projectWorkers' => $us->findProjectWorkers(true, $model->id),
            'projectCommercials' => $us->findCommercials(true, $model->id),
            'projectImputetypes' => $oImputetypeService->findImputetypes(),
            'projectTargets' => Role::model()->findAllByAttributes(array()),
            // If no companies, empty array
            'projectCustomers' => ( count($companies) == 0 ) ? array() : $us->findProjectCustomersByCompany($companyToLoad),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new Project();
        $model->imputetypes = array("0" => Imputetype::OPERACIONES);
        $this->createUpdateRefactor($model, 'create');
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id, true);
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
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax'])) {
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin') );
            }
        } else {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {

        $dataProvider = new CActiveDataProvider('Project', array(
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
        $model = new Project('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['Project'])) {
            $model->attributes = $_GET['Project'];
        }

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id, $loadRelations = false) {
        if ($loadRelations) {
            $model = Project::model()->with('company')->findByPk((int) $id);
        } else {
            $model = Project::model()->findByPk((int) $id);
        }
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'project-form') {
            echo CActiveForm::validate($model);
            Yii::$app->end();
        }
    }

    private function getProjectModelForSearchFromRequest() {
        $model = new Project('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['Project'])) {
            $model->attributes = $_GET['Project'];
        }
        return $model;
    }

    public function actionProjectOverview() {
        $model = $this->getProjectModelForSearchFromRequest();
        
        if (!isset($model->imputetype)) {
            $model->imputetype = Imputetype::getDefaultImputetypesFilter();
        }
        
        $projectsCriteria = new CDbCriteria();
        $projectsCriteria->select = 't.id, t.name';
        $projectsCriteria->order = 't.id desc';
        if (!empty($model->company_id)) {
            $projectsProvider = ServiceFactory::createProjectService()
                    ->findProjectsFromCustomerByCustomerId($model->company_id, Yii::$app->user, $projectsCriteria, !Yii::$app->user->hasDirectorPrivileges(), $model->open_time, $model->close_time);
        } else {
            $projectsProvider = array();
        }
        
        $oImputetypeService = ServiceFactory::createImputetypeService();
        $this->render('projectOverview', array(
            'model' => $model,
            'projectsProvider' => $projectsProvider,
            'projectImputetypes' => $oImputetypeService->findImputetypes(),
        ));
    }

    public function actionExportToCSV() {
        $model = $this->getProjectModelForSearchFromRequest();
        if (!isset($model->imputetype)) {
            $model->imputetype = Imputetype::getDefaultImputetypesFilter();
        }
        $content = ServiceFactory::createProjectService()->getCSVContentFromSearch($model);
        if (!$content) {
            $content = "No results";
        } else {
            // Convert to UTF-16 for working around excel bug (not handling utf-8 properly)
            // and remove BOM, that Excel seems not to like it either
            //$content = substr( iconv( 'UTF-8', 'UTF-8', $content ), 2 );
            $content = utf8_decode($content);
        }
        $filename = 'projects_' . date('Ymd') . '.csv';
        Yii::$app->getRequest()->sendFile($filename, $content, "text/csv", true);
    }

}
