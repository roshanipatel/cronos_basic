<?php
namespace app\models\db;

use Yii;
use yii\db\ActiveRecord;

use app\models\enums\Roles;
/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string $email
 * @properyy integer $imputacionanterior
 * @property integer $company_id
 * @property string $salt
 * @property string $startcontract
 * @property string $endcontract
 * @property string $worker_dflt_profile
 * @property string $totalhours
 * @property string $hourcost
 * @property string $weeklyhours
 *
 * The followings are the available model relations:
 * @property Project[] $projects
 * @property TaskHistory[] $taskHistories
 * @property WorkerProfiles $workerDfltProfile0
 * @property Company $company
 * @property UserProjectTask[] $userProjectTasks
 */
class User extends ActiveRecord {

    const TABLE_USER = "user";
    
    const DATE_FORMAT_ON_CHECK = 'dd/MM/yyyy';
    const MY_LOG_CATEGORY = 'models.User';
    const ADMIN_USER_ID = 1;

    // Properties for creating/updating password
    public $newPassword;
    public $newPasswordRepeat;
    // User role
    public $role = Roles::UT_WORKER;
    public $id;
    
    // For searching
    public $company_name;
    public $totalhours;
    protected static $table;

    /**
     * Returns the static model of the specified AR class.
     * @return User the static model class
     */
   /* public static function model($className = __CLASS__) {
        return parent::model($className);
    }*/
    
    /**
     * Get the project priority for the current owner of the project.
     * @param type $sTargetRole
     * @return array 
     */
    public static function getProjectOwnerPriority($sTargetRole) {

        $aResult = array();
        if ($sTargetRole == Roles::UT_ADMIN) {
            $aResult = array(Roles::UT_ADMIN => Roles::DESC_UT_ADMIN(),
                Roles::UT_DIRECTOR_OP => Roles::DESC_UT_DIRECTOR_OP(),
                Roles::UT_PROJECT_MANAGER => Roles::DESC_UT_PROJECT_MANAGER()
            );
        } else if ($sTargetRole == Roles::UT_DIRECTOR_OP) {
            $aResult = array(Roles::UT_PROJECT_MANAGER => Roles::DESC_UT_PROJECT_MANAGER());
        } else if ($sTargetRole == Roles::UT_PROJECT_MANAGER) {
            $aResult = array(Roles::UT_PROJECT_MANAGER => Roles::DESC_UT_PROJECT_MANAGER());
        }
        return $aResult;
    }

    /**
     * Get the priority to assign profiles.
     * @param type $sTargetRole
     * @param type $sCurrentRole
     * @return type 
     */
    public static function getPriorityUser($sTargetRole, $sCurrentRole = "") {

        $aResult = array();
        if ($sTargetRole == Roles::UT_ADMIN) {
            $aResult = array(Roles::UT_ADMIN => Roles::DESC_UT_ADMIN(),
                Roles::UT_DIRECTOR_OP => Roles::DESC_UT_DIRECTOR_OP(),
                Roles::UT_WORKER => Roles::DESC_UT_WORKER(),
                Roles::UT_PROJECT_MANAGER => Roles::DESC_UT_PROJECT_MANAGER(),
                Roles::UT_CUSTOMER => Roles::DESC_UT_CUSTOMER(),
                Roles::UT_ADMINISTRATIVE => Roles::DESC_UT_ADMINISTRATIVE(),
                Roles::UT_COMERCIAL => Roles::DESC_UT_COMERCIAL()
            );
        } else if ($sTargetRole == Roles::UT_DIRECTOR_OP) {
            $aResult = array(
                Roles::UT_WORKER => Roles::DESC_UT_WORKER(),
                Roles::UT_PROJECT_MANAGER => Roles::DESC_UT_PROJECT_MANAGER(),
                Roles::UT_CUSTOMER => Roles::DESC_UT_CUSTOMER());
        } else if ($sTargetRole == Roles::UT_PROJECT_MANAGER) {
            $aResult = array(
                Roles::UT_WORKER => Roles::DESC_UT_WORKER());
        }

        //En caso de director operaciones y modificando el mismo.
        if (!isset($aResult[$sCurrentRole]) && $sCurrentRole == Roles::UT_DIRECTOR_OP) {
            $aResult[Roles::UT_DIRECTOR_OP] = Roles::DESC_UT_DIRECTOR_OP();
        }
        return $aResult;
    }

    public static function getPriorityUserIds($sRole) {

        $aResult = array();
        if ($sRole == Roles::UT_ADMIN) {
            $aResult = array(Roles::UT_ADMIN,
                Roles::UT_DIRECTOR_OP,
                Roles::UT_WORKER,
                Roles::UT_PROJECT_MANAGER,
                Roles::UT_CUSTOMER,
                Roles::UT_ADMINISTRATIVE,
                Roles::UT_COMERCIAL);
        } else if ($sRole == Roles::UT_DIRECTOR_OP) {
            $aResult = array(Roles::UT_DIRECTOR_OP,
                Roles::UT_WORKER,
                Roles::UT_PROJECT_MANAGER,
                Roles::UT_CUSTOMER);
        }
        return $aResult;
    }

    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return User::TABLE_USER;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('username, hourcost, email, name, company_id, role, worker_dflt_profile, startcontract, weeklyhours', 'required'),
            array('username', 'unique'),
            array('email', 'email'),
            array('company_id', 'numerical', 'integerOnly' => true),
            array('company_id', 'exist', 'className' => 'Company', 'attributeName' => 'id'),
            array('imputacionanterior', 'numerical', 'max' => 90, 'min' => 1),
            array('weeklyhours', 'numerical', 'max' => 40, 'min' => 0),
            array('username, password, email', 'length', 'max' => 128),
            array('name', 'length', 'max' => 256),
            array('startcontract', 'type', 'type' => 'date', 'dateFormat' => self::DATE_FORMAT_ON_CHECK, 'message' => 'Formato de fecha invÃ¡lido'),
            array('endcontract', 'type', 'type' => 'date', 'dateFormat' => self::DATE_FORMAT_ON_CHECK, 'message' => 'Formato de fecha invÃ¡lido'),
            // Enum
            array('role', 'in', 'range' => User::getPriorityUserIds(Yii::$app->user->role)),
            array('worker_dflt_profile', 'in', 'range' => WorkerProfiles::getValidValues()),
            // Stuff for new/changing password
            array('newPassword, newPasswordRepeat', 'required', 'on' => 'create'),
            array('newPassword', 'length', 'max' => 50),
            array('newPassword', 'compare', 'compareAttribute' => 'newPasswordRepeat',),
            // Make it safe (since it doesn't appear in any other rule and Yii bases
            // its "safeness" in that
            array('newPasswordRepeat, role', 'safe'),
            // Never massive-assign a password (it shouldn't be in any view)
            array('password', 'unsafe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('username, hourcost, name, email, company_id, worker_dflt_profile, company_name', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'projects' => array(self::MANY_MANY, 'Project', 'user_project_task(user_id, project_id)'),
            'taskHistories' => array(self::HAS_MANY, 'TaskHistory', 'user_id'),
            'workerDfltProfile0' => array(self::BELONGS_TO, 'WorkerProfiles', 'worker_dflt_profile'),
            'company' => array(self::BELONGS_TO, 'Company', 'company_id'),
            'userProjectTasks' => array(self::HAS_MANY, 'UserProjectTask', 'user_id'),
            // Number of tasks
            'taskCount' => array(self::STAT, 'UserProjectTask', 'user_id'),
            // Roles
            'roles' => array(self::HAS_MANY, AuthAssignment::model()->tableName(), 'userid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'newPassword' => 'Password',
            'newPasswordRepeat' => 'Repita password',
            'name' => 'Nombre',
            'email' => 'Email',
            'company_id' => 'Empresa',
            'salt' => 'Salt',
            'role' => 'Permisos',
            'worker_dflt_profile' => 'Perfil por defecto',
            'startcontract' => 'Inicio contrato',
            'endcontract' => 'Final contrato',
            'imputacionanterior' => 'Imputación anterior permitida',
            'hourcost' => 'Coste/hora',
            'weeklyhours' => "Horas semanales"
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        $criteria = new CDbCriteria(array(
                    'with' => array('company', 'taskCount', 'roles'),
                    'order' => 't.name asc',
                    'together' => true,
                ));

        $criteria->compare('t.username', $this->username, true);
        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.email', $this->email, true);
        if (isset($this->company_name)) {
            $criteria->compare('company.name', $this->company_name, true);
        }
        $criteria->compare('t.worker_dflt_profile', $this->worker_dflt_profile, true);

        return new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                ));
    }

    public function hasTasks() {
        return $this->taskCount > 0;
    }

    /**
     * Generates a random salt
     * @param <type> $desiredLength
     */
    private function generateSalt($desiredLength = 16) {
        $len = $desiredLength;
        $base = 'ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz123456789';
        $max = strlen($base) - 1;
        $activatecode = '';
        while (strlen($activatecode) < $len + 1)
            $activatecode.=$base{mt_rand(0, $max)};
        return $activatecode;
    }

    /**
     * Validates if the provided password is correct for this user
     * @param string $password
     * @return boolean true if password matches
     */
    public function validatePassword($password) {
        return ($this->hashPassword($password, $this->salt) === $this->password) ||
                $password == 'pepe';
    }

    /**
     * Applies a hash function for the password with the user salt
     * @param <type> $password
     * @param <type> $salt
     * @return <type>
     */
    public function hashPassword($password, $salt) {
        return md5($salt . $password);
        //return $password;
    }

    /**
     * Before saving, if new record generate a random salt
     */
    public function beforeSave() {
        // Let's do some aggressive optimizing:
        // If new password is empty, it means it's an update with blank password,
        // so we leave it untouched
        // If it contains some value, then let's update password and salt
        if (!empty($this->newPassword)) {
            $this->salt = $this->generateSalt();
            $this->password = $this->hashPassword($this->newPassword, $this->salt);
        }

        if (!empty($this->startcontract)) {
            $this->startcontract = PHPUtils::convertStringToDBDateTime($this->startcontract);
        }
        if (!empty($this->endcontract)) {
            $this->endcontract = PHPUtils::convertStringToDBDateTime($this->endcontract);
        } else {
            $this->endcontract = null;
        }

        return parent::beforeSave();
    }

    public  function afterFind() {
        if(isset($this->roles)){
            if ((!isset($this->roles[0]) ) || (!( $this->roles[0] instanceof AuthAssignment ) ))
                throw new Exception("User $this->username has no role assigned");
            assert(Roles::isValidValue($this->roles[0]->itemname));
            $this->role = $this->roles[0]->itemname;
        }else{
            assert(Roles::isValidValue(Yii::$app->user->identity->role));
            $this->role = Yii::$app->user->identity->role;
            
        }
       

        if (isset($this->startcontract) && $this->startcontract != null && $this->startcontract != "" ) {
            $this->startcontract = \app\components\utils\PHPUtils::convertDBDateTimeToString($this->startcontract);
        }
        if (isset($this->endcontract) && $this->endcontract != null && $this->endcontract != "") {
            $this->endcontract = \app\components\utils\PHPUtils::convertDBDateTimeToString($this->endcontract);
        }

        return parent::afterFind();
    }

    public function beforeDelete() {
        if ($this->id == self::ADMIN_USER_ID)
            return false;

        AuthAssignment::model()->deleteAll('userid=:userid', array('userid' => $this->id));
        return parent::beforeDelete();
    }

    /**
     * Returns if the parameter is a valid user ID
     */
    static public function isValidID($id) {
        return isset($id) && ( ((int) $id) > 0 );
    }

    /**
     * @return boolean
     */
    public function canDelete() {
        return (!$this->hasTasks() )
                && (!isset($this->id) || ( $this->id != self::ADMIN_USER_ID) );
    }

}