<?php
namespace app\services\models;

use Yii;
use app\services\CronosService;
use app\models\enums\ProjectStatus;
use app\components\utils\PHPUtils;

/**
 * Description of ProjectService
 *
 * @author twocandles
 */
class ProjectService implements CronosService {

    const MY_LOG_CATEGORY = 'services.ProjectService';

    private function manageStatusTimestamps( $model, array $newData) {
        // Manage open/close status timestamps
        if (( $model->status == ProjectStatus::PS_OPEN )
                && ( $newData['status'] == ProjectStatus::PS_CLOSED )) {
            // Closing project, no matter what date is now existing
            $model->close_time = PHPUtils::getNowAsString();
        } else if (( $model->status == ProjectStatus::PS_CLOSED )
                && ( $newData['status'] == ProjectStatus::PS_OPEN )) {
            // Reopening
            $model->close_time = null;
        } else {
            if (isset($newData['close_time']) && $newData['close_time'] != "") {
                $model->close_time = $newData['close_time'];
            }
        }
    }

    private function buildArrayOfPricePerProjectModels(array $arr) {
        $result = array();
        foreach ($arr as $profileId => $priceArr) {
            $model = new PricePerProjectAndProfile;
            $result[] = $model;
            // Set a fake project id
            $model->project_id = -1;
            if (!WorkerProfiles::isValidValue($profileId)) {
                throw new CHttpException(400, 'Invalid request', 400);
            }
            $model->worker_profile_id = $profileId;
            if ((!is_numeric($priceArr['price']) )
                    || ( ((float) $priceArr['price']) < 0 )) {
                $model->price = 0.0;
            } else {
                $model->price = $priceArr['price'];
            }
        }
        return $result;
    }

    /**
     * Saves a project
     * @paramProject $model
     * @param array $newData
     * @return boolean
     */
    private function saveProject($model, $newData) {
        
        $this->manageStatusTimestamps($model, $newData);
        unset($newData['close_time']);
        
        // Massively set attributes
        $model->attributes = $newData;
        //$model->close_time = null;
        //die();
        
        $bSaveWorkerProfiles = true;
        if (isset($newData['workerProfiles'])) {
            $bSaveWorkerProfiles = false;
            $model->workerProfiles = $this->buildArrayOfPricePerProjectModels($newData['workerProfiles']);
        }
        // Ready to save
        $transaction = $model->dbConnection->beginTransaction();
        $allSavesOK = false;
        try {
            // Save relationships
            if ($model->save()) {
                
                // Customers & managers & workers
                $customers = PHPUtils::getArrayFromSelect(@$_POST['Project']['customers']);
                $managers = PHPUtils::getArrayFromSelect(@$_POST['Project']['managers']);
                $workers = PHPUtils::getArrayFromSelect(@$_POST['Project']['workers']);
                $commercials = PHPUtils::getArrayFromSelect(@$_POST['Project']['commercials']);
                $imputetypes = PHPUtils::getArrayFromSelect(@$_POST['Project']['imputetypes']);
                $reportingTarget = PHPUtils::getArrayFromSelect(@$_POST['Project']['reportingtarget']);
                
                $bSavedWorkedProfiles = true;
                if (!$bSaveWorkerProfiles) {
                    $bSavedWorkedProfiles = ServiceFactory::createWorkerProfilesService()->saveProfilePricesForProject($model->workerProfiles, $model->id);
                }
                
                $aCostManagers = array();
                foreach($managers as $worker) {
                    $oCurrentUser = User::find()->where( array( "id" => $worker ) )->all();
                    $aCostManagers[$worker] = $oCurrentUser->hourcost;
                }
                
                $aCostWorkers = array();
                foreach($workers as $worker) {
                    $oCurrentUser = User::find()->where( array( "id" => $worker ) )->all();
                    $aCostWorkers[$worker] = $oCurrentUser->hourcost;
                }
                
                $aCostCommercials = array();
                foreach($commercials as $worker) {
                    $oCurrentUser = User::find()->where( array( "id" => $worker ) )->all();
                    $aCostCommercials[$worker] = $oCurrentUser->hourcost;
                }

                if (( ProjectCustomer::model()->saveCustomers($model->id, $customers) )
                        && ( ProjectManager::model()->saveManagers($model->id, $managers, $aCostManagers) )
                        && ( ProjectWorker::model()->saveWorkers($model->id, $workers, $aCostWorkers) )
                        && ( ProjectImputetype::model()->saveImputetypes($model->id, $imputetypes) )
                        && ( ProjectReporting::model()->saveProjectReporting($model->id, $reportingTarget) )
                        && ( ProjectCommercial::model()->saveCommercial($model->id, $commercials, $aCostCommercials) )
                        && ( $bSavedWorkedProfiles )) {
                    $allSavesOK = true;
                    
                }
            }
            if ($allSavesOK) {
                Yii::log('Project ' . $model->name . ' saved OK', CLogger::LEVEL_INFO, self::MY_LOG_CATEGORY);
                $transaction->commit();
            } else {
                Yii::log('Error saving Project: ' . $model->name, CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
                $transaction->rollback();
            }
        } catch (Exception $e) {
            Yii::log('Error saving Project ' . $e, CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
            $transaction->rollback();
            throw $e;
        }

        return $allSavesOK;
    }

    /**
     * Creates a new project
     *
     * @param Project $model
     * @param array $newData
     * @return boolean
     */
    public function createProject( $model, array $newData) {
        //$model->unsetAttributes();
        // Default attributes
        // TODO : implement variable time behaviour
        $model->fixed_time = 0;
        // Set open status if new project
        $model->status = ProjectStatus::PS_OPEN;
        $model->open_time = PHPUtils::getNowAsString();
        return $this->saveProject($model, $newData);
    }

    /**
     * Updates an existing project
     * @param Project $model
     * @param array $newData
     * @return boolean
     */
    public function updateProject( $model, array $newData) {
        return $this->saveProject($model, $newData);
    }

    /**
     * @param int $userId
     * @param int $projectId
     * @return boolean
     */
    public function isManagerOfProject($userId, $projectId) {
        return ( ProjectManager::findOne(array(
                    'user_id' => $userId,
                    'project_id' => $projectId,
                )) != null );
    }
    
    public function isWorkerOfProject($userId, $projectId) {
        return ( ProjectWorker::findOne(array(
                    'user_id' => $userId,
                    'project_id' => $projectId,
                )) != null );
    }

    /**
     * Returns if the customer has access to query the project
     * @param int $customerId
     * @param int $projectId
     * @return boolean
     */
    public function isCustomerOfProject($customerId, $projectId) {
        return ( ProjectCustomer::findOne(array(
                    'user_id' => $customerId,
                    'project_id' => $projectId,
                )) != null );
    }
    
    /**
     * Check if the commercial is on the project
     * @param type $commercial
     * @param type $projectId
     * @return type
     */
    public function isCommercialOfProject($commercial, $projectId) {
        return ( ProjectCommercial::findOne(array(
                    'user_id' => $commercial,
                    'project_id' => $projectId,
                )) != null );
    }

    /**
     * Returns a list of projects which the customer has been given access to.
     * PROJECT STATUS MAY BE OPEN OR CLOSED, NO MATTER WHAT
     * @param int $cust_id user id of the customer
     * @return array List of Projects (models) which customer has access to
     */
    public function findProjectsCustomerHasAccessTo($cust_id) {
        $criteria = new CDbCriteria(array(
                    'join' => 'INNER JOIN ' . ProjectCustomer::model()->tableName() . ' customers ON customers.project_id = t.id',
                    'condition' => 'customers.user_id=:cust_id',
                    'params' => array('cust_id' => $cust_id),
                    'order' => 't.name asc',
                ));
        return Project::findAll($criteria);
    }
    
    /**
     * Get the ProjectCommercials
     * @param type $cust_id
     * @return type
     */
    public function findProjectsCommercialHasAccessTo($cust_id) {
        $criteria = new CDbCriteria(array(
                    'join' => 'INNER JOIN ' . ProjectCommercial::model()->tableName() . ' commercial ON commercial.project_id = t.id',
                    'condition' => 'commercial.user_id=:cust_id',
                    'params' => array('cust_id' => $cust_id),
                    'order' => 't.name asc',
                ));
        return Project::findAll($criteria);
    }

    /**
     * Returns a list of projects which belong to a customer. The customer may have
     * access to them or not!!
     * PROJECT STATUS IS OPEN
     * @param string $customerName company name of the customer
     * @return array List of Projects (models) belonging to a customer in open status
     */
    public function findOpenProjectsFromCustomerByCustomerName($customerName, $iCurrentUser = "") {
        
        $sQuery = "";
        if ($iCurrentUser != "") {
            $sQuery = " AND (
                                exists (select pw.* from " . ProjectWorker::model()->tableName() . " pw where t.id = pw.project_id and pw.user_id = ".$iCurrentUser.") OR
                                exists (select pw.* from " . ProjectManager::model()->tableName() . " pw where t.id = pw.project_id and pw.user_id = ".$iCurrentUser.") OR
                                exists (select pw.* from " . ProjectCommercial::model()->tableName() . " pw where t.id = pw.project_id and pw.user_id = ".$iCurrentUser.")
                            )";
        }
        
        
        $criteria = new CDbCriteria(array(
                    'select' => 't.id, t.name',
                    'join' => 'INNER JOIN ' . Company::model()->tableName() . ' customers ON customers.id = t.company_id',
                    'condition' => 'customers.name=:customerName '.$sQuery,
                    'scopes' => 'open',
                    'params' => array('customerName' => $customerName),
                    'order' => 't.name',
                ));
        return Project::findAll($criteria);
    }

    /**
     * Returns a list of projects which belong to a customer. The customer may have
     * access to them or not!!
     * @param string $customerId company id of the customer
     * @param CronosUser $user user launching the query
     * @param CDbCriteria $projectCriteria already existing criteria
     * @param $onlyManagedByUser add a condition to return projects managed by user for the customer
     * @return array List of Projects (models) belonging to a customer in open status
     */
    public function findProjectsFromCustomerByCustomerId($customerId, CronosUser $user, 
            $projectCriteria, $onlyManagedByUser, 
            $sStartFilter = "", $sEndFilter = "", 
            $onlyUserEnvolved = false) {
        // If no customer => no projects!!
        if (empty($customerId) &&
                empty($sStartFilter) &&
                empty($sEndFilter)) {
            return array();
        }
        if ($projectCriteria === null) {
            $criteria = new yii\db\Query();
        } else {
            $criteria = $projectCriteria;
        }

        if (!empty($customerId)) {
            $criteria->where('t.company_id=:companyId');
            $criteria->params['companyId'] = $customerId;
        }

        if (!empty($sStartFilter) && !empty($sEndFilter)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartFilter, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndFilter, "23:59");

            $criteria->where("
                            (t.open_time <= :start_open AND t.close_time IS NULL) OR                            
                            (t.open_time <= :end_open AND t.close_time IS NULL) OR   
                            (t.open_time <= :start_open AND t.close_time >= :start_open) OR 
                            (:start_open <= t.open_time AND t.close_time <= :end_open) OR 
                            (:start_open <= t.open_time AND t.open_time <= :end_open AND t.close_time >= :end_open) OR 
                            (t.open_time <= :start_open AND t.close_time >= :end_open)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else if (!empty($sStartFilter)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartFilter, "00:00");
            $criteria->where("
                            (:start_open >= t.open_time AND :start_open <= t.close_time) OR 
                            (:start_open >= t.open_time AND t.close_time IS NULL) OR                            
                            (:start_open <= t.open_time)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
        } else if (!empty($sEndFilter)) {
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndFilter, "23:59");
            $criteria->where("
                            (t.open_time <= :end_open AND :end_open >= t.close_time) OR 
                            (t.open_time <= :end_open AND :end_open <= t.close_time) OR                            
                            (t.open_time <= :end_open AND t.close_time IS NULL)");
            
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        }
        
        // Project manager
        if ($onlyManagedByUser) {
            $criteria->join = 'INNER JOIN ' . ProjectManager::model()->tableName() . ' managers ON managers.project_id = t.id';
            $criteria->where('managers.user_id=:user_id');
            $criteria->params['user_id'] = $user->id;
        }
        
        //Only when the user is involved
        if ($onlyUserEnvolved && !Yii::$app->user->isAdmin()) {
            $criteria->where('
                (exists (select * from ' . ProjectManager::model()->tableName() . ' m where m.project_id = t.id and m.user_id= '. $user->id.') OR 
                exists (select * from ' . ProjectWorker::model()->tableName() . ' m where m.project_id = t.id and m.user_id= '. $user->id.') OR
                exists (select * from ' . ProjectCommercial::model()->tableName() . ' m where m.project_id = t.id and m.user_id= '. $user->id.'))');
        }
        
        $criteria->order = 't.name asc';
        return Project::findAll($criteria);
    }

    /**
     * Adds the conditions for filtering projects a project manager has access to
     * @param CDbCriteria $criteria
     * @param type $userId
     */
    public function addCriteriaForProjectManagers( $criteria, $userId) {
        $criteria->join = 'INNER JOIN ' . ProjectManager::model()->tableName() . ' managers ON managers.project_id = t.id';
        $criteria->addColumnCondition(array('managers.user_id' => $userId));
    }

    /**
     * @param int $userId
     * @return array List of Projects (models) which manager has access to AND ARE OPEN
     */
    public function findProjectsByProjectManager($userId, $onlyOpen = TRUE) {
        $criteria = new yii\db\Query();
        $criteria->order = 't.name asc';
        $this->addCriteriaForProjectManagers($criteria, $userId);
        if ($onlyOpen) {
            $criteria->scopes = 'open';
        }
        return Project::findAll($criteria);
    }
    
    public function addCriteriaForWorker( $criteria, $userId) {
        $criteria->join = 'INNER JOIN ' . ProjectWorker::model()->tableName() . ' workers ON workers.project_id = t.id';
        $criteria->addColumnCondition(array('workers.user_id' => $userId));
    }
    
    public function findProjectsByWorker($userId, $onlyOpen = TRUE) {
        $criteria = new yii\db\Query();
        $criteria->order = 't.name asc';
        $this->addCriteriaForWorker($criteria, $userId);
        if ($onlyOpen) {
            $criteria->scopes = 'open';
        }
        return Project::findAll($criteria);
    }
    
    
    public function addCriteriaForCommercial( $criteria, $userId) {
        $criteria->join = 'INNER JOIN ' . ProjectCommercial::model()->tableName() . ' commercials ON commercials.project_id = t.id';
        $criteria->addColumnCondition(array('commercials.user_id' => $userId));
    }

    /**
     * @param int $userId
     * @return array List of Projects (models) which manager has access to AND ARE OPEN
     */
    public function findProjectsByCommercial($userId, $onlyOpen = TRUE) {
        $criteria = new yii\db\Query();
        $criteria->order = 't.name asc';
        $this->addCriteriaForCommercial($criteria, $userId);
        if ($onlyOpen) {
            $criteria->scopes = 'open';
        }
        return Project::findAll($criteria);
    }
    
    /**
     * Envia reporte
     * @param int $project
     */
    public function sendProjectStatusReport($project, $bForce = false) {
        $oProject = Project::findOne($project);
        
        $aRoleToBeIncluded = array();
        foreach($oProject->reportingtarget as $oRole) {
            array_push($aRoleToBeIncluded, $oRole->name);
        }
        
        if ($oProject->reporting != ReportingFreq::FREQ_NONE || $bForce) {
            Yii::log( 'The frequence for the report is defined for '.$oProject->reporting, CLogger::LEVEL_ERROR);
            //Calculamos la fecha
            $sCurrentDate = date("d");
            
            $sFechaIni = date('d/m/Y',(strtotime ( '-' .($sCurrentDate+1) .' day' , strtotime (date("Y-m-d")) ) ));
            $sFechaFi = date('d/m/Y',(strtotime ( '-' .($sCurrentDate) .' day' , strtotime (date("Y-m-d")) ) ));
            if ($oProject->reporting == ReportingFreq::FREQ_ANUAL) {
                $sFechaIni = date('d/m/Y',(strtotime ( '-1 year -' .($sCurrentDate) .' day' , strtotime (date("Y-m-d")) ) ));
            } else if ($oProject->reporting == ReportingFreq::FREQ_MENSUAL) {
                $sFechaIni = date('d/m/Y',(strtotime ( '-1 month -' .($sCurrentDate) .' day' , strtotime (date("Y-m-d")) ) ));
            } else if ($oProject->reporting == ReportingFreq::FREQ_SEMESTRAL) {
                $sFechaIni = date('d/m/Y',(strtotime ( '-6 month -' .($sCurrentDate) .' day' , strtotime (date("Y-m-d")) ) ));
            } else if ($oProject->reporting == ReportingFreq::FREQ_TRIMESTRAL) {
                $sFechaIni = date('d/m/Y',(strtotime ( '-3 month -' .($sCurrentDate) .' day' , strtotime (date("Y-m-d")) ) ));
            }
            
            $aMailTarget = array();
            foreach($aRoleToBeIncluded as $sRole) {
                if ($sRole == Roles::UT_COMERCIAL) {
                    foreach($oProject->commercials as $oUser) {
                        if (!in_array($oUser->email, $aMailTarget)) {
                            array_push($aMailTarget, $oUser->email);
                        }
                    }
                } else if ($sRole == Roles::UT_PROJECT_MANAGER) {
                    foreach($oProject->managers as $oUser) {
                        if (!in_array($oUser->email, $aMailTarget)) {
                            array_push($aMailTarget, $oUser->email);
                        }
                    }
                }
            }
            
            if (in_array(Roles::UT_DIRECTOR_OP, $aRoleToBeIncluded)) {
                $us = ServiceFactory::createUserService();
                $aDirectorOp = $us->findUsersWithRole(Roles::UT_DIRECTOR_OP, true);
                foreach($aDirectorOp as $oUser) {
                    if (!in_array($oUser->email, $aMailTarget)) {
                        array_push($aMailTarget, $oUser->email);
                    }
                }
            }
            
            $aCustomTarget = mb_split(",", $oProject->reportingtargetcustom);
            foreach($aCustomTarget as $sCustomTarget) {
                if (!in_array($sCustomTarget, $aMailTarget)) {
                    array_push($aMailTarget, $sCustomTarget);
                }
            }
            
            $sSecure = md5($project."TOKEN");
            $as = ServiceFactory::createAlertService();
            $as->notify(Alerts::PROJECT_REPORTING, array(
                Alerts::MESSAGE_REPLACEMENTS => array(
                    'project.name' => $oProject->name,
                    'client.name' => $oProject->company_name,
                    'periodo_informe' => ReportingFreq::getLabel($oProject->reporting),
                    'range_ini' => $sFechaIni,
                    "range_fi" => $sFechaFi,
                    "project" => $project,
                    "server" => $_SERVER["SERVER_NAME"],
                    "secure" => $sSecure
                ),
                EmailNotifier::NOTIFICATION_RECEIVERS => $aMailTarget,
            ));
            Yii::log( 'E-mail enviado para el proyecto '.$oProject->name." ".implode(",", $aMailTarget), CLogger::LEVEL_ERROR);
        }
    }

    /**
     * Returns an array of id_project => project with the projects
     * belonging to the same customer of $projectId and managed by
     * $sessionUser
     * @param int $projectId project id whose customer serves as the key to search for
     * the list of projects
     * @param CronosUser $sessionUser manager that restricts the projects returned
     * @return array projects of customer and manager
     */
    public function findProjectsForCustomerAndManagerForDropdown($projectId, CronosUser $sessionUser, $bAllStatus = false) {
        assert(is_numeric($projectId));
        // Load project to get it's company
        $project = Project::findOne($projectId);
        if (empty($project)) {
            Yii::log("Project $projectId not found", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
            return array();
        }
        // Build search criteria depending on the user
        $criteria = new CDbCriteria;
        $criteria->where('company_id = :companyId');
        $criteria->params['companyId'] = $project->company_id;
        if (!$bAllStatus) {
            $criteria->where('statuscommercial = :status');
            $criteria->params['status'] = ProjectStatus::PS_OPEN;
        }
        
        
//        if ($sessionUser->hasDirectorPrivileges()) {
//            
//        } else if ($sessionUser->hasProjectManagerPrivileges()) {
//            $criteria->join = 'INNER JOIN ' . ProjectManager::model()->tableName()
//                    . ' managers ON managers.project_id = t.id';
//            $criteria->where('managers.user_id = :user_id');
//            $criteria->params['user_id'] = $sessionUser->id;
//        } else if ($sessionUser->hasCommercialPrivileges()) {
//            $criteria->join = 'INNER JOIN ' . ProjectCommercial::model()->tableName()
//                    . ' managers ON managers.project_id = t.id';
//            $criteria->where('managers.user_id = :user_id');
//            $criteria->params['user_id'] = $sessionUser->id;
//        } else {
//            $criteria->join = 'INNER JOIN ' . ProjectWorker::model()->tableName()
//                    . ' managers ON managers.project_id = t.id';
//            $criteria->where('managers.user_id = :user_id');
//            $criteria->params['user_id'] = $sessionUser->id;
//        }
        
        $criteria->order = 't.name asc';
        $models = Project::findAll($criteria);
        $result = array();
        foreach ($models as $project)
            $result[$project->id] = $project->name;
        return $result;
    }
    
    /**
     * Retrieves the project managers for a project (including admins)
     * @param int $project_id
     * @return array List of users which are managers for a project
     */
    public function findProjectManagersAndAdminByProject($project_id) {
        $criteria = new CDbCriteria(array(
                    'join' => 'LEFT JOIN ' . ProjectManager::model()->tableName() . ' managers ON managers.user_id = t.id'
                    . ' INNER JOIN ' . AuthAssignment::model()->tableName() . ' roles ON roles.userid = t.id',
                    'condition' => 'managers.project_id=:project_id OR roles.itemname = :role',
                    'params' => array(
                        'project_id' => $project_id,
                        'role' => Roles::UT_ADMIN,
                    ),
                    'order' => 't.name',
                ));

        return User::findAll($criteria);
    }
    
    public function findProjectManagersByProject($project_id) {
        $criteria = new CDbCriteria(array(
                    'join' => 'INNER JOIN ' . ProjectManager::model()->tableName() . ' managers ON managers.user_id = t.id',
                    'condition' => 'managers.project_id=:project_id',
                    'params' => array(
                        'project_id' => $project_id
                    ),
                    'order' => 't.name',
                ));

        return User::findAll($criteria);
    }

    /**
     * Get the Project Workers
     * @param type $project_id
     * @return type
     */
    public function findProjectWorkersByProject($project_id) {
        $criteria = new CDbCriteria(array(
                    'join' => 'INNER JOIN ' . ProjectWorker::model()->tableName() . ' workers ON workers.user_id = t.id',
                    'condition' => 'workers.project_id=:project_id',
                    'params' => array(
                        'project_id' => $project_id
                    ),
                    'order' => 't.name',
                ));

        return User::findAll($criteria);
    }
    
    public function getMailWorkersProject($projectId) {
        $workers = $this->findProjectWorkersByProject($projectId);
        $mails = array();
        foreach ($workers as $worker) {
            $mails[] = $worker->email;
        }
        return $mails;
    }
    
    public function getMailPmsProject($projectId) {
        $pms = $this->findProjectManagersByProject($projectId);
        $mails = array();
        foreach ($pms as $pm) {
            $mails[] = $pm->email;
        }
        return $mails;
    }   
    
    public function getMailCommercialProject($projectId) {
        $commercials = $this->findProjectCommercialByProject($projectId);
        $mails = array();
        foreach ($commercials as $commercial) {
            $mails[] = $commercial->email;
        }
        return $mails;
    }

    public function getMailAdministrative() {
        $administrative = $this->findAdministrativeUser();
        $mails = array();
        foreach ($administrative as $administrative) {
            $mails[] = $administrative->email;
        }
        return $mails;
    }

    /**
     * 
     * @param type $project_id
     * @return type
     */
    public function findProjectCommercialByProject($project_id) {
        
        $criteria = new CDbCriteria(array(
                    'join' => 'INNER JOIN ' . ProjectCommercial::model()->tableName() . ' commercials ON commercials.user_id = t.id',
                    'condition' => 'commercials.project_id=:project_id',
                    'params' => array(
                        'project_id' => $project_id
                    ),
                    'order' => 't.name',
                ));

        return User::findAll($criteria);
    }

    /**
     * Get the administrative roles.
     * @return type
     */
    public function findAdministrativeUser() {
        $criteria = new CDbCriteria(array(
                    'join' => ' INNER JOIN ' . AuthAssignment::model()->tableName() . ' roles ON roles.userid = t.id',
                    'condition' => 'roles.itemname = :role',
                    'params' => array(
                        'role' => Roles::UT_ADMINISTRATIVE
                    ),
                    'order' => 't.name',
                ));

        return User::findAll($criteria);
    }

    /**
     * @param Project $model
     * @return CDbCriteria
     */
    public function getCriteriaFromModel($model) {

        $criteria = new Yii\db\Query();
//        $isManager = (!Yii::$app->user->hasDirectorPrivileges()) && (!Yii::$app->user->hasCommercialPrivileges());
//
//        if (!$isManager) {
//            $criteria->with[] = 'managers';
//        }
        $criteria->andFilterWhere([
            'or',
            ['like', 't.id', $model->id],
        ]);
        if (isset($model->company_id)) {
            $criteria->andFilterWhere([
                'or',
                ['like', 't.company_id', $model->company_id],
            ]);
        }
        if ((!isset($model->company_id)) && isset($model->company_name)) {
            $criteria->andFilterWhere([
                'or',
                ['like', 'company.name', $model->company_name],
            ]);
          //  $criteria->addSearchCondition('company.name', $model->company_name);
        }
        $criteria->andFilterWhere([
            'or',
            ['like', 't.code', $model->code],
            ['like', 't.name', $model->name],
            ['like', 't.status', $model->status],
            ['like','t.statuscommercial', $model->statuscommercial],
            ['like','t.cat_type', $model->cat_type],
            ['like','managers.id', $model->manager_id],
        ]);
        if ($model->reporting == "0") {
            $criteria->where("t.reporting <> 'NONE'");
        } else if ($model->reporting == "1") {
            $criteria->where("t.reporting = 'NONE'");
        }
        if (!empty($model->open_time) && !empty($model->close_time)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($model->open_time, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($model->close_time, "23:59");

            $criteria->where("
                            (t.open_time <= :start_open AND t.close_time IS NULL) OR                            
                            (t.open_time <= :end_open AND t.close_time IS NULL) OR   
                            (t.open_time <= :start_open AND t.close_time >= :start_open) OR 
                            (:start_open <= t.open_time AND t.close_time <= :end_open) OR 
                            (:start_open <= t.open_time AND t.close_time >= :end_open) OR 
                            (t.open_time <= :start_open AND t.close_time >= :end_open)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else
        if (!empty($model->open_time)) {
            $model->open_time = PHPUtils::addHourToDateIfNotPresent($model->open_time, "00:00");
            $criteria->where("
                            (:start_open >= t.open_time AND :start_open <= t.close_time) OR 
                            (:start_open >= t.open_time AND t.close_time IS NULL) OR                            
                            (:start_open <= t.open_time)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($model->open_time);
        } else
        if (!empty($model->close_time)) {
            $model->close_time = PHPUtils::addHourToDateIfNotPresent($model->close_time, "23:59");
            $criteria->where('t.close_time', '<=' . PhpUtils::convertStringToDBDateTime($model->close_time));
        }
        if (!empty($model->manager_id)) {
            $criteria->where("
                            exists (select * from " . ProjectManager::model()->tableName() . " managers where managers.project_id = t.id and managers.user_id = :id_manager )");
            $criteria->params[':id_manager'] = $model->manager_id;
            
        }
        
//        if ($isManager) {
//            $userId = Yii::$app->user->id;
//            
//            ServiceFactory::createProjectService()->addCriteriaForProjectManagers($criteria, $userId);
//        }
        return $criteria;
    }

    public function getCSVContentFromSearch( $model) {
        $criteria = $this->getCriteriaFromModel($model, TRUE);
        $sWhere = "";
        if (isset($model->imputetype)) {
            $sWhere = " AND ( FALSE ";
            foreach($model->imputetype as $imputetype) {
                $sWhere .= " OR user_project_task.imputetype_id = ".$imputetype." ";
            }
            $sWhere .= " ) ";
        }
        $criteria->select = "*, 
                    ( select user.name from " . Project::TABLE_PROJECT_MANAGER . " inner join " . User::TABLE_USER . " on user.id = user_id where project_id = t.id order by user.name limit 1 ) as manager_custom,
                    ( select user.name from " . Project::TABLE_PROJECT_COMMERCIAL . " inner join " . User::TABLE_USER . " on user.id = user_id where project_id = t.id order by user.name limit 1 ) as commercial_custom,
                    ( select roundResult(sum( unix_timestamp(date_end) - unix_timestamp(date_ini) ) / 3600) from user_project_task where t.id = user_project_task.project_id ".$sWhere.") as totalSeconds, 
                    ( select count(*) from user_project_task where t.id = user_project_task.project_id ".$sWhere.") as taskCount,
                    ( select roundResult( ( roundResult(sum( unix_timestamp(date_end) - unix_timestamp(date_ini)) / 3600) / resultExist(max_hours) ) * 100) from user_project_task where t.id = user_project_task.project_id ) as executed,
                    ( select description from project_category where name = t.cat_type ) as category_name ";
        Yii::import('ext.csv.CSVExport');
        $criteria->order = "t.name asc";
        $data = Project::findAll($criteria);
        $csv = new CSVExport($data);
        $csv->includeColumnHeaders = true;
        $csv->headers = array(
            'Cliente',
            'Proyecto',
            'Apertura',
            'Cierre',
            'Manager',
            'Comercial',
            'Est. proyecto Op.',
            'Est. proyecto Com.',
            'Categoría',
            'Horas imputadas',
            'Horas previstas',
            'Horas threshold'
        );
        // Callback for giving an appropiate result format (array)
        $csv->callback = function($row) {
                    assert($row instanceof Project);
                    $results = array();
                    $results[] = $row->company->name;
                    $results[] = $row->name;
                    $results[] = PHPUtils::removeHourPartFromDate($row->open_time);
                    $results[] = (empty($row->close_time)) ? "Abierto" : PHPUtils::removeHourPartFromDate($row->close_time);
                    if (is_array($row->managers) && count($row->managers) > 0) {
                        $results[] = $row->managers[0]->name;
                    } else {
                        $results[] = "Sin manager";
                    }
                    if (is_array($row->commercials) && count($row->commercials) > 0) {
                        $results[] = $row->commercials[0]->name;
                    } else {
                        $results[] = "Sin comercial";
                    }
                    $results[] = ProjectStatus::toString($row->status);
                    $results[] = ProjectStatus::toString($row->statuscommercial);
                    $results[] = (empty($row->cat_type)) ? " " : ProjectCategories::toString($row->cat_type);
                    $results[] = $row->totalSeconds;
                    $results[] = $row->max_hours;
                    $results[] = $row->hours_warn_threshold;
                    return $results;
                };
        $sep = ( empty(Yii::$app->params['csv_separator']) ) ? ',' : Yii::$app->params['csv_separator'];
        return $csv->toCSV(null, $sep);
    }
    
    public function findProjectInTime($sStartDate, $sEndDate) {
            
        $criteria = new CDbCriteria(array(
                    'join' => ' INNER JOIN ' . UserProjectTask::model()->tableName() . ' upt ON upt.project_id = t.id '
                            . ' INNER JOIN ' . Company::model()->tableName() . ' co ON co.id = t.company_id '
                            . ' INNER JOIN ' . Imputetype::model()->tableName() . ' it ON it.id = upt.imputetype_id '
                            . ' INNER JOIN project_category pc ON pc.name = t.cat_type ' ,
                    'order' => 'totalhours desc',
                    'select' => 't.name, 
                                t.cat_type,
                                co.name as company_name,
                                pc.description as category_name,
                                it.name as imputetypeName,
                                -sum(round((unix_timestamp(upt.date_ini) - unix_timestamp(upt.date_end))/3600,2)) as totalhours',
                    'group' => 't.name, t.cat_type, co.name, pc.description, it.name'
                ));

        if (!empty($sStartDate) && !empty($sEndDate)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");

            $criteria->where("
                            (:start_open <= upt.date_ini AND :start_open <= upt.date_end) and   
                            (:end_open >= upt.date_ini AND :end_open >= upt.date_end)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else
        if (!empty($sStartDate)) {
            $sStartDate = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $criteria->where("
                            (:start_open <= upt.date_ini AND :start_open <= upt.date_end)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartDate);
        } else
        if (!empty($sEndDate)) {
            $sEndDate = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");
            $criteria->compare('upt.date_end', '<=' . PhpUtils::convertStringToDBDateTime($sEndDate));
        }
        
        return Project::findAll($criteria);
    }
}

?>
