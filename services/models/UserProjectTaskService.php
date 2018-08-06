<?php
namespace app\services\models;

use app\services\CronosService;
/**
 * Description of UserProjectTaskService
 *
 * @author twocandles
 */
class UserProjectTaskService implements CronosService {

    const MY_LOG_CATEGORY = 'services.UserProjectTaskService';

    public function isRangeInConflict($userId, DateTime $dateIni, DateTime $dateEnd, $taskId) {
        assert(is_numeric($userId));
        assert(is_numeric($taskId));
        $dbDateIni = PHPUtils::convertPHPDateTimeToDBDateTime($dateIni);
        $dbDateEnd = PHPUtils::convertPHPDateTimeToDBDateTime($dateEnd);

        $dbCommand = Yii::$app->db->createCommand()
                ->select('count(*)')
                ->from(UserProjectTask::model()->tableName())
                ->where('user_id = :user'
                        . ' AND ( date_ini < :dateend AND :dateini < date_end)'
                        . ' AND id != :task_id')
                ->bindParam('user', $userId)
                ->bindParam('dateini', $dbDateIni)
                ->bindParam('dateend', $dbDateEnd)
                ->bindParam('task_id', $taskId);

        $result = $dbCommand->queryScalar();
        return $result > 0;
    }

    /**
     * Retrieves a list of models based on the manager access rights
     * @return CDbCriteria the data provider that can return the models based on the search/filter conditions.
     */
    public function findNewByManager($mngr_id = null) {
        $criteria = new CDbCriteria();
        $criteria->with = array('worker', 'project');
        $criteria->addColumnCondition(array('t.status' => TaskStatus::TS_NEW));
        if (isset($mngr_id)) {
            $criteria->join = 'INNER JOIN project_manager pm ON pm.project_id=t.project_id';
            $criteria->addColumnCondition(array('pm.user_id' => $mngr_id));
            $criteria->addColumnCondition(array('project.status' => ProjectStatus::PS_OPEN));
        }
        return $criteria;
    }

    /**
     * Returns if the specified project has hours left to approve
     * @param int $projectId
     * @return boolean
     */
    public function hasProjectHoursToApprove($projectId) {
        assert(is_numeric($projectId));
        $taskSearch = new TaskSearch;
        $taskSearch->projectId = $projectId;
        $taskSearch->status = TaskStatus::TS_NEW;
        return UserProjectTask::model()->count($taskSearch->buildCriteria()) > 0;
    }

    /**
     *
     * @param integer $taskId
     * @param string $newProfile
     * @param CronosUser $sessionUser
     */
    public function approveTask($taskId, $newProfile, CronosUser $sessionUser, $newProject, $comment = "", $ticketId = "", $imputetype = "") {
        assert(is_numeric($taskId));
        assert(is_numeric($newProject));
        // Throws an exception if not found.
        if (!( $task = UserProjectTask::model()->findByPk((int) $taskId) ))
            throw new CHttpException(404, 'The requested page does not exist.');

        if (!WorkerProfiles::isValidValue($newProfile))
            throw new CHttpException(404, 'The requested page does not exist.');

        if (!( Project::model()->findByPk((int) $newProject) ))
            throw new CHttpException(404, 'The requested page does not exist.');

        // Check if project belongs to manager!
        if (!$this->isUserManagerOfProject($task->project_id, $sessionUser)) {
            Yii::log("User $sessionUser->username tried to approve a task FROM project $task->project_id");
            throw new CHttpException(403, 'No allowed to approve this task');
        }
        // Check if new project belongs to manager!
        if (!$this->isUserManagerOfProject($newProject, $sessionUser)) {
            Yii::log("User $sessionUser->guestName tried to approve a task TO project $newProject");
            throw new CHttpException(403, 'No allowed to approve this task');
        }
        // Check TaskStatus
        if ($task->status == TaskStatus::TS_APPROVED) {
            Yii::log("Can't approve task $taskId. Already approved", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY);
            return;
        } else if ($task->status != TaskStatus::TS_NEW) {
            Yii::log("Cant approve task $taskId. Not NEW!", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
            throw new CHttpException(403, 'No allowed to approve this task');
        }
        $this->doApprove($task, $newProfile, $newProject, $comment, $ticketId, $imputetype);
    }
    
    public function updateTask($taskId, $newProfile, CronosUser $sessionUser, $newProject, $comment = "", $ticketId = "", $imputetype = "") {
        assert(is_numeric($taskId));
        assert(is_numeric($newProject));
        
        // Throws an exception if not found.
        if (!( $task = UserProjectTask::model()->findByPk((int) $taskId) ))
            throw new CHttpException(404, 'The requested page does not exist.');

        if (!WorkerProfiles::isValidValue($newProfile))
            throw new CHttpException(404, 'The requested page does not exist.');

        if (!( Project::model()->findByPk((int) $newProject) ))
            throw new CHttpException(404, 'The requested page does not exist.');

        //Check if project belongs to manager!
        if (!$this->isUserManagerOfProject($task->project_id, $sessionUser)) {
            Yii::log("User $sessionUser->guestName tried to update a task FROM project $task->project_id");
            throw new CHttpException(403, 'No allowed to update this task');
        }
        // Check if new project belongs to manager!
        if (!$this->isUserManagerOfProject($newProject, $sessionUser)) {
            Yii::log("User $sessionUser->guestName tried to update a task TO project $newProject");
            throw new CHttpException(403, 'No allowed to update this task');
        }
        
        $this->doUpdate($task, $newProfile, $newProject, $comment, $ticketId, $imputetype);
    }

    /**
     * @param UserProjectTask $taskModel
     * @param string $profile
     */
    private function doApprove(UserProjectTask $taskModel, $profile, $project, $comment = "", $ticketId = "", $imputetype = "") {
        $taskModel->status = TaskStatus::TS_APPROVED;
        $this->doUpdate($taskModel, $profile, $project, $comment, $ticketId, $imputetype);
    }
    
    /**
     * Update a task
     * @param UserProjectTask $taskModel
     * @param type $profile
     * @param type $project
     * @param type $comment
     */
    private function doUpdate(UserProjectTask $taskModel, $profile, $project, $comment = "", $ticketId = "", $imputetype = "") {
        $taskModel->profile_id = $profile;
        if ($taskModel->task_description != $comment && $comment != "") {
            $taskModel->task_description = $comment;
        }
        if ($taskModel->ticket_id != $ticketId && $ticketId != "") {
            $taskModel->ticket_id = $ticketId;
        }
        if ($taskModel->imputetype_id != $imputetype && $imputetype != "") {
            $taskModel->imputetype_id = $imputetype;
        }
        if (!empty($project))
            $taskModel->project_id = $project;
        // No validation required
        $taskModel->save(false);
    }

    /**
     * Checks if a CronosUser is manager of project
     * @param int $projectId
     * @param CronosUser $sessionUser
     * @return boolean
     */
    private function isUserManagerOfProject($projectId, CronosUser $sessionUser) {
        assert(is_numeric($projectId));
        // If admin, then OK
        if ($sessionUser->hasDirectorPrivileges())
            return true;
        else {
            return  ( ServiceFactory::createProjectService()->isManagerOfProject(
                            $sessionUser->id, $projectId) ) || 
                    ( ServiceFactory::createProjectService()->isWorkerOfProject(
                            $sessionUser->id, $projectId) );
        }
    }

    private function getDateFieldsFromTaskSearch(TaskSearch $taskSearch) {
        $result = array();
        if (!empty($taskSearch->dateIni))
            $result[] = "GREATEST(t.date_ini, '" . PHPUtils::convertStringToDBDateTime($taskSearch->dateIni) . "')";
        else
            $result[] = 't.date_ini';
        if (!empty($taskSearch->dateEnd))
            $result[] = "LEAST(t.date_end, '" . PHPUtils::convertStringToDBDateTime($taskSearch->dateEnd) . "')";
        else
            $result[] = 't.date_end';
        return $result;
    }

    /**
     * Get hours and cost spent in a project
     * @param TaskSearch $tasksCriteria
     * @return array with the form array( 'hours' => <hours_spent>, 'price' => <cost> )
     */
    public function getTasksCost(TaskSearch $taskSearch) {
        $tasksCriteria = $taskSearch->buildCriteria();
        $dates = $this->getDateFieldsFromTaskSearch($taskSearch);
        $tasksCriteria->select = array(
            "sum(round((unix_timestamp($dates[1]) - unix_timestamp($dates[0]))/3600,2)) as custom1",
            "sum(round((unix_timestamp($dates[1]) - unix_timestamp($dates[0]))/3600,2)*price_per_hour) as custom2");
        // $result is a UserProjectTask model
        $taskModelForSearch = new UserProjectTask(UserProjectTask::SCENARIO_COST_SEARCH);
        $result = $taskModelForSearch->find($tasksCriteria);
        // Create command and execute
        if (empty($result->custom1))
            return array('hours' => 0, 'price' => 0);
        else
            return array(
                'hours' => round($result->custom1, 2),
                'price' => Round($result->custom2, 2));
    }

    /**
     * @param TaskSearch $taskSearch
     * @return CDbCriteria
     */
    public function getCriteriaFromTaskSearch(TaskSearch $taskSearch) {
        $criteria = $taskSearch->buildCriteria();
        $dates = $this->getDateFieldsFromTaskSearch($taskSearch);
        $criteria->with = array('worker', 'project', 'imputetype', 'project.company');
        $criteria->select = array(
            'id',
            'user_id',
            'project_id',
            'status',
            "$dates[0] as date_ini",
            "$dates[1] as date_end",
            "imputetype.name as imputetypeName",
            'task_description',
            'ticket_id',
            'profile_id',
            'price_per_hour',
            'is_extra',
            'is_billable',
            'imputetype_id'
        );
        $criteria->select = "*, 
                    ( select user.name from " . Project::TABLE_PROJECT_MANAGER . " inner join " . User::TABLE_USER . " on "
                 . "" . User::TABLE_USER . ".id = " . Project::TABLE_PROJECT_MANAGER . ".user_id "
                 . "where " . Project::TABLE_PROJECT_MANAGER . ".project_id = t.project_id order by user.name limit 1 ) as managerName";
        $criteria->order = "date_ini desc, date_end";
        return $criteria;
    }
    
    /**
     * Retrieve the summary from TaskSearch
     * @param TaskSearch $taskSearch
     * @return type
     */
    public function getCriteriaFromTaskSearchSummaryProject(TaskSearch $taskSearch) {
        
        $criteria = new CDbCriteria(array(
                    'order' => 'totalhours desc',
                ));
        $criteria = $taskSearch->buildCriteria($criteria);
        //print_r($criteria);
        //die();
        //$criteria->together = true;
        //$criteria->with = array('project');
        
        //$criteria = $taskSearch->buildCriteria();
        //$criteria->with = array('worker', 'project', 'imputetype', 'project.company');
        $criteria->order = 'totalhours desc';
        $criteria->select = 't.project_id, 
                                (SELECT c.name FROM ' .Project::TABLE_PROJECT. ' p, '.Company::TABLE_COMPANY.' c WHERE c.id = p.company_id and p.id = t.project_id limit 1) as companyName,
                                (SELECT u.name FROM ' .Project::TABLE_PROJECT_MANAGER. ' pm, '.User::TABLE_USER. ' u WHERE u.id = pm.user_id and pm.project_id = t.project_id limit 1) as projectManager,
                                (SELECT min(upt.date_ini) FROM ' .UserProjectTask::TABLE_USER_PROJECT_TASK. ' upt WHERE upt.project_id = t.project_id limit 1) as firstUserProjectTask,
                                (SELECT max(upt.date_ini) FROM ' .UserProjectTask::TABLE_USER_PROJECT_TASK. ' upt WHERE upt.project_id = t.project_id limit 1) as lastUserProjectTask,
                                roundResult(sum( unix_timestamp(date_end) - unix_timestamp(date_ini) ) / 3600) as totalhours';
                                
                
        $criteria->group = 't.project_id';
        $criteria->limit = 10;
        return UserProjectTask::model()->findAll($criteria);
    }
    
    /**
     *
     * @param UserProjectTask $task
     * @return boolean
     */
    protected function managePossibleSplittedTask(UserProjectTask $task) {
        // Task must be validated
        assert($task->validate());
        $hourValues = $this->checkIfTaskExceedsLimitsOfProject($task);
        // Apply max hours behaviour?
        if ($hourValues['taskOutOfHours']) {
            // New task completely out of project time
            $task->status = TaskStatus::TS_ORPHAN;
        } else if ($hourValues['maxHoursExceeded']) {
            // Split hours!
            $hoursLeftToMax = $hourValues['hoursLeftToMax'];
            $originalDuration = $task->getDuration();
            // Incr. hours
            $task->date_end->setTimestamp($task->date_ini->getTimestamp() + ( $hoursLeftToMax * Constants::SECONDS_PER_HOUR ));
            $task->refreshHoursFromTimestamps();
            $newOrphanTask = new UserProjectTask(UserProjectTask::SCENARIO_NO_VALIDATION);
            $newOrphanTask->attributes = $task->attributes;
            $newOrphanTask->date_ini->setTimestamp($task->date_end->getTimestamp());
            $newOrphanTask->date_end->setTimestamp($newOrphanTask->date_ini->getTimestamp() + ( ( $originalDuration - $hoursLeftToMax ) * Constants::SECONDS_PER_HOUR ));
            $newOrphanTask->status = TaskStatus::TS_ORPHAN;
            //$newOrphanTask->refreshHoursFromTimestamps();
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $task->save(false);
            // Save it after the "original" so it appears after in listings
            if (isset($newOrphanTask)) {
                $newOrphanTask->save(false);
            }
            $transaction->commit();
        } catch (Exception $e) {
            Yii::log('Could not commit transaction ' . $e, CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
            $transaction->rollback();
            throw new CHttpException(500, 'Internal server error');
        }
        return true;
    }

    /**
     * @param UserProjectTask $model
     * @param boolean $isWorker
     * @param CronosUser $userInfo
     */
    public function saveNewTask(UserProjectTask $model, CronosUser $userInfo) {
        // If worker, set forced params
        if (!$userInfo->hasDirectorPrivileges()) {
            assert($userInfo != null);
            $model->user_id = $userInfo->id;
            $model->status = TaskStatus::TS_NEW;
            $model->profile_id = $userInfo->profile;
            $model->is_billable = TRUE;
        }
        //Check if valid task param
        if (!$model->validate())
            return false;
        return $this->managePossibleSplittedTask($model);
    }

    /**
     * Retrieves CSV content with the criteria specified in TaskSearch
     * @param TaskSearch $taskSearch
     * @param CronosUser $user user launching the search
     * @return string
     */
    public function getCSVContentFromTaskSearch(TaskSearch $taskSearch, CronosUser $user) {
        // Tweak task search for user
        if ($user->hasDirectorPrivileges() || $user->hasRole(Roles::UT_ADMINISTRATIVE)) {
            $searchFlag = TaskSearchService::SEARCH_AS_ADMIN;
        } else if ($user->hasRole( Roles::UT_COMERCIAL )) {
            $searchFlag = TaskSearchService::SEARCH_AS_COMMERCIAL;
        } else {
            assert($user->hasRole(Roles::UT_PROJECT_MANAGER));
            $searchFlag = TaskSearchService::SEARCH_AS_MANAGER;
        }
        // Only interested on the tweaked taskSearch, so ignoring output
        ServiceFactory::createTaskSearchService()->getTaskSearchFormProvidersForProfile($taskSearch, $user, $searchFlag);
        $tasksCriteria = $this->getCriteriaFromTaskSearch($taskSearch);
        Yii::import('ext.csv.CSVExport');
        $data = UserProjectTask::model()->findAll($tasksCriteria);
        
        $csv = new CSVExport($data);
        $csv->includeColumnHeaders = true;
        $csv->headers = array(
            'Imputador',
            'Cliente',
            'Proyecto',
            'Perfil',
            'Tipo de imputación',
            'Fecha inicial',
            'Fecha final',
            'Duración',
            'Ticket',
            'Descripción',
            'Extra',
            'Facturable',
        );
        // Callback for giving an appropiate result format (array)
        $csv->callback = function( $row ) {
                    assert($row instanceof UserProjectTask);
                    $results = array();
                    $results[] = $row->worker->name;
                    $results[] = $row->project->company->name;
                    $results[] = $row->project->name;
                    $results[] = WorkerProfiles::toString($row->profile_id);
                    $oImputetype = Imputetype::model()->findByPk($row->imputetype_id);
                    $results[] = $oImputetype->name;
                    $results[] = $row->getLongDateIni();
                    $results[] = $row->getLongDateEnd();
                    $results[] = $row->getFormattedDuration();
                    $results[] = $row->ticket_id;
                    $results[] = $row->task_description;
                    $results[] = $row->is_extra ? "X" : "";
                    $results[] = $row->is_billable ? "X" : "";
                    return $results;
                };
        $sep = ( empty(Yii::$app->params['csv_separator']) ) ? ',' : Yii::$app->params['csv_separator'];
        return $csv->toCSV(null, $sep);
    }

    /**
     * Find the user project tasks in time.
     * @param type $sStartDate
     * @param type $sEndDate
     * @param type $onlyBillable
     * @param type $iCustomer
     * @param type $iProject
     * @param type $iWorker
     * @return type
     */
    public function findUserProjectTasksInTime($sStartDate, $sEndDate, $onlyBillable = true, $iCustomer = "", $iProject = "", $iWorker = "") {

        $criteria = new CDbCriteria(array(
                    'join' => ' INNER JOIN ' . Project::model()->tableName() . ' proj ON t.project_id = proj.id 
                                INNER JOIN ' . User::model()->tableName() . ' us ON t.user_id = us.id 
                                INNER JOIN ' . Company::model()->tableName() . ' com ON proj.company_id = com.id',
                    'order' => 'com.name asc, proj.name, us.name',
                    'select' => '*, 
                         com.name as companyName,
                         proj.name as projectName,
                         us.name as workerName,
                         us.hourcost as workerCost'
                ));

        if ($iWorker != "") {
            $criteria->addCondition(" t.user_id = '".$iWorker."' ");
        }
        if ($onlyBillable) {
            $criteria->addCondition("t.is_billable = 1");
            $criteria->addCondition("t.status = '" . TaskStatus::TS_APPROVED . "'");
        }
        if ($iCustomer != "") {
            $criteria->addCondition("proj.company_id = " . $iCustomer);
        }
        if ($iProject != "") {
            $criteria->addCondition("t.project_id = " . $iProject);
        }

        if (!empty($sStartDate) && !empty($sEndDate)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");

            $criteria->addCondition("
                            (:start_open <= t.date_ini AND :start_open <= t.date_end) and   
                            (:end_open >= t.date_ini AND :end_open >= t.date_end)");

            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else
        if (!empty($sStartDate)) {
            $sStartDate = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $criteria->addCondition("
                            (:start_open <= t.date_ini AND :start_open <= t.date_end)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartDate);
        } else
        if (!empty($sEndDate)) {
            $sEndDate = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");
            $criteria->compare('t.date_end', '<=' . PhpUtils::convertStringToDBDateTime($sEndDate));
        }
        
        return UserProjectTask::model()->findAll($criteria);
    }

    /**
     * Retrieves an array of UserProjectTasks belonging (imputed) by user and "around"
     * the date specified (imputed some hours before and after the specified date)
     * @param int $userId
     * @param DateTime $date
     * @return array array of UserProjectTasks objects
     */
    public function findTasksAroundDateForUser($userId, DateTime $date, $companyId = null) {
        assert(is_int($userId));
        $dateObj = clone $date;
        $dateObj2 = clone $date;

        $ts = new TaskSearch;
        if ($companyId != null && $companyId != "") {
            $ts->companyId = $companyId;
        }
        if ($userId != null && $userId != "") {
            $ts->creator = $userId;
        }
        // Convert intervals to string for searching
        $ts->dateIni = PHPUtils::converPHPDateTimeToString(PHPUtils::getStartOfWeek($dateObj));
        $ts->dateEnd = PHPUtils::converPHPDateTimeToString(PHPUtils::getEndOfWeek($dateObj));
        // Build the query
        $tasksCriteria = ServiceFactory::createUserProjectTaskService()->getCriteriaFromTaskSearch($ts);
        return UserProjectTask::model()->findAll($tasksCriteria);
    }

    private function ensureCustomerCanRefuseTask(CronosUser $user, $task, $taskId) {
        // Check task is valid
        if (!($task instanceof UserProjectTask)) {
            Yii::log("El usuario {$user->getName()} ha intentado rechazar la tarea $taskId que no existe");
            throw new CHttpException(404, 'No existe la página solicitada');
        }
        // If not admin nor is a customer with privileges => no access
        if ((!$user->hasDirectorPrivileges() )
                && (!ServiceFactory::createProjectService()->isCustomerOfProject($user->getId(), $task->project_id) )) {
            Yii::log("El usuario {$user->getName()} ha intentado rechazar la tarea $task->id y no tiene acceso");
            throw new CHttpException(403, 'No tiene acceso a la página solicitada.');
        }
        // Check project open
        if ($task->project->status !== ProjectStatus::PS_OPEN) {
            Yii::log("El usuario {$user->getName()} ha intentado rechazar la tarea $taskId de un proyecto CERRADO!! ");
            throw new CHttpException(403, 'No tiene acceso a la página solicitada.');
        }
        // Check task approved
        if ($task->status !== TaskStatus::TS_APPROVED) {
            Yii::log("El usuario {$user->getName()} ha intentado rechazar la tarea $task->id que no está aprobada");
            return false;
        }
        return true;
    }

    /**
     * Refuses a task, inserting a record into task_history.
     *
     * Returns a model of the new entry in task_history or false
     * if the task is not approved
     *
     * @param CronosUser $user
     * @param int $taskId
     * @param string $motive
     * @return TaskHistory if everything goes right. If task is not approved
     * then false is returned
     * @throws CHttpException if:
     * - $user has no access (customer without access to project, project manager...)
     * - $task does not exist
     * - $motive empty
     * - $user does not exist
     */
    public function refuseTask(CronosUser $user, $taskId, $motive) {
        // Is task approved so it can be refused
        $task = UserProjectTask::model()->findByPk($taskId);
        if ($this->ensureCustomerCanRefuseTask($user, $task, $taskId) === false)
            return false;

        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            // Save task record
            $task->status = TaskStatus::TS_REJECTED;
            if (!$task->save()) {
                Yii::log('Error actualizando estado de tarea: ' . print_r($task->getErrors(), true), CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
                throw new CHttpException(500, 'Error al guardar la tarea');
            }
            // Save task history record
            $taskHistory = new TaskHistory();
            $taskHistory->user_project_task_id = $taskId;
            $taskHistory->user_id = $user->getId();
            $taskHistory->comment = $motive;
            $taskHistory->status = TaskStatus::TS_REJECTED;
            $taskHistory->timestamp = PHPUtils::getNowAsString(true);
            if (!$taskHistory->save()) {
                Yii::log('Error guardando histórico de tarea: ' . print_r($taskHistory->getErrors(), true), CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
                throw new CHttpException(500, 'Error al guardar la tarea');
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
        // If everything ok, try to notify
        ServiceFactory::createAlertService()->notify(Alerts::TASK_REFUSED_BY_CUSTOMER, array(
            'task.description' => $task->task_description,
            'project.name' => $task->project->name,
            'user.name' => $user->getName(),
        ));
        return $taskHistory;
    }

    /**
     * @param int $projectId
     * @return array
     */
    private function getEmailsForManagersOfProject($projectId) {
        $managers = ServiceFactory::createProjectService()->findProjectManagersAndAdminByProject($projectId);
        $mails = array();
        foreach ($managers as $manager) {
            $mails[] = $manager->email;
        }
        return $mails;
    }

    /**
     * Notifies when a task exeedes project hours
     *
     * @param UserProjectTask $task
     * @return array list of notifier errors. The list has the signature
     * array(
     *   array(
     *     'name' => 'NotifierClass',
     *     'message' => 'error message',
     *   ),
     * )
     */
    public function alertIfTaskExceedsProjectLimits(UserProjectTask $task) {
        $hourValues = $this->checkIfTaskExceedsLimitsOfProject($task);
        $notifierErrors = array();
        $aCommercials = ServiceFactory::createProjectService()->getMailPmsProject($task->project_id);
        $aManagers = ServiceFactory::createProjectService()->getMailCommercialProject($task->project_id);
        
        // Check thresholds
        if ($hourValues['warnThresholdExceeded']) {
            Yii::log("Hours warning threshold for project {$task->project->name} exceeded!", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY);            
            $as = ServiceFactory::createAlertService();
            $project = Project::model()->findByPk($task->project_id);
            $as->notify(Alerts::PROJECT_HOURS_WARNING, array(
                Alerts::MESSAGE_REPLACEMENTS => array(
                    'project.name' => $task->project->name,
                    'customer.name' => $task->project->company->name,
                    'project.hours_after' => $hourValues['projectHoursAfter'],
                    'project.warn_hours_threshold' => $project->hours_warn_threshold,
                ),
                EmailNotifier::NOTIFICATION_RECEIVERS => array_merge($aManagers, $aCommercials),
            ));
            array_merge($notifierErrors, $as->getNotifierErrors());
        }
        if ($hourValues['maxHoursExceeded']) {
            Yii::log("Hours assigned for project {$task->project->name} exceeded!", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY);
            if (!isset($project)) {
                $project = Project::model()->findByPk($task->project_id);
            }
            $project = Project::model()->findByPk($task->project_id);
            // Notify always
            $as = ServiceFactory::createAlertService();
            $as->notify(Alerts::PROJECT_HOURS_EXCEEDED, array(
                Alerts::MESSAGE_REPLACEMENTS => array(
                    'project.name' => $task->project->name,
                    'customer.name' => $task->project->company->name,
                    'project.hours_after' => $hourValues['projectHoursAfter'],
                    'project.max_hours' => $project->max_hours,
                ),
                EmailNotifier::NOTIFICATION_RECEIVERS => array_merge($aManagers, $aCommercials),
            ));
            array_merge($notifierErrors, $as->getNotifierErrors());
        }
        return $notifierErrors;
    }

    /**
     * Retrieves some data about a task and if it exceeds project limits.
     * THIS METHODS RELIES ON THE FACT THAT THE TASK IS VALIDATED (or at least
     * that the getDuration() method returns a valid value
     * @param UserProjectTask $task
     * @return array[] associative array with
     * 'warnThresholdExceeded' => bool true if warning threshold exceeded
     * 'maxHoursExceeded' => bool true if project max hours exceeded
     * 'projectHoursBefore' => int hours of project before task
     * 'projectHoursAfter' => int hours of project after task
     * 'taskOutOfHours' => bool true project was out of hours before task
     * 'hoursLeftToMax' => float number of hours left for reaching max hours of project
     * @todo Pass in an instance of project
     */
    private function checkIfTaskExceedsLimitsOfProject(UserProjectTask $task) {
        $result = array();
        //$project = Project::model()->findByPk($task->project_id);
        $criteria = new CDbCriteria();
        $criteria->select = "*, 
                    ( select user.name from " . Project::TABLE_PROJECT_MANAGER . " inner join " . User::TABLE_USER . " on user.id = user_id where project_id = t.id order by user.name limit 1 ) as manager_custom,
                    ( select user.name from " . Project::TABLE_PROJECT_COMMERCIAL . " inner join " . User::TABLE_USER . " on user.id = user_id where project_id = t.id order by user.name limit 1 ) as commercial_custom,
                    ( select roundResult(sum( unix_timestamp(date_end) - unix_timestamp(date_ini) ) / 3600) from user_project_task where t.id = user_project_task.project_id) as totalSeconds, 
                    ( select count(*) from user_project_task where t.id = user_project_task.project_id ) as taskCount,
                    ( select roundResult( ( roundResult(sum( unix_timestamp(date_end) - unix_timestamp(date_ini)) / 3600) / resultExist(max_hours) ) * 100) from user_project_task where t.id = user_project_task.project_id ) as executed,
                    ( select description from project_category where name = t.cat_type ) as category_name ";
        $criteria->compare('t.id', $task->project_id);
        $project = Project::model()->find($criteria);
        
        if ($project->hours_warn_threshold == 0 && $project->max_hours == 0) {
            $result['warnThresholdExceeded'] = false;
            $result['maxHoursExceeded'] = false;
            $result['projectHoursBefore'] = 0;
            $result['projectHoursAfter'] = 0;
            $result['hoursLeftToMax'] = 0;
            $result['taskOutOfHours'] = false;
        } else {
            $taskDuration = $task->getDuration();

            // Take into account that if the task is not new => the project already
            // contains its hours
            if (!$task->isNewRecord) {
                $oldTask = UserProjectTask::model()->findByPk($task->id);
                $factor = $oldTask->getDuration();
            } else {
                $factor = 0.0;
            }
            $result['projectHoursBefore'] = $project->getTotalHours();
            $result['projectHoursAfter'] = $result['projectHoursBefore'] + $taskDuration - $factor;
            $result['hoursLeftToMax'] = max($project->max_hours - $result['projectHoursBefore'], 0);
            
            $result['warnThresholdExceeded'] = ( $project->hours_warn_threshold > 0.00 )
                    && ( $result['projectHoursBefore'] < $project->hours_warn_threshold )
                    && ( $result['projectHoursAfter'] >= $project->hours_warn_threshold );
            // Exceeded means EXCEEDED (so '>')
            $result['maxHoursExceeded'] =
                    ( $project->max_hours > 0 )
                    && ( $result['projectHoursBefore'] <= $project->max_hours )
                    && ( $result['projectHoursAfter'] > $project->max_hours );
            $result['taskOutOfHours'] = $result['projectHoursBefore'] >= $project->max_hours;
            
            

            // Lets make some roundings
            $result['projectHoursBefore'] = round($result['projectHoursBefore'], self::TIME_PRECISION);
            $result['projectHoursAfter'] = round($result['projectHoursAfter'], self::TIME_PRECISION);
            $result['hoursLeftToMax'] = round($result['hoursLeftToMax'], self::TIME_PRECISION);
        }
        return $result;
    }

    const TIME_PRECISION = 4;

}

?>
