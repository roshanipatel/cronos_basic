<?php
namespace app\models\form;

use yii\db\ActiveRecord;
use app\models\db\Project;
use app\models\enums\ProjectStatus;
use app\models\enums\ProjectCategories;
use app\models\User;
use app\models\enums\WorkerProfiles;
use app\models\db\Company;
use app\models\db\Imputetype;
use app\models\enums\TaskStatus;
/**
 * Model for making task searchs
 *
 * @property string $dateini
 * @property string $dateEnd
 * @property integer $projectId
 * @property array $projectIdsForSearch
 * @property string $projectStatus
 * @property projectCategoryType $projectCategoryType
 * @property integer $creator
 * @property string $profile
 * @property integer $customerCompany
 * @property string $status
 */
class TaskSearch extends ActiveRecord {
	// Search fields
	const FLD_DATE_INI = 1;
	const FLD_DATE_END = 2;
	const FLD_PROJECT_ID = 3;
	const FLD_PROJECT_STATUS = 4;
	const FLD_PROJECT_CATEGORY= 5;
	const FLD_CREATOR = 6;
	const FLD_PROFILE = 7;
	const FLD_CUSTOMER = 8;
	const FLD_STATUS = 9;
	const FLD_DESCRIPTION = 10;
	const FLD_IS_EXTRA = 11;
	const FLD_IS_BILLABLE = 12;
    const FLD_TICKET = 13;
    const FLD_OWNER = 14;
    const FLD_PROJECT_STATUS_COM = 15;
    const FLD_IMPUTE_TYPE = 16;

	public $dateIni;
	public $dateEnd;
	public $projectId;
	public $projectStatus;
    public $projectStatusCom;
    public $imputetype;
	public $projectCategoryType;
	public $creator;
    public $owner;
	public $profile;
	public $companyId;
	public $companyName;
	public $status;
	public $description;
	public $isExtra;
	public $isBillable;
	// Ordering
	public $day;
	public $sort;
	public $tickedId;
	// Used only for building criteria
	public $projectIdsForSearch;
        
    public $roleSearch;

	const DATE_FORMAT_ON_CHECK = 'dd/MM/yyyy';

	// Values for boolean search criteria
	const VALUE_ALL = -1;
	const VALUE_ONLY_SET = 1;
	const VALUE_ONLY_NOT_SET = 0;

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('dateIni', 'datetime', 'format' => self::DATE_FORMAT_ON_CHECK, 'message' => 'Formato de fecha inválido'),
			array('dateEnd', 'datetime', 'format' => self::DATE_FORMAT_ON_CHECK, 'message' => 'Formato de fecha inválido'),
			array(TaskSearch::FLD_NAME_DATE_INI.', '.TaskSearch::FLD_NAME_DATE_END.', projectId, '.TaskSearch::FLD_NAME_PROJECT_STATUS.',
                            '.TaskSearch::FLD_NAME_PROJECT_STATUS_COM.',
                            projectCategoryType, 
                            creator, profile, companyId, companyName, status, 
                            '.TaskSearch::FLD_NAME_TICKET.', 
                            '.TaskSearch::FLD_NAME_DESCRIPTION.',
                                '.TaskSearch::FLD_NAME_OWNER.',
                                    '.TaskSearch::FLD_NAME_IMPUTE_TYPE.',
                                isExtra, isBillable, sort', 'safe', 'on' => 'search'),
		);
	}
        
    const FLD_NAME_DATE_INI = 'dateIni';
    const FLD_NAME_DATE_END = 'dateEnd';
    const FLD_NAME_PROJECT_STATUS = 'projectStatus';
    const FLD_NAME_PROJECT_STATUS_COM = 'projectStatusCom';
    const FLD_NAME_IMPUTE_TYPE = 'imputetype';
    const FLD_NAME_TICKET = 'tickedId';
    const FLD_NAME_DESCRIPTION = 'description';
    const FLD_NAME_OWNER = 'owner';

	public function attributeLabels() {
		return array(
			'dateIni' => 'Fecha inicial',
			'dateEnd' => 'Fecha final',
			'projectId' => 'Proyecto',
			'projectStatus' => 'Estado proyecto',
			'projectCategoryType' => 'Categoría proyecto',
			'creator' => 'Imputador',
			'profile' => 'Perfil',
			'companyId' => 'ID Cliente',
			'companyName' => 'Cliente',
			'status' => 'Estado',
			TaskSearch::FLD_NAME_DESCRIPTION => 'Descripción',
                        TaskSearch::FLD_NAME_TICKET => 'Ticket Id',
                        TaskSearch::FLD_NAME_OWNER => 'Manager',
                        TaskSearch::FLD_NAME_IMPUTE_TYPE => 'Tipo de imputación',
			'isExtra' => 'H. Extra',
			'isBillable' => 'H. Facturable',
		);
	}
        
	/**
	 * @return CDbCriteria
	 */
	public function buildCriteria($criteria = null) {
                
                if ($criteria == null) {
                    $criteria = new \yii\db\Query();
                }
		
		$addJoinProject = false;
                
		// Fields for project: if not defined project field => all projects OK
		if((!isset($this->projectIdsForSearch) ) && ( Project::isValidID($this->projectId) )) {
			$this->projectIdsForSearch = $this->projectId;
		}
		if(isset($this->projectIdsForSearch)) {
			if(is_array($this->projectIdsForSearch) || Project::isValidID($this->projectIdsForSearch)) {
				if($this->projectIdsForSearch === array()) {
					$criteria->andFilterWhere([
		                'or',
		                ['like', 't.project_id', Project::INVALID_ID],
		            ]);
				} else {
					$criteria->andFilterWhere([
		                'or',
		                ['like', 't.project_id', $this->projectIdsForSearch],
		            ]);
					
				}
			}
		}
                
		if(ProjectStatus::isValidValue($this->projectStatus)) {
			$criteria->andFilterWhere([
		                'or',
		                ['like', 'proj.status', $this->projectStatus],
		            ]);
					
			$addJoinProject = true;
		}
        if(ProjectStatus::isValidValue($this->projectStatusCom)) {
        	$criteria->andFilterWhere([
		                'or',
		                ['like', 'proj.statuscommercial', $this->projectStatusCom],
		            ]);
			$addJoinProject = true;
		}
		if(ProjectCategories::isValidValue($this->projectCategoryType)) {
			$criteria->andFilterWhere([
		                'or',
		                ['like','proj.cat_type', $this->projectCategoryType],
		            ]);
			$addJoinProject = true;
		}

		// Fields for dates
		if(!empty($this->dateIni)) {
			$this->dateIni = PHPUtils::addHourToDateIfNotPresent($this->dateIni, "00:00");
			$criteria->andWhere('date_end', '>=' . PHPUtils::convertStringToDBDateTime($this->dateIni));
		}
		if(!empty($this->dateEnd)) {
			$this->dateEnd = PHPUtils::addHourToDateIfNotPresent($this->dateEnd, "23:59");
			$criteria->andWhere('date_ini', '<=' . PHPUtils::convertStringToDBDateTime($this->dateEnd));
		}

		// Fields for workers
		if(User::isValidID($this->creator)) $criteria->compare('t.user_id', $this->creator);
		else if(WorkerProfiles::isValidValue($this->profile)) {
			$criteria->compare('t.profile_id', $this->profile);
		}
                
                // Fields for owners
		if(User::isValidID($this->owner)) {
                    if ("" == $this->owner && $this->roleSearch == Roles::UT_PROJECT_MANAGER ) {
                        $criteria->where("( t.project_id in (select project_id from ".Project::TABLE_PROJECT_MANAGER." where user_id = '".$this->owner."' ) OR  "
                                . " t.user_id = ".$this->owner.") ");
                    } else if(\Yii::$app->user->id != $this->owner && $this->roleSearch == Roles::UT_PROJECT_MANAGER) {
                        $criteria->where("( t.project_id in (select project_id from ".Project::TABLE_PROJECT_MANAGER." where user_id = '".$this->owner."' ) AND  "
                                . " t.user_id = ".Yii::$app->user->id.") ");
                    } else {
                        $criteria->where("( t.project_id in (select project_id from ".Project::TABLE_PROJECT_MANAGER." where user_id = '".$this->owner."' )) ");
                    }
                }
                
                
		// Fields for customers
		if(Company::isValidID($this->companyId)) {
			$criteria->andFilterWhere([
                'or',
                ['like', 'proj.company_id', $this->companyId],
            ]);
			$addJoinProject = true;
		}

        if(Imputetype::isValidID($this->imputetype)) {

            $criteria->andWhere("t.imputetype_id in (".implode(",", $this->imputetype).") ");
        }
                
		// Fields for status
		if(TaskStatus::isValidValue($this->status)) {
			$criteria->andFilterWhere([
                'or',
                ['like', 't.status', $this->status],
            ]);
			
		}
                // Fields for ticketId
		if(!empty($this->tickedId)) {
			$criteria->andFilterWhere([
                'or',
                ['like', 't.ticket_id', $this->tickedId],
            ]);
			
		}
		// Field for description
		if(!empty($this->description)) {
			$criteria->andFilterWhere([
                'or',
                ['like', 't.task_description', $this->description],
            ]);
			
		}
		// Field for extra
		if(isset($this->isExtra) && $this->isExtra != self::VALUE_ALL){
			$criteria->andFilterWhere([
                'or',
                ['like', 't.is_extra', $this->isExtra],
            ]);
			
		}
		// Field for billable
		if(isset($this->isBillable) && $this->isBillable != self::VALUE_ALL){
			$criteria->andFilterWhere([
                'or',
                ['like', 't.is_billable', $this->isBillable],
            ]);
		}

                if($addJoinProject) {
                    $criteria->join = ' INNER JOIN ' . Project::tableName() . ' proj ON t.project_id = proj.id ';
		}
                
		if(empty($this->sort)) {
			$criteria->orderBy = 't.id desc';
		}
		return $criteria;
	}

	public static function getDropdownForFlags() {
		return array(
			self::VALUE_ONLY_SET => 'Activado',
			self::VALUE_ONLY_NOT_SET => 'Desactivado',
		);
	}
}

?>
