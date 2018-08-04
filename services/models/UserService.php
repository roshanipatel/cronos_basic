<?php
namespace app\services\models;

use app\models\enums\Roles;
use app\models\db\User;
use app\models\db\AuthAssignment;

/**
 * Description of AlertService
 *
 * @author twocandles
 */
class UserService {

    const MY_LOG_CATEGORY = 'services.UserService';

    /**
     * Retrieves a company a user belongs to
     */
    public function getCompanyIdFromUserId($userId) {
        //assert( User::isValidID( $userId ) );
        $user = User::find()->where(['id'=>$userId])->one();
        if ($user == null) {
            Yii::log("$userId is not an existing user", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
            throw new Exception('Non existing user', 0, null);
        }
        return $user->company_id;
    }
    
    const ORDER_NAME = 'name';

    /**
     * Get the list of workers matching the parameters.
     * @param type $orderBy
     * @param type $sStartFilter
     * @param type $sEndFilter
     * @return CActiveRecord 
     */
    public function getWorkers($orderBy = 'name', $sStartFilter = "", $sEndFilter = "", $bOnlyManagers = false) {
        
        $criteria = new CDbCriteria();
        $criteria->addCondition('t.company_id=:companyId');
        $criteria->params['companyId'] = Company::OPEN3S_ID;
        $criteria->order = "username asc";
        
        if ($bOnlyManagers) {
            $aRoleAllowed = User::getProjectOwnerPriority(Roles::UT_ADMIN);
            $sSqlFilter = "";
            foreach($aRoleAllowed as $iRole => $sDescriptionRole) {
                if ($sSqlFilter != "") {
                    $sSqlFilter .= ",";
                }
                $sSqlFilter .= "'".$iRole."'";
            }
            
            //Retrieve its role and down to them.
            if ($sSqlFilter != "") {
                $criteria->addCondition(' t.id IN ( SELECT userid FROM ' . AuthAssignment::model()->tableName() . " WHERE itemname in (".$sSqlFilter.") ) or t.id = '".Yii::app()->user->id."'");
            } else {
                $criteria->addCondition(" t.id = '".Yii::app()->user->id."'");
            }
        } else {
                $criteria->addCondition(' t.id IN ( SELECT userid FROM ' . AuthAssignment::model()->tableName() . 
                    " WHERE itemname in ('".Roles::UT_DIRECTOR_OP."', '".
                                            Roles::UT_PROJECT_MANAGER."', '".
                                            Roles::UT_COMERCIAL."', '".
                                            Roles::UT_WORKER."', '".
                                            Roles::UT_ADMIN."') ) ");
        }
        
        if (!empty($sStartFilter) && !empty($sEndFilter)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartFilter, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndFilter, "23:59");
            $criteria->addCondition("
                            (t.startcontract <= :start_open AND t.endcontract IS NULL) OR                            
                            (t.startcontract <= :end_open AND t.endcontract IS NULL) OR   
                            (t.startcontract <= :start_open AND t.endcontract >= :start_open) OR 
                            (:start_open <= t.startcontract AND t.endcontract <= :end_open) OR 
                            (:start_open <= t.startcontract AND t.startcontract <= :end_open AND t.endcontract >= :end_open) OR 
                            (t.startcontract <= :start_open AND t.endcontract >= :end_open)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else if (!empty($sStartFilter)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartFilter, "00:00");
            $criteria->addCondition("
                            (:start_open <= t.endcontract) OR 
                            (t.startcontract <= :start_open AND t.endcontract IS NULL) OR
                            (t.startcontract >= :start_open AND t.endcontract IS NULL)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
        } else if (!empty($sEndFilter)) {
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndFilter, "23:59");
            $criteria->addCondition("
                            (t.startcontract <= :end_open) OR
                            (t.endcontract <= :end_open)");
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
            
        }
        return User::model()->findAll($criteria);
    }
    
    
    /**
     * Get the administrative roles.
     * @return CActiveRecord
     */
    public function findWorkersWithProjectInTime($sStartDate, $sEndDate, $onlyBillable = false, $iCustomer = "", $sProjectName = "") {
        
        $criteria = new CDbCriteria(array(
                    'join' => ' INNER JOIN ' . UserProjectTask::model()->tableName() . ' upt ON upt.user_id = t.id 
                                INNER JOIN ' . Project::model()->tableName() . ' proj ON upt.project_id = proj.id ',
                    'order' => 'totalhours desc',
                    'select' => 't.id, t.name, 
                                -sum(round((unix_timestamp(upt.date_ini) - unix_timestamp(upt.date_end))/3600,2)) as totalhours',
                    'group' => 't.id,t.name'
                ));

        if ($onlyBillable) {
            $criteria->addCondition("upt.is_billable = 1");
        }
        if ($iCustomer != "") {
            $criteria->addCondition("proj.company_id = ".$iCustomer);
        }
        if ($sProjectName != "") {
            $criteria->addCondition("proj.name like '%".$sProjectName."%'");
        }
        
        if (!empty($sStartDate) && !empty($sEndDate)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");
            
            $criteria->addCondition("
                            (:start_open <= upt.date_ini AND :start_open <= upt.date_end) and   
                            (:end_open >= upt.date_ini AND :end_open >= upt.date_end)");
            
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else
        if (!empty($sStartDate)) {
            $sStartDate = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $criteria->addCondition("
                            (:start_open <= upt.date_ini AND :start_open <= upt.date_end)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartDate);
        } else
        if (!empty($sEndDate)) {
            $sEndDate = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");
            $criteria->compare('upt.date_end', '<=' . PhpUtils::convertStringToDBDateTime($sEndDate));
        }
        
        return User::model()->findAll($criteria);
    }

    /**
     * 
     * @param type $role
     * @param type $bCurrent
     * @param type $iProject
     * @return type
     */
    public function findUsersWithRole($role, $bCurrent = false, $iProject = "") {
        
        $sWhere = "";
        $sWhere2 = "";
        if ($bCurrent) {
            $sWhere = " and endcontract is null ";            
        }
        if (($role == Roles::UT_ADMIN || $role == Roles::UT_DIRECTOR_OP) && $iProject != "") {
            $sWhere2 = " or exists ( SELECT * FROM " .Project::TABLE_PROJECT_MANAGER. " WHERE project_id = ".$iProject." and user_id = id )";
        }
        if (($role == Roles::UT_COMERCIAL ) && $iProject != "") {
            $sWhere2 = " or exists ( SELECT * FROM " .Project::TABLE_PROJECT_COMMERCIAL. " WHERE project_id = ".$iProject." and user_id = id )";
        }
        
        return User::find()->where(' (id IN ( SELECT userid FROM ' . AuthAssignment::tableName() . ' WHERE itemname = :role ) '.$sWhere.') '.$sWhere2)->params(['role' => $role])->all();
    }
    
    /**
     * Find worker by Manager.
     * @param type $bCurrent
     * @param type $iPerson
     * @return type
     */
    public function findWorkersByManager($bCurrent = false, $iPerson = "") {
        
        $sWhere = " (( exists ( SELECT pm.* FROM " .Project::TABLE_PROJECT_MANAGER. " pm WHERE pm.user_id = ".$iPerson."  and exists (select pw.* from " .Project::TABLE_PROJECT_WORKER. " pw WHERE id = pw.user_id and pm.project_id = pw.project_id)) 
            and id IN ( SELECT userid FROM " . AuthAssignment::model()->tableName(). " WHERE   itemname = '".Roles::UT_WORKER."')) OR id = ".$iPerson." ) ";
        
        if ($bCurrent) {
            $sWhere .= " and endcontract is null ";            
        }
        
        return User::model()->findAll(array(
                    'condition' => $sWhere
                    ));
    }

    /**
     * Get the project managers
     * @param type $bCurrentProjectManager
     * @return type 
     */
    public function findProjectManagers($bCurrentProjectManager = false, $iProject = "") {
        return array_merge($this->findUsersWithRole(Roles::UT_PROJECT_MANAGER, $bCurrentProjectManager, $iProject),
            $this->findUsersWithRole(Roles::UT_DIRECTOR_OP, $bCurrentProjectManager, $iProject));
    }
    
    public function findProjectWorkers($bCurrentProjectWorkers = false, $iProject = "") {
        return array_merge(array_merge($this->findUsersWithRole(Roles::UT_WORKER, $bCurrentProjectWorkers, $iProject),
            $this->findUsersWithRole(Roles::UT_PROJECT_MANAGER, $bCurrentProjectWorkers, $iProject)),
            $this->findUsersWithRole(Roles::UT_DIRECTOR_OP, $bCurrentProjectWorkers, $iProject));
    }
    
    public function findAllWorkers($bCurrentProjectWorkers = false, $iProject = "") {
        return  array_merge(
                array_merge(
                        array_merge(
                array_merge(
                array_merge($this->findUsersWithRole(Roles::UT_WORKER, $bCurrentProjectWorkers, $iProject),
                            $this->findUsersWithRole(Roles::UT_PROJECT_MANAGER, $bCurrentProjectWorkers, $iProject)),
                $this->findUsersWithRole(Roles::UT_DIRECTOR_OP, $bCurrentProjectWorkers, $iProject)),
                $this->findUsersWithRole(Roles::UT_ADMIN, $bCurrentProjectWorkers, $iProject)),
                $this->findUsersWithRole(Roles::UT_COMERCIAL, $bCurrentProjectWorkers, $iProject)),
                $this->findUsersWithRole(Roles::UT_ADMINISTRATIVE, $bCurrentProjectWorkers, $iProject));
    }
    
    public function findProjectWorkersByManager($iProjectManager = "") {
        return $this->findUsersWithRole(Roles::UT_WORKER, $iProjectManager);
    }
    
    public function findCommercials($bCurrentCommercial = false, $iProject = "") {
        return $this->findUsersWithRole(Roles::UT_COMERCIAL, $bCurrentCommercial, $iProject);
    }

    public function findProjectCustomersByCompany($companyId) {
        return User::model()->findAllByAttributes(
                        array("company_id" => $companyId), 'id IN ( SELECT userid FROM ' . AuthAssignment::model()->tableName()
                        . ' WHERE itemname = :role )', array('role' => Roles::UT_CUSTOMER));
    }

}

?>
