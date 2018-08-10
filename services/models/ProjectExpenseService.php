<?php
namespace app\services\models;

use app\services\CronosService;
/**
 * Description of UserProjectTaskService
 *
 * @author twocandles
 */
class ProjectExpenseService implements CronosService {

    const MY_LOG_CATEGORY = 'services.ProjectExpenseService';

    /**
     *
     * @param integer $expenseId
     * @param string $newProfile
     * @param CronosUser $sessionUser
     */
    public function approveCost($expenseId, CronosUser $sessionUser) {
        assert(is_numeric($expenseId));
        // Throws an exception if not found.
        if (!( $oProjectExpense = ProjectExpense::model()->findByPk((int) $expenseId) ))
            throw new CHttpException(404, 'The requested page does not exist.');

        // Check if project belongs to manager!
        if (!$this->isUserManagerOfProject($oProjectExpense->project_id, $sessionUser)) {
            Yii::log("User $sessionUser->username tried to approve a cost FROM project $$oProjectExpense->project_id");
            throw new CHttpException(403, 'No allowed to approve this cost');
        }

        // Check TaskStatus
        if ($oProjectExpense->status == TaskStatus::TS_APPROVED) {
            Yii::log("Can't approve task $expenseId. Already approved", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY);
            return;
        } else if ($oProjectExpense->status != TaskStatus::TS_NEW) {
            Yii::log("Cant approve task $expenseId. Not NEW!", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
            throw new CHttpException(403, 'No allowed to approve this task');
        }
        $this->doApprove($oProjectExpense);
    }

    /**
     * @param ProjectExpense $projectExpenseModel
     * @param string $profile
     */
    private function doApprove(ProjectExpense $projectExpenseModel) {
        $projectExpenseModel->status = TaskStatus::TS_APPROVED;
        $projectExpenseModel->save(false);
    }

    /**
     * Check the expense can be refused.
     * @param CronosUser $user
     * @param ProjectExpense $oProjectExpense
     * @param type $expenseId
     * @return boolean
     * @throws CHttpException
     */
    private function ensureCanRefuseCost(CronosUser $user, $oProjectExpense, $expenseId) {
        // Check task is valid
        if (!($oProjectExpense instanceof ProjectExpense)) {
            Yii::log("El usuario {$user->getName()} ha intentado rechazar la tarea $expenseId que no existe");
            throw new CHttpException(404, 'No existe la página solicitada');
        }
        // Check project open
        if ($oProjectExpense->project->status !== ProjectStatus::PS_OPEN) {
            Yii::log("El usuario {$user->getName()} ha intentado rechazar la tarea $expenseId de un proyecto CERRADO!! ");
            throw new CHttpException(403, 'No tiene acceso a la página solicitada.');
        }
        // Check task approved
        if ($oProjectExpense->status !== TaskStatus::TS_APPROVED) {
            Yii::log("El usuario {$user->getName()} ha intentado rechazar la tarea $oProjectExpense->id que no está aprobada");
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
     * @param int $expenseId
     * @param string $motive
     * @return TaskHistory if everything goes right. If task is not approved
     * then false is returned
     * @throws CHttpException if:
     * - $user has no access (customer without access to project, project manager...)
     * - $task does not exist
     * - $motive empty
     * - $user does not exist
     */
    public function refuseCost(CronosUser $user, $expenseId) {
        // Is task approved so it can be refused
        $oProjectExpense = ProjectExpense::model()->findByPk($expenseId);
        if ($this->ensureCanRefuseCost($user, $oProjectExpense, $expenseId) === false)
            return false;

        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            // Save task record
            $oProjectExpense->status = TaskStatus::TS_REJECTED;
            if (!$oProjectExpense->save()) {
                Yii::log('Error actualizando estado de tarea: ' . print_r($oProjectExpense->getErrors(), true), CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
                throw new CHttpException(500, 'Error al guardar la tarea');
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
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
        else
            return( ServiceFactory::createProjectService()->isManagerOfProject(
                            $sessionUser->id, $projectId) );
    }

    private function getDateFieldsFromExpenseSearch(ExpenseSearch $expenseSearch) {
        $result = array();
        if (!empty($expenseSearch->dateIni))
            $result[] = "GREATEST(t.date_ini, '" . PHPUtils::convertStringToDBDateTime($expenseSearch->dateIni) . "')";
        else
            $result[] = 't.date_ini';
        return $result;
    }

    public function getCriteriaFromModel(ProjectExpense $model) {

        $criteria = new CDbCriteria(array(
                    'with' => array('user'),
                    'order' => 't.date_ini asc',
                    'together' => true,
                ));
        $isManager = (!Yii::$app->user->hasDirectorPrivileges()) && (!Yii::$app->user->hasCommercialPrivileges());

        if (!$isManager) {
            $criteria->with[] = 'managers';
        }
        $criteria->compare('t.id', $model->id);
        if (isset($model->project_id)) {
            $criteria->compare('t.project_id', $model->project_id);
        }
        $criteria->compare('t.manager_id', $model->manager_id);
        $criteria->compare('t.status', $model->status, false);
        if (!empty($model->open_time) && !empty($model->close_time)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($model->open_time, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($model->close_time, "23:59");

            $criteria->addCondition("
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
            $criteria->addCondition("
                            (:start_open >= t.open_time AND :start_open <= t.close_time) OR 
                            (:start_open >= t.open_time AND t.close_time IS NULL) OR                            
                            (:start_open <= t.open_time)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($model->open_time);
        } else
        if (!empty($model->close_time)) {
            $model->close_time = PHPUtils::addHourToDateIfNotPresent($model->close_time, "23:59");
            $criteria->compare('t.close_time', '<=' . PhpUtils::convertStringToDBDateTime($model->close_time));
        }
        if ($isManager) {
            $userId = Yii::$app->user->id;
            ServiceFactory::createProjectExpenseService()->addCriteriaForProjectManagers($criteria, $userId);
        }
        return $criteria;
    }

    /**
     * Adds the conditions for filtering projects a project manager has access to
     * @param CDbCriteria $criteria
     * @param type $userId
     */
    public function addCriteriaForProjectManagers(CDbCriteria $criteria, $userId) {
        $criteria->join = 'INNER JOIN ' . ProjectManager::model()->tableName() . ' managers ON managers.project_id = t.project_id';
        $criteria->addColumnCondition(array('managers.user_id' => $userId));
    }

    /**
     * @param ExpenseSearch $expenseSearch
     * @return CDbCriteria
     */
    public function getCriteriaFromExpenseSearch(ExpenseSearch $expenseSearch) {
        $criteria = $expenseSearch->buildCriteria();
        $criteria->with = array('worker', 'project', 'project.company');
        //$criteria->select = "*";
        $criteria->join = ' INNER JOIN ' . Project::model()->tableName() . ' proj ON t.project_id = proj.id 
                                INNER JOIN ' . User::model()->tableName() . ' us ON t.user_id = us.id 
                                INNER JOIN ' . Company::model()->tableName() . ' com ON proj.company_id = com.id';

        if ($expenseSearch->sort == "") {
            $criteria->order = 'com.name asc, proj.name, us.name';
        }
        $criteria->select = 't.*, 
                         com.name as companyName,
                         proj.name as projectName,
                         us.name as workerName';
        return $criteria;
    }

    /**
     * 
     * @param ProjectExpense $model
     * @param array $newData
     * @return type
     */
    public function createProjectExpense(ProjectExpense $model, array $newData) {
//        $model->unsetAttributes();
        $model->status = TaskStatus::TS_NEW;
        return $this->saveProjectExpense($model, $newData);
    }

    /**
     * 
     * @param ProjectExpense $model
     * @param type $newData
     * @return boolean
     * @throws Exception
     */
    private function saveProjectExpense($model, $newData) {
        // Massively set attributes
        $model->attributes = $newData;
        $model->importe = str_replace(",", ".", $model->importe);
        if ($file = CUploadedFile::getInstance($model, 'pdffile')) {
            
            $model->pdffile = file_get_contents($file->tempName);
            if (strtolower($file->getExtensionName()) != "pdf") {
                Yii::log('File format : ' . $file->getExtensionName() . " Not supported. Only PDF", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
                Yii::$app->user->setFlash(Constants::FLASH_ERROR_MESSAGE, 'File format : ' . $file->getExtensionName() . " Not supported. Only PDF");
                return false;
            }
            
        }
        
        // Ready to save
        $transaction = $model->dbConnection->beginTransaction();
        $allSavesOK = false;
        try {
            // Save relationships
            if ($model->save()) {
                $allSavesOK = true;
            }
            if ($allSavesOK) {
                Yii::log('Expenses from project ' . $model->project->name . ' saved OK', CLogger::LEVEL_INFO, self::MY_LOG_CATEGORY);
                $transaction->commit();
            } else {
                Yii::log('Error saving expenses from project : ' . $model->project->name, CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
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
     * Updates an existing project
     * @param Project $model
     * @param array $newData
     * @return boolean
     */
    public function updateProjectExpense(ProjectExpense $model, array $newData) {
        return $this->saveProjectExpense($model, $newData);
    }

    /**
     * Retrieve the project costs in a gap of time.
     * @param type $sStartDate
     * @param type $sEndDate
     * @param type $onlyBillable
     * @param type $iCustomer
     * @param type $iProject
     * @param type $iWorker
     * @return type
     */
    public function findUserProjectCostsInTime($sStartDate, $sEndDate, $onlyBillable = true, $iCustomer = "", $iProject = "", $iWorker = "") {

        $criteria = new CDbCriteria(array(
                    'join' => ' INNER JOIN ' . Project::model()->tableName() . ' proj ON t.project_id = proj.id 
                                INNER JOIN ' . User::model()->tableName() . ' us ON t.user_id = us.id 
                                INNER JOIN ' . Company::model()->tableName() . ' com ON proj.company_id = com.id',
                    'order' => 'com.name asc, proj.name, us.name',
                    'select' => '*, 
                         com.name as companyName,
                         proj.name as projectName,
                         us.name as workerName'
                ));

        if ($onlyBillable) {
            //$criteria->addCondition("t.status = '" . TaskStatus::TS_APPROVED . "'");
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
                            (:start_open <= t.date_ini) and   
                            (:end_open >= t.date_ini)");

            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else
        if (!empty($sStartDate)) {
            $sStartDate = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $criteria->addCondition("
                            (:start_open <= t.date_ini)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartDate);
        } else
        if (!empty($sEndDate)) {
            $sEndDate = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");
            $criteria->compare('t.date_ini', '<=' . PhpUtils::convertStringToDBDateTime($sEndDate));
        }

        return ProjectExpense::model()->findAll($criteria);
    }

    const TIME_PRECISION = 4;

}

?>
