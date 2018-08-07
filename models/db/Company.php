<?php
namespace app\models\db;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "company".
 *
 * The followings are the available columns in table 'company':
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $totalhours
 *
 * The followings are the available model relations:
 * @property Project[] $projects
 * @property User[] $users
 */
class Company extends ActiveRecord
{
    public $totalhours;
    const MY_LOG_CATEGORY = 'models.Company';
    const TABLE_COMPANY = "company";
    const OPEN3S_ID = 1;

   

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array( 'name', 'required' ),
            array( 'name', 'string', 'max' => 256 ),
            array( 'name', 'unique' ),
            array( 'email', 'email' ),
            array( 'email', 'string', 'max' => 128 ),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array( 'name, email', 'safe', 'on' => 'search' ),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'projects' => array( self::HAS_MANY, 'Project', 'company_id' ),
            'users' => array( self::HAS_MANY, 'User', 'company_id' ),
            // Number of projects and users
            'userCount' => array( self::STAT, 'User', 'company_id' ),
            'projectCount' => array( self::STAT, 'Project', 'company_id' ),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'name' => 'Nombre',
            'email' => 'Email',
        );
    }

    /**
     * Make sure the admin company doesn't get deleted
     * @return <type>
     */
    public function beforeDelete()
    {
        if( $this->id == self::OPEN3S_ID )
            return false;
        else
            return parent::beforeDelete();
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return ActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria( array(
                    'with' => array( 'userCount', 'projectCount' ),
                    'order' => 't.name asc',
                        ) );

        $criteria->compare( 'name', $this->name, true );
        $criteria->compare( 'email', $this->email, true );

        return new ActiveDataProvider( get_class( $this ), array(
            'criteria' => $criteria,
                ) );
    }
    
    /**
     * Get the random color for the company
     * @return string
     */
    public function getColor() {
        $letters = array('0','1','2','3','4','5','6','7','8','9');
        $color = "";
        for ($i = 0; $i < 6; $i++) {
            $color .= $letters[rand(0, 9)];
        }
        return $color;
    }
    

    public function hasUsers()
    {
        return $this->userCount > 0;
    }

    public function hasProjects()
    {
        return $this->projectCount > 0;
    }

    /**
     * Returns if the parameter is a valid company ID
     */
    static public function isValidID( $id )
    {
        return isset( $id ) && ( ((int)$id) > 0 );
    }

    /**
     * @return boolean
     */
    public function canDelete()
    {
        return (!$this->hasProjects() )
            && (!$this->hasUsers() )
            && (!isset( $this->id ) || ( $this->id != self::OPEN3S_ID) );
    }

}