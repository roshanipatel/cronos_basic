<?php
namespace app\services\models;

use app\models\enums\Roles;
use app\models\db\User;
use app\models\db\AuthAssignment;
use app\models\db\UserProjectTask;
use app\models\db\Project;
use app\components\utils\PHPUtils;
use app\models\db\Company;
use app\models\enums\TaskStatus;

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
            Yii::error("$userId is not an existing user", __METHOD__);
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
        
        $criteria = User::find();
        $criteria->where('user.company_id=:companyId');
        $criteria->params['companyId'] = Company::OPEN3S_ID;
        $criteria->orderBy("username asc");
        
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
                $criteria->andWhere(' user.id IN ( SELECT userid FROM ' . AuthAssignment::tableName() . " WHERE itemname in (".$sSqlFilter.") ) or user.id = '".\Yii::$app->user->id."'");
            } else {
                $criteria->andWhere(" user.id = '".Yii::$app->user->id."'");
            }
        } else {
                $criteria->andWhere(' user.id IN ( SELECT userid FROM ' . AuthAssignment::tableName() . 
                    " WHERE itemname in ('".Roles::UT_DIRECTOR_OP."', '".
                                            Roles::UT_PROJECT_MANAGER."', '".
                                            Roles::UT_COMERCIAL."', '".
                                            Roles::UT_WORKER."', '".
                                            Roles::UT_ADMIN."') ) ");
        }
        
        if (!empty($sStartFilter) && !empty($sEndFilter)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartFilter, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndFilter, "23:59");
            $criteria->andWhere("
                            (user.startcontract <= :start_open AND user.endcontract IS NULL) OR                            
                            (user.startcontract <= :end_open AND user.endcontract IS NULL) OR   
                            (user.startcontract <= :start_open AND user.endcontract >= :start_open) OR 
                            (:start_open <= user.startcontract AND user.endcontract <= :end_open) OR 
                            (:start_open <= user.startcontract AND user.startcontract <= :end_open AND user.endcontract >= :end_open) OR 
                            (user.startcontract <= :start_open AND user.endcontract >= :end_open)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else if (!empty($sStartFilter)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartFilter, "00:00");
            $criteria->andWhere("
                            (:start_open <= user.endcontract) OR 
                            (user.startcontract <= :start_open AND user.endcontract IS NULL) OR
                            (user.startcontract >= :start_open AND user.endcontract IS NULL)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
        } else if (!empty($sEndFilter)) {
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndFilter, "23:59");
            $criteria->andWhere("
                            (user.startcontract <= :end_open) OR
                            (user.endcontract <= :end_open)");
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
            
        }
        return $criteria->all();
    }
    
    
    /**
     * Get the administrative roles.
     * @return CActiveRecord
     */
    public function findWorkersWithProjectInTime($sStartDate, $sEndDate, $onlyBillable = false, $iCustomer = "", $sProjectName = "") {
        $query =  User::find();
        $query->innerJoin(UserProjectTask::tableName().' upt','upt.user_id = user.id')
              ->innerJoin(Project::tableName().' proj','upt.project_id = proj.id');
        
        $query->select(['user.id', 'user.name', 
                                '-sum(round((unix_timestamp(upt.date_ini) - unix_timestamp(upt.date_end))/3600,2)) as totalhours']);
        $query->groupBy('user.id,user.name') ;
        $query->orderBy('totalhours desc');                                           
        

        if ($onlyBillable) {
            $query->where("upt.is_billable = 1");
        }
        if ($iCustomer != "") {
            $query->where("proj.company_id = ".$iCustomer);
        }
        if ($sProjectName != "") {
            $query->where("proj.name like '%".$sProjectName."%'");
        }
        
        if (!empty($sStartDate) && !empty($sEndDate)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");
            
            $query->where("
                            (:start_open <= upt.date_ini AND :start_open <= upt.date_end) and   
                            (:end_open >= upt.date_ini AND :end_open >= upt.date_end)");
            
            $query->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $query->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else
        if (!empty($sStartDate)) {
            $sStartDate = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $query->where("
                            (:start_open <= upt.date_ini AND :start_open <= upt.date_end)");
            $query->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartDate);
        } else
        if (!empty($sEndDate)) {
            $sEndDate = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");
            $query->where('upt.date_end', '<=' . PhpUtils::convertStringToDBDateTime($sEndDate));
        }
        
        return $query->all();
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
            and id IN ( SELECT userid FROM " . AuthAssignment::tableName(). " WHERE   itemname = '".Roles::UT_WORKER."')) OR id = ".$iPerson." ) ";
        
        if ($bCurrent) {
            $sWhere .= " and endcontract is null ";            
        }
        
        return User::findAll(array(
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
        return User::findAll(
                        array("company_id" => $companyId), 'id IN ( SELECT userid FROM ' . AuthAssignment::tableName()
                        . ' WHERE itemname = :role )', array('role' => Roles::UT_CUSTOMER));
    }

}

?>
