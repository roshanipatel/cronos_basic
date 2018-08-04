<?php
namespace app\models\db;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "project".
 *
 * The followings are the available columns in table 'project':
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property integer $company_id
 * @property string $status
 * @property string $cat_type
 * @property string $open_time
 * @property string $close_time
 * @property integer $fixed_time
 * @property integer $fix_time_hour_ini
 * @property string $fix_time_hour_end
 * @property float $max_hours
 * @property float $hours_warn_threshold
 * @property integer $commercial
 * @property string $statuscommercial;
 * @property string $reporting;
 * @property string $reportingtargetcustom;
 *
 * The followings are the available model relations:
 * @property WorkerProfiles[] $workerProfiles
 * @property Company $company
 * @property string $company_name
 * @property User[] $users
 * @property UserProjectTask[] $userProjectTasks
 * @property Role[] $reportingtarget
 */
class Project extends ActiveRecord {

    const MY_LOG_CATEGORY = 'models.Project';
    const INVALID_ID = -1;
    
    const VACACIONES_ID = 'Vacaciones';

    private $onFindStatus;
    // For searching
    public $company_name;
    public $manager_id;
    public $manager_custom;
    public $commercial_custom;
    public $company_custom;
    public $totalSeconds;
    public $taskCount;
    public $executed;
    public $category_name;
    public $totalhours;
    public $imputetypeName;
    public $imputetype;

    

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return Project::TABLE_PROJECT;
    }

    const TABLE_PROJECT = 'project';
    const TABLE_PROJECT_MANAGER = "project_manager";
    const TABLE_PROJECT_CUSTOMER = "project_customer";
    const TABLE_PROJECT_WORKER = "project_worker";
    const TABLE_PROJECT_COMMERCIAL = "project_commercial";
    const TABLE_PROJECT_IMPUTETYPE = "project_imputetype";
    const TABLE_PROJECT_REPORTINGTARGET = "project_reporting";

    /**
     * @return array
     */
    public function scopes() {
        return array(
            'open' => array(
                'condition' => "t.status = '" . ProjectStatus::PS_OPEN . "'",
            ),
        );
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, company_id', 'required'),
            array('company_id, fixed_time, fix_time_hour_ini', 'numerical', 'integerOnly' => true),
            array('max_hours, hours_warn_threshold', 'numerical'),
            array('company_id', 'exist', 'className' => 'Company', 'attributeName' => 'id'),
            array('manager_id', 'exist', 'className' => 'User', 'attributeName' => 'id'),
            array('commercial', 'exist', 'className' => 'User', 'attributeName' => 'id'),
            array('id', 'exist', 'className' => 'Project', 'attributeName' => 'id'),
            array('status', 'in', 'range' => ProjectStatus::getValidValues()),
            array('statuscommercial', 'in', 'range' => ProjectStatus::getValidValues()),
            array('cat_type', 'in', 'range' => ProjectCategories::getValidValues()),
            array('reporting', 'in', 'range' => ReportingFreq::getValidValues()),
            array('code, name, status', 'length', 'max' => 45),
            array('fix_time_hour_end', 'safe'),
            array('open_time, close_time', 'safe'),
            array('reportingtarget, managers, customers, workers, commercials, imputetypes, reportingtargetcustom', 'safe'),
            // Check project name unique for customer
            array('name', 'checkProjectNameUniqueForCustomer'),
            // Check status if hours pending
            array('status', 'checkHoursPendingToApprove', 'message' => 'No se puede cerrar el proyecto operativo. Todavía quedan horas pendientes de aprobar.'),
            array('statuscommercial', 'checkProjectIsOperationalClosed', 'message' => 'No se puede cerrar el proyecto comercial. Todavía quedan horas pendientes de aprobar.'),
            // Check max_hours, hours_warn_threshold to check semantics
            array('max_hours', 'checkMaxHours', 'message' => 'Número de horas inválido'),
            array('hours_warn_threshold', 'checkHourWarnThreshold', 'message' => 'Número de horas inválido (debe ser menor que el máximo de horas)'),
            array('cat_type', 'safe'),
            // Declare it unsafe to not massively assign it!!
            array('workerProfiles', 'unsafe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, code, name, status, manager_custom, commercial_custom, company_custom, totalSeconds, company_name, open_time, close_time, manager_id, imputetype', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'workerProfiles' => array(self::HAS_MANY, 'PricePerProjectAndProfile', 'project_id'),
            'company' => array(self::BELONGS_TO, 'Company', 'company_id'),
            'commercial' => array(self::BELONGS_TO, 'User', 'id'),
            'managers' => array(self::MANY_MANY, 'User', Project::TABLE_PROJECT_MANAGER . '(project_id, user_id)'),
            'customers' => array(self::MANY_MANY, 'User', Project::TABLE_PROJECT_CUSTOMER . '(project_id, user_id)'),
            'workers' => array(self::MANY_MANY, 'User', Project::TABLE_PROJECT_WORKER . '(project_id, user_id)'),
            'commercials' => array(self::MANY_MANY, 'User', Project::TABLE_PROJECT_COMMERCIAL . '(project_id, user_id)'),
            'imputetypes' => array(self::MANY_MANY, 'Imputetype', Project::TABLE_PROJECT_IMPUTETYPE . '(project_id, imputetype_id)'),
            'reportingtarget' => array(self::MANY_MANY, 'Role', Project::TABLE_PROJECT_REPORTINGTARGET . '(project_id, role_id)')            
        );
    }

    const PROJECT_LABEL_COMMERCIAL = 'commercial';

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'code' => 'Código interno',
            'name' => 'Nombre',
            'company_id' => 'Cliente',
            'company' => 'Cliente',
            'managers' => 'Jefes de proyecto',
            'customers' => 'Clientes con acceso',
            'workers' => 'Trabajadores con acceso',
            'commercials' => 'Comerciales con acceso',
            'imputetypes' => 'Tipos de imputación',
            'status' => 'Estado operativo',
            'cat_type' => 'Categoría',
            'open_time' => 'Fecha apertura',
            'close_time' => 'Fecha cierre',
            'statuscommercial' => 'Estado comercial',
            'fixed_time' => 'Tiempo fijado',
            'fix_time_hour_ini' => 'Inicio hora tiempo fijado',
            'fix_time_hour_end' => 'Final hora tiempo fijado',
            'max_hours' => 'Máximo horas',
            'hours_warn_threshold' => 'Umbral de aviso en horas',
            Project::PROJECT_LABEL_COMMERCIAL => 'Comercial',
            'category_name' => "Categoría",
            'reportingtarget' => "Destinatario Rol",
            'reportingtargetcustom' => "Destinatario libre"
        );
    }

    /**
     * Check the project is operationally closed.
     * @param type $att
     * @param type $params
     * @return boolean
     */
    public function checkProjectIsOperationalClosed($att, $params) {
        // If new status is not closed, return
        
        if ($this->statuscommercial == ProjectStatus::PS_CLOSED && ServiceFactory::createUserProjectTaskService()->hasProjectHoursToApprove($this->id)) {
            $this->addError('status', 'No se puede cerrar el proyecto. Todavía quedan horas pendientes de aprobar');
            return false;
        }
        
        if ($this->status == ProjectStatus::PS_OPEN && $this->statuscommercial == ProjectStatus::PS_CLOSED) {
            $this->addError('statuscommercial', 'No se puede cerrar el proyecto. El proyecto tiene que estar cerrado a nivel operacional.');
            return false;
        }
        return true;
    }

    public function checkHoursPendingToApprove($att, $params) {
        // If record not retrieved from DB or not set status change fields, return
        if (empty($this->onFindStatus) || ( empty($this->id) )) {
            return true;
        }
        
        // If status not changed, return
        if ($this->onFindStatus == $this->status) {
            return true;
        }
        // If new status is not closed, return
        if ($this->status != ProjectStatus::PS_CLOSED) {
            return true;
        }
        // New status is CLOSED and previous status was NOT CLOSED. Let's check
        // if there're pending hours to approve
        //if (ServiceFactory::createUserProjectTaskService()->hasProjectHoursToApprove($this->id)) {
        //    $this->addError('status', 'No se puede cerrar el proyecto. Todavía quedan horas pendientes de aprobar');
        //    return false;
        //} else {
            return true;
        //}
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {

        $sWhere = "";
        if (isset($this->imputetype)) {
            $sWhere = " AND ( FALSE ";
            foreach($this->imputetype as $imputetype) {
                $sWhere .= " OR user_project_task.imputetype_id = ".$imputetype." ";
            }
            $sWhere .= " ) ";
        }
        $criteria = new CDbCriteria();
        $criteria = ServiceFactory::createProjectService()->getCriteriaFromModel($this);
        $criteria->order = "";
        $criteria->select = "*, 
                    ( select user.name from " . Project::TABLE_PROJECT_MANAGER . " inner join " . User::TABLE_USER . " on user.id = user_id where project_id = t.id order by user.name limit 1 ) as manager_custom,
                    ( select user.name from " . Project::TABLE_PROJECT_COMMERCIAL . " inner join " . User::TABLE_USER . " on user.id = user_id where project_id = t.id order by user.name limit 1 ) as commercial_custom,
                    ( select company.name from " . Company::TABLE_COMPANY . " where t.company_id = company.id order by company.name limit 1 ) as company_custom,
                    ( select roundResult(sum( unix_timestamp(date_end) - unix_timestamp(date_ini) ) / 3600) from user_project_task where t.id = user_project_task.project_id ".$sWhere." ) as totalSeconds, 
                    ( select count(*) from user_project_task where t.id = user_project_task.project_id ".$sWhere.") as taskCount,
                    ( select roundResult( ( roundResult(sum( unix_timestamp(date_end) - unix_timestamp(date_ini) ) / 3600) / resultExist(max_hours) ) * 100) from user_project_task where t.id = user_project_task.project_id ".$sWhere.") as executed,
                    ( select description from project_category where name = t.cat_type ) as category_name ";
        
        return new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                    'pagination' => array(
                        'pageSize' => Yii::$app->params->default_page_size,
                    ),
                    'sort' => $this->getSort(),
                ));
    }

    /**
     * @return CSort
     */
    private function getSort() {
        $sort = new CSort();
        $sort->attributes = array(
            'company_custom' => array(
                'asc' => 'company_custom ASC',
                'desc' => 'company_custom DESC',
            ),
            'name' => array(
                'asc' => 't.name ASC',
                'desc' => 't.name DESC',
            ),
            'open_time' => array(
                'asc' => 't.open_time ASC',
                'desc' => 't.open_time DESC',
            ),
            'close_time' => array(
                'asc' => 't.open_time ASC',
                'desc' => 't.open_time DESC',
            ),
            'status' => array(
                'asc' => 't.status ASC',
                'desc' => 't.status DESC'
            ),
            'statuscommercial' => array(
                'asc' => 't.statuscommercial ASC',
                'desc' => 't.statuscommercial DESC'
            ),
            'manager_custom' => array(
                'asc' => 'manager_custom ASC',
                'desc' => 'manager_custom DESC'
            ),
            'commercial_custom' => array(
                'asc' => 'commercial_custom ASC',
                'desc' => 'commercial_custom DESC'
            ),
            'totalSeconds' => array(
                'asc' => 'totalSeconds ASC',
                'desc' => 'totalSeconds DESC'
            ),
            'executed' => array(
                'asc' => 'executed ASC',
                'desc' => 'executed DESC'
            ),
            'category_name' => array(
                'asc' => 'cat_type ASC',
                'desc' => 'cat_type DESC'
            )
        );
        return $sort;
    }

    public function hasTasks() {
        return $this->taskCount > 0;
    }
    
    public function hasReport() {
        return $this->reporting != ReportingFreq::FREQ_NONE; 
    }

    /**
     * Returns if the parameter is a valid project ID
     */
    static public function isValidID($id) {
        return is_numeric($id) && ( ((int) $id) > 0 );
    }

    protected function beforeSave() {

        if (!empty($this->open_time)) {
            $this->open_time = PHPUtils::convertStringToDBDateTime($this->open_time);
        }
        $this->open_time = PHPUtils::addHourToDateIfNotPresent($this->open_time, "00:00");

        if ($this->close_time != "") {
            $this->close_time = PHPUtils::convertStringToDBDateTime($this->close_time);
            $this->close_time = PHPUtils::addHourToDateIfNotPresent($this->close_time, "00:00");
        } else {
            $this->close_time = null;
        }
        
        return parent::beforeSave();
    }

    protected function afterFind() {
        if ($this->open_time != null) {
            $this->open_time = PHPUtils::convertDBDateTimeToString($this->open_time);
        }
        
        if ($this->close_time != null) {
            $this->close_time = PHPUtils::convertDBDateTimeToString($this->close_time);
        }
        $this->onFindStatus = $this->status;
        return parent::afterFind();
    }

    public function checkMaxHours($att, $params) {
        if (empty($this->max_hours)) {
            $this->max_hours = 0;
        }
        $res = ((float) $this->max_hours) >= 0;
        if (!$res) {
            $this->addError('max_hours', 'Número máximo de horas incorrecto');
        }
        return $res;
    }

    public function checkHourWarnThreshold($att, $params) {
        if ($this->hasErrors('max_hours')) {
            return false;
        }
        if (empty($this->hours_warn_threshold)) {
            $this->hours_warn_threshold = 0;
        }
        $val = (float) $this->hours_warn_threshold;
        $res = $val >= 0 && $val <= $this->max_hours;
        if (!$res) {
            $this->addError('hours_warn_threshold', 'Número de umbral de horas incorrecto. Deber ser menor que el máximo de horas');
        }
        return $res;
    }

    public function getTotalHours() {
        return $this->totalSeconds;
    }

    public function getPercentageExecuted() {
        $iMaxHours = $this->max_hours;
        if ($this->max_hours == 0) {
            $iMaxHours = 1;
        }
        return number_format((($this->totalSeconds) / $iMaxHours) * 100, 2);
    }

    public function checkProjectNameUniqueForCustomer($att, $params) {
        if (empty($this->company_id) || (empty($this->name))) {
            return false;
        }
        $id = empty($this->id) ? -1 : (int) $this->id;
        $projects = Project::model()->findAllByAttributes(array(
            'name' => $this->name,
            'company_id' => $this->company_id,
                ),
                // The project must be different than itself!!
                'id != :id', array(
            'id' => $id
                )
        );
        $res = count($projects) == 0;
        if (!$res) {
            $this->addError('name', 'Ya existe un proyecto con este nombre para este cliente');
        }
        return $res;
    }

}