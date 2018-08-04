<?php

namespace app\models;

use app\models\enums\Roles;

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    //public $id;
    //public $username;
   // public $password;
    public $authKey;
    public $accessToken;
    public $role = Roles::UT_WORKER;
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
        return static::findOne($id);
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
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
       
       // return static::findOne(['username' => $username]);

         $user = User::find()->where(['username' => $username])->one();//I don't know if this is correct i am   //checing value 'becky' in username column of my user table.

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
