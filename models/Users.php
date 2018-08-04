<?php

namespace common\models;

use Yii;
use yii\web\IdentityInterface;

class Users extends \common\models\base\UsersBase implements IdentityInterface {

    public $username;
    public $confirm_password;
    public $schools_type;
    public $captcha;

    const STATUS_ACTIVE = 1;

    public function beforeSave($insert) {
        if ($this->isNewRecord) {
            $this->setAttribute('created_at', date('Y-m-d H:i:s'));
        }
        $this->setAttribute('updated_at', date('Y-m-d H:i:s'));

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
           [['email', 'first_name'], 'required'],
           [['schools_id','password','confirm_password','country','state'], 'required','on'=>'front_registration'],
           [['role_id'], 'required','on'=>'front_registration','message'=>'Please select role.'],
           //['captcha', 'required','on'=>'front_registration'],
           ['captcha', 'captcha','on'=>'front_registration'],
           ['email','email'],
           [['status','password'],'safe'],
           ['email','validateEmail'],
           [['password','status'], 'required','on'=>'create'],
           ['confirm_password', 'compare','compareAttribute'=>'password','on'=>'front_registration'],
        ];
    }

    public function validateEmail() {
        $ASvalidateemail = Users::find()->where('email = "' . $this->email . '" and id != "' . $this->id . '"')->all();
        if (!empty($ASvalidateemail)) {
            $this->addError('email', 'This email address already registered.');
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'role_id' => Yii::t('app', 'Role ID'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'schools_id' => Yii::t('app', 'School Name'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'country' => Yii::t('app', 'Country'),
            'state' => Yii::t('app', 'State'),
            'user_info_url' => Yii::t('app', 'Website'),
            'schools_type' => Yii::t('app', 'School Type'),
            'captcha' => Yii::t('app', 'Enter Captch'),
            'captcha' => Yii::t('app', 'Verification Code'),
        ];
    }

    /** INCLUDE USER LOGIN VALIDATION FUNCTIONS* */

    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username) {
        return static::findOne(['email' => $username]);
    }

    /**
     * Finds user by password reset token
     *
     * @param  string      $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token) {
        $expire = \Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        if ($timestamp + $expire < time()) {
            // token expired
            return null;
        }

        return static::findOne([
                    'password_reset_token' => $token
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password) {
        return $this->password === md5($password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password_hash = Security::generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey() {
        $this->auth_key = Security::generateRandomKey();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken() {
        $this->password_reset_token = Security::generateRandomKey() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken() {
        $this->password_reset_token = null;
    }

    /**
     * Count  all users By users status.
     * @status : passed multiple status in comma serperetor
     * @is_logged_in : check user is logged in ya not
     */
    public static function countusersbystatus($status = false, $role_id = false, $is_logged_in = false) {
        $condition = '';
        if ($status != '') {
            $condition .= "status IN ($status)";
        }
        if (!empty($role_id)) {
            $condition .= (!empty($condition)) ? ' && ' : '';
            $condition .= "role_id = $role_id";
        }
        //$condition = "status IN ($status) && role_id = $role_id";
        if (!empty($is_logged_in)) {
            $condition .= (!empty($condition)) ? ' && ' : '';
            $condition .= "is_logged_in  = $is_logged_in";
        }
        return Users::find()
                        ->where($condition)
                        ->count();
    }
    public static function GetUserName(){
        
    }
   
}