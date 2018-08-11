<?php
namespace app\services\models;

use app\models\db\Company;
use app\models\db\Project;
use app\models\db\UserProjectTask;
use app\components\utils\PHPUtils;
use app\services\CronosService;
/**
 * Description of ProjectService
 *
 * @author twocandles
 */
class CompanyService implements CronosService
{
    const MY_LOG_CATEGORY = 'services.CompanyService';

    /**
     *
     * @param string $substr
     * @return array[Company]
     */
    public function getCompaniesBySubstring( $substr )
    {
        $criteria = new yii\db\Query();
        $criteria->addSearchCondition('name', $substr);
        $criteria->order = 'id desc';
        return Company::findAll($criteria);
    }
    
    
    public function findCustomerForDropdown($companyId, CronosUser $sessionUser) {
        assert(is_numeric($companyId));
        // Load project to get it's company
        $company = Company::findOne($companyId);
        if (empty($company)) {
            Yii::log("Company $companyId not found", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
            return array();
        }
        // Build search criteria depending on the user
        $criteria = new CDbCriteria;
        $criteria->order = 't.name asc';
        $models = Company::findAll($criteria);
        $result = array();
        foreach ($models as $company)
            $result[$company->id] = $company->name;
        return $result;
    }
    
    /**
     * Get the administrative roles.
     * @return CActiveRecord
     */
    public function findCompaniesWithProjectInTime($sStartDate, $sEndDate, $worker = "", $bOrderAsc = false) {
            
        $sOrder = "";
        if (!$bOrderAsc) {
            $sOrder = "totalhours desc";
        } else {
            $sOrder = "t.name asc ";
        }
        
        $criteria = new CDbCriteria(array(
                    'join' => ' INNER JOIN ' . Project::tableName() . ' proj ON proj.company_id = t.id 
                                INNER JOIN ' . UserProjectTask::tableName() . ' upt ON upt.project_id = proj.id ',
                    'order' => $sOrder,
                    'select' => 't.name, 
                                 t.id,
                                -sum(round((unix_timestamp(upt.date_ini) - unix_timestamp(upt.date_end))/3600,2)) as totalhours',
                    'group' => 't.name, t.id'
                ));

        if ($worker != "") {
            $criteria->addCondition(" upt.user_id = ".$worker);
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
        
        return Company::findAll($criteria);
    }
}
?>
