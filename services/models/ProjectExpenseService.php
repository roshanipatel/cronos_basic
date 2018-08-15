<?php
namespace app\services\models;

use app\services\CronosService;
use app\models\db\Project;
use app\models\User;
use app\models\db\Company;
use app\models\enums\TaskStatus;
use app\components\utils\PHPUtils;
use app\models\db\ProjectExpense;
use app\models\db\ProjectManager;

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
    public function approveCost($expenseId, $sessionUser) {
        assert(is_numeric($expenseId));
        // Throws an exception if not found.
        if (!( $oProjectExpense = ProjectExpense::findOne((int) $expenseId) ))
            throw new HttpException(404, 'The requested page does not exist.');

        // Check if project belongs to manager!
        if (!$this->isUserManagerOfProject($oProjectExpense->project_id, $sessionUser)) {
            Yii::info("User $sessionUser->username tried to approve a cost FROM project $$oProjectExpense->project_id",__METHOD__);
            throw new HttpException(403, 'No allowed to approve this cost');
        }

        // Check TaskStatus
        if ($oProjectExpense->status == TaskStatus::TS_APPROVED) {
            Yii::warning("Can't approve task $expenseId. Already approved",__METHOD__);
            return;
        } else if ($oProjectExpense->status != TaskStatus::TS_NEW) {
            Yii::error("Cant approve task $expenseId. Not NEW!", __METHOD__);
            throw new HttpException(403, 'No allowed to approve this task');
        }
        $this->doApprove($oProjectExpense);
    }

    /**
     * @param ProjectExpense $projectExpenseModel
     * @param string $profile
     */
    private function doApprove($projectExpenseModel) {
        $projectExpenseModel->status = TaskStatus::TS_APPROVED;
        $projectExpenseModel->save(false);
    }

    /**
     * Check the expense can be refused.
     * @param CronosUser $user
     * @param ProjectExpense $oProjectExpense
     * @param type $expenseId
     * @return boolean
     * @throws HttpException
     */
    private function ensureCanRefuseCost($user, $oProjectExpense, $expenseId) {
        // Check task is valid
        if (!($oProjectExpense instanceof ProjectExpense)) {
            Yii::info("El usuario {$user->getName()} ha intentado rechazar la tarea $expenseId que no existe",__METHOD__);
            throw new HttpException(404, 'No existe la página solicitada');
        }
        // Check project open
        if ($oProjectExpense->project->status !== ProjectStatus::PS_OPEN) {
            Yii::info("El usuario {$user->getName()} ha intentado rechazar la tarea $expenseId de un proyecto CERRADO!! ",__METHOD__);
            throw new HttpException(403, 'No tiene acceso a la página solicitada.');
        }
        // Check task approved
        if ($oProjectExpense->status !== TaskStatus::TS_APPROVED) {
            Yii::info("El usuario {$user->getName()} ha intentado rechazar la tarea $oProjectExpense->id que no está aprobada",__METHOD__);
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
     * @throws HttpException if:
     * - $user has no access (customer without access to project, project manager...)
     * - $task does not exist
     * - $motive empty
     * - $user does not exist
     */
    public function refuseCost($user, $expenseId) {
        // Is task approved so it can be refused
        $oProjectExpense = ProjectExpense::findOne($expenseId);
        if ($this->ensureCanRefuseCost($user, $oProjectExpense, $expenseId) === false)
            return false;

        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            // Save task record
            $oProjectExpense->status = TaskStatus::TS_REJECTED;
            if (!$oProjectExpense->save()) {
                Yii::error('Error actualizando estado de tarea: ' . print_r($oProjectExpense->getErrors(), true), __METHOD__);
                throw new HttpException(500, 'Error al guardar la tarea');
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
    private function isUserManagerOfProject($projectId, $sessionUser) {
        assert(is_numeric($projectId));
        // If admin, then OK
        if ($sessionUser->hasDirectorPrivileges())
            return true;
        else
            return( ServiceFactory::createProjectService()->isManagerOfProject(
                            $sessionUser->id, $projectId) );
    }

    private function getDateFieldsFromExpenseSearch($expenseSearch) {
        $result = array();
        if (!empty($expenseSearch->dateIni))
            $result[] = "GREATEST(t.date_ini, '" . PHPUtils::convertStringToDBDateTime($expenseSearch->dateIni) . "')";
        else
            $result[] = 't.date_ini';
        return $result;
    }

    public function getCriteriaFromModel($model) {

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
    public function addCriteriaForProjectManagers($criteria, $userId) {
        $criteria->join = 'INNER JOIN ' . ProjectManager::tableName() . ' managers ON managers.project_id = t.project_id';
        $criteria->addColumnCondition(array('managers.user_id' => $userId));
    }

    /**
     * @param ExpenseSearch $expenseSearch
     * @return CDbCriteria
     */
    public function getCriteriaFromExpenseSearch($expenseSearch) {
        $criteria = $expenseSearch->buildCriteria();
        $criteria->with = array('worker', 'project', 'project.company');
        //$criteria->select = "*";
        $criteria->join = ' INNER JOIN ' . Project::tableName() . ' proj ON t.project_id = proj.id 
                                INNER JOIN ' . User::tableName() . ' us ON t.user_id = us.id 
                                INNER JOIN ' . Company::tableName() . ' com ON proj.company_id = com.id';

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
    public function createProjectExpense($model, $newData) {
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
                Yii::error('File format : ' . $file->getExtensionName() . " Not supported. Only PDF", __METHOD__);
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
                Yii::info('Expenses from project ' . $model->project->name . ' saved OK',__METHOD__);
                $transaction->commit();
            } else {
                Yii::error('Error saving expenses from project : ' . $model->project->name, __METHOD__);
                $transaction->rollback();
            }
        } catch (Exception $e) {
            Yii::error('Error saving Project ' . $e, __METHOD__);
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
    public function updateProjectExpense($model, array $newData) {
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

        $criteria = ProjectExpense::find();
        $criteria->innerJoin(Project::tableName().' proj','user_project_cost.project_id = proj.id')
                 ->innerJoin(User::tableName().' us','user_project_cost.user_id = us.id')
                ->innerJoin(Company::tableName().' com','proj.company_id = com.id');
        $criteria->orderBy('com.name asc, proj.name, us.name');
        $criteria->select('*, 
                         com.name as companyName,
                         proj.name as projectName,
                         us.name as workerName');
       
        if ($onlyBillable) {
            //$criteria->where("t.status = '" . TaskStatus::TS_APPROVED . "'");
        }
        if ($iCustomer != "") {
            $criteria->andWhere("proj.company_id = " . $iCustomer);
        }
        if ($iProject != "") {
            $criteria->andWhere("user_project_cost.project_id = " . $iProject);
        }

        if (!empty($sStartDate) && !empty($sEndDate)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");

            $criteria->andWhere("
                            (:start_open <= user_project_cost.date_ini) and   
                            (:end_open >= user_project_cost.date_ini)");

            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else
        if (!empty($sStartDate)) {
            $sStartDate = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $criteria->andWhere("
                            (:start_open <= user_project_cost.date_ini)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartDate);
        } else
        if (!empty($sEndDate)) {
            $sEndDate = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");
            $criteria->andWhere('user_project_cost.date_ini', '<=' . PhpUtils::convertStringToDBDateTime($sEndDate));
        }

        return $criteria->all();
    }

    const TIME_PRECISION = 4;

}

?>
