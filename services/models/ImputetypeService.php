<?php
namespace app\services\models;
use app\models\db\Imputetype;
/**
 * Description of AlertService
 *
 * @author twocandles
 */
class ImputetypeService {

    const MY_LOG_CATEGORY = 'services.ImputetypeService';
    
    /**
     * 
     * @param type $role
     * @param type $bCurrent
     * @param type $iProject
     * @return type
     */
    private function findImputetypesFilter($iProject = "") {
        $criteria = new \yii\db\Query();
        if ($iProject != "") {
            $criteria->addCondition(" exists (select * from " . Project::model()->tableName() . " proj,"
                    . " " . ProjectImputetype::model()->tableName() . " projimpute "
                    . " where :projectId = proj.id and projimpute.project_id = proj.id and t.id = projimpute.imputetype_id ) ");
            
            $criteria->params['projectId'] = $iProject;
        }
        return Imputetype::find($criteria)->all();
    }
    
    public function findImputetypes($iProject = "") {
        return $this->findImputetypesFilter($iProject);
    }
    
    public function findImputetypesFromDropdown($iProject = "", $imputetype_id = "") {
        $models = $this->findImputetypesFilter($iProject);
        $result = array();
        foreach ($models as $imputetype) {
            $result[$imputetype->id] = $imputetype->name;
        }
        
        if ($imputetype_id != "") {
            $oImputeType = Imputetype::model()->findByPk($imputetype_id);
            if (!isset($result[$imputetype_id])) {
                $result[$imputetype_id] = $oImputeType->name;
            }
        }
        return $result;
    }
}

?>
