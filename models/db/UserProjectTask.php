<?php
namespace app\models\db;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_project_task".
 *
 * The followings are the available columns in table 'user_project_task':
 * @property integer $id
 * @property integer $user_id
 * @property integer $project_id
 * @property integer $imputetype_id
 * @property string $status
 * @property string $date_ini;
 * @property string $date_end;
 * @property string $hour_ini;
 * @property string $hour_end;
 * @property string $frm_customer_name
 * @property string $task_description
 * @property string $ticket_id
 * @property string $price_per_hour
 * @property string $profile_id
 * @property bool $is_extra
 * @property bool $is_billable
 * @property string $companyName
 * @property string $projectName
 * @property string $workerName
 * @property string $managerName
 * @property string $workerCost
 * @property string $imputetypeName
 *
 * The followings are the available model relations:
 * @property TaskHistory[] $taskHistories
 * @property User $worker
 * @property WorkerProfiles $profile
 * @property Project $project
 * @property Imputetype $imputetype
 */
class UserProjectTask extends ActiveRecord {

    public $totalhours;
    public $projectManager;
    public $firstUserProjectTask;
    public $lastUserProjectTask;
    
    const MY_LOG_CATEGORY = 'models.UserProjectTask';
    const DATE_FORMAT_ON_CHECK = 'dd/MM/yyyy';
    const TIME_FORMAT_ON_CHECK = 'HH:mm';
    const DATE_TIME_FORMAT_ON_CONVERSION = 'd/m/Y H:i';
    //const ONLY_DATE_ON_CONVERSION = 'd/m/Y';
    //const ONLY_TIME_ON_CONVERSION = 'H:i';
    const SCENARIO_COST_SEARCH = 'SCN_COST_SEARCH';
    const SCENARIO_NO_VALIDATION = 'SCN_NO_VALIDATION';

    // Form transitional fields
    public $frm_date_ini;
    public $frm_date_end;
    public $frm_hour_ini;
    public $frm_hour_end;
    public $frm_customer_name;
    // Internal for detecting profile change
    private $oldTaskProfile;
    // For custom searchs
    public $custom1;
    public $custom2;
    
    // For searching
    public $companyName;
    public $projectName;
    public $workerName;
    public $managerName;
    public $workerCost;
    public $imputetypeName;
    protected static $table;


    /**
     * Returns the static model of the specified AR class.
     * @return UserProjectTask the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public static  function tableName() {
        return 'user_project_task';
    }
    
    const TABLE_USER_PROJECT_TASK = 'user_project_task';

    public function init() {
        if ($this->scenario == self::SCENARIO_NO_VALIDATION) {
            $this->date_ini = new DateTime;
            $this->date_end = new DateTime;
        }
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, project_id, imputetype_id, status, profile_id, frm_date_ini, frm_hour_ini, frm_date_end, frm_hour_end', 'required'),
            array('user_id, project_id, imputetype_id, ticket_id', 'numerical', 'integerOnly' => true),
            array('user_id', 'exist', 'className' => 'User', 'attributeName' => 'id'),
            array('project_id', 'exist', 'className' => 'Project', 'attributeName' => 'id'),
            array('imputetype_id', 'exist', 'className' => 'Imputetype', 'attributeName' => 'id'),
            array('frm_customer_name', 'exist', 'className' => 'Company', 'attributeName' => 'name'),
            array('status', 'in', 'range' => TaskStatus::getValidValues()),
            array('profile_id', 'in', 'range' => WorkerProfiles::getValidValues()),
            array('frm_date_ini, frm_date_end', 'type', 'type' => 'datetime', 'datetimeFormat' => self::DATE_FORMAT_ON_CHECK, 'message' => 'Formato de fecha inválida'),
            array('frm_hour_ini, frm_hour_end', 'type', 'type' => 'datetime', 'datetimeFormat' => self::TIME_FORMAT_ON_CHECK, 'message' => 'Formato de hora inválida'),
            array('frm_date_ini', 'checkHourSemantics', 'message' => 'La hora final no puede ser anterior a la inicial'),
            array('frm_date_ini', 'checkHourIsNotLateFromNow', 'message' => 'Las horas que está imputando son demasiado antiguas.'),
            array('task_description', 'length', 'max' => 1024),
            array('ticket_id', 'length', 'max' => 128),
            array('is_extra, is_billable', 'boolean'),
                // The following rule is used by search().
                // Please remove those attributes that should not be searched.
                //array( 'status, frm_date_ini, task_description, ticket_id', 'safe', 'on' => 'search' ),
        );
    }

    /**
     * Check that the current time is not greater than the time allowed to the user to impute old hours.
     * @param type $att
     * @param type $params
     * @return boolean
     */
    public function checkHourIsNotLateFromNow($att, $params) {
        if ($this->hasErrors())
            return false;
        // Convert to timestamps
        $ts_ini = PHPUtils::convertStringToPHPDateTime($this->frm_date_ini . ' ' . $this->frm_hour_ini);
        if (!$ts_ini) {
            $this->addError('frm_date_ini', 'Formato de hora inválido');
            $this->addError('frm_hour_ini', 'Formato de hora inválido');
            return false;
        }
        $ts_end = PHPUtils::convertStringToPHPDateTime($this->frm_date_end . ' ' . $this->frm_hour_end);
        if (!$ts_end) {
            $this->addError('frm_date_end', 'Formato de hora inválido');
            $this->addError('frm_hour_end', 'Formato de hora inválido');
            return false;
        }
        
        return true;
    }

    public function checkHourSemantics($att, $params) {
        if ($this->hasErrors())
            return false;
        // Convert to timestamps
        $ts_ini = PHPUtils::convertStringToPHPDateTime($this->frm_date_ini . ' ' . $this->frm_hour_ini);
        if (!$ts_ini) {
            $this->addError('frm_date_ini', 'Formato de hora inválido');
            $this->addError('frm_hour_ini', 'Formato de hora inválido');
            return false;
        }
        $ts_end = PHPUtils::convertStringToPHPDateTime($this->frm_date_end . ' ' . $this->frm_hour_end);
        if (!$ts_end) {
            $this->addError('frm_date_end', 'Formato de hora inválido');
            $this->addError('frm_hour_end', 'Formato de hora inválido');
            return false;
        }
        // Check semantics: is end hour after ini hour?
        if ($ts_end <= $ts_ini) {
            $this->addError('frm_date_end', 'Intervalo de horas inválido');
            return false;
        }
        // Set datetimes
        $this->date_ini = $ts_ini;
        $this->date_end = $ts_end;
        // Check if hours are already used by another task
        $upts = ServiceFactory::createUserProjectTaskService();
        if (empty($this->id))
            $taskId = -1;
        else
            $taskId = $this->id;
        // Check if range in conflict with existing
        if ($upts->isRangeInConflict($this->user_id, $this->date_ini, $this->date_end, $taskId)) {
            $this->addError('frm_hour_ini', 'Conflicto de horas con otra tarea');
            return false;
        }
        // Everything ok!
        return true;
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'worker' => array(self::BELONGS_TO, 'User', 'user_id', 'select' => 'worker.name'),
            'project' => array(self::BELONGS_TO, 'Project', 'project_id', 'select' => 'project.name, project.company_id, project.status'),
            'imputetype' => array(self::BELONGS_TO, 'Imputetype', 'imputetype_id', 'select' => 'imputetype.name'),
            'taskHistories' => array(self::HAS_MANY, 'TaskHistory', 'user_project_task_id'),
                //'profile' => array( self::BELONGS_TO, 'WorkerProfiles', 'profile_id' ),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'user_id' => 'Usuario',
            'project_id' => 'Proyecto',
            'imputetype_id' => 'Tipo de imputación',
            'status' => 'Estado',
            'date_ini' => 'Fecha inicial',
            'date_end' => 'Fecha final',
            'date' => 'Fecha',
            'frm_date_ini' => 'Hora inicial',
            'frm_hour_ini' => 'Hora inicial',
            'frm_date_end' => 'Hora final',
            'frm_hour_end' => 'Hora final',
            //'frm_duration_hours' => 'Duración',
            'frm_customer_name' => 'Cliente',
            'task_description' => 'Descripción',
            'ticket_id' => 'Ticket ID',
            'profile_id' => 'Perfil',
            'price_per_hour' => 'Precio por hora',
            'is_extra' => 'Hora extra',
            'is_billable' => 'Hora facturable',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        $criteria = new CDbCriteria(array(
                    'with' => array('worker', 'project', 'imputetype'),
                ));
        $criteria->select = "*, 
                    ( select user.name from " . Project::TABLE_PROJECT_MANAGER . " inner join " . User::TABLE_USER . " on "
                 . "" . User::TABLE_USER . ".id = " . Project::TABLE_PROJECT_MANAGER . ".user_id "
                 . "where " . Project::TABLE_PROJECT_MANAGER . ".project_id = t.project_id order by user.name limit 1 ) as managerName";
        return new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                ));
    }

    public function refreshHoursFromTimestamps() {
        $this->frm_date_ini = $this->date_ini->format('d/m/Y');
        $this->frm_date_end = $this->date_end->format('d/m/Y');
        $this->frm_hour_ini = $this->date_ini->format('H:i');
        $this->frm_hour_end = $this->date_end->format('H:i');
        //$diff = PHPUtils::getHoursAndMinutesBetweenDates( $this->date_ini, $this->date_end );
        //$this->frm_duration_hours = $diff["hours"];
        //$this->frm_duration_minutes = $diff["minutes"];
    }

    public function afterFind() {
        // No action if custom search
        if (empty($this->date_ini) || empty($this->date_end))
            return;
        // Save old profile. If modified, then update profile price
        $this->oldTaskProfile = $this->profile_id;
        // Convert database dates back to PHP
        $this->date_ini = PHPUtils::convertDBDateTimeToPHPDateTime($this->date_ini);
        $this->date_end = PHPUtils::convertDBDateTimeToPHPDateTime($this->date_end);
        $this->refreshHoursFromTimestamps();
        // Fill frm_customer_name
        $this->frm_customer_name = $this->project->company->name;
        return parent::afterFind();
    }

    public function beforeSave() {
        if (!parent::beforeSave()) {
            return false;
        }
        
        // Check if notify exceeded project hours
        ServiceFactory::createUserProjectTaskService()->alertIfTaskExceedsProjectLimits($this);
        // Convert DateTime's to MySql datetime
        $this->date_ini = PHPUtils::convertPHPDateTimeToDBDateTime($this->date_ini);
        $this->date_end = PHPUtils::convertPHPDateTimeToDBDateTime($this->date_end);
        // For new records or altered profile, update price_per_hour
        if (( $this->isNewRecord ) || ( $this->oldTaskProfile != $this->profile_id )) {
            $profilePrices = ServiceFactory::createWorkerProfilesService()->getMapOfProfilePricesForProject($this->project_id);
            $this->price_per_hour = $profilePrices[$this->profile_id];
        }
        return true;
    }

    /* Return the date & time in "spanish" format for showing in views */

    public function getLongDateIni() {
        return $this->frm_date_ini . ' ' . $this->frm_hour_ini;
    }

    public function getLongDateEnd() {
        return $this->frm_date_end . ' ' . $this->frm_hour_end;
    }

    public function getCost() {
        return $this->getDuration() * $this->price_per_hour;
    }

    public function getDuration() {
        return PHPUtils::getHoursBetweenDates($this->date_ini, $this->date_end);
        //return round( PHPUtils::getHoursBetweenDates( $this->date_ini, $this->date_end ), 2 );
    }

    public function getFormattedDuration() {
        return number_format($this->getDuration(), 2, ',', '.');
    }

    public function getFormattedHourRange() {
        return $this->frm_hour_ini . '-' . $this->frm_hour_end;
    }

    public function canRefuse() {
        return ($this->status === TaskStatus::TS_APPROVED)
                && ($this->project->status === ProjectStatus::PS_OPEN);
    }

}
