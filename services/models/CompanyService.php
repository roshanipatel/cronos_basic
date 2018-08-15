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
        $criteria = Company::find();
        $criteria->addSearchCondition('name', $substr);
        $criteria->orderBy = 'id desc';
        return $criteria->all();
    }
    
    
    public function findCustomerForDropdown($companyId, CronosUser $sessionUser) {
        assert(is_numeric($companyId));
        // Load project to get it's company
        $company = Company::findOne($companyId);
        if (empty($company)) {
            Yii::error("Company $companyId not found", __METHOD__);
            return array();
        }
        // Build search criteria depending on the user
        $criteria = Company::find();
        $criteria->orderBy = 't.name asc';
        $models = $criteria->all();
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
        $criteria = Company::find();
        $criteria->innerJoin(Project::tableName().' proj','company.id = proj.company_id')
                 ->innerJoin(UserProjectTask::tableName() . ' upt','upt.project_id = proj.id');
        $criteria->orderBy($sOrder);
        $criteria->select(['company.name', 
                                 'company.id',
                                '-sum(round((unix_timestamp(upt.date_ini) - unix_timestamp(upt.date_end))/3600,2)) as totalhours']);
        $criteria->groupBy('company.name, company.id');

        if ($worker != "") {
            $criteria->andWhere(" upt.user_id = ".$worker);
        }
        
        if (!empty($sStartDate) && !empty($sEndDate)) {
            $sStartFilter = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $sEndFilter = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");

            $criteria->andWhere("
                            (:start_open <= upt.date_ini AND :start_open <= upt.date_end) and   
                            (:end_open >= upt.date_ini AND :end_open >= upt.date_end)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartFilter);
            $criteria->params[':end_open'] = PhpUtils::convertStringToDBDateTime($sEndFilter);
        } else
        if (!empty($sStartDate)) {
            $sStartDate = PHPUtils::addHourToDateIfNotPresent($sStartDate, "00:00");
            $criteria->andWhere("
                            (:start_open <= upt.date_ini AND :start_open <= upt.date_end)");
            $criteria->params[':start_open'] = PhpUtils::convertStringToDBDateTime($sStartDate);
        } else
        if (!empty($sEndDate)) {
            $sEndDate = PHPUtils::addHourToDateIfNotPresent($sEndDate, "23:59");
            $criteria->andWhere('upt.date_end', '<=' . PhpUtils::convertStringToDBDateTime($sEndDate));
        }
        
        return $criteria->all();
    }
}
?>
