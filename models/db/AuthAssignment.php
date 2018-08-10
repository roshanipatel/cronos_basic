<?php
namespace app\models\db;

use Yii;
use app\components\ActiveRelationalRecord;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "AuthAssignment".
 *
 * The followings are the available columns in table 'AuthAssignment':
 * @property string $itemname
 * @property string $userid
 * @property string $bizrule
 * @property string $data
 *
 * The followings are the available model relations:
 * @property AuthItem $itemname0
 */
class AuthAssignment extends ActiveRelationalRecord
{
    protected static $table;
    

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'authassignment';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array( 'itemname, userid', 'required' ),
            array( 'itemname, userid', 'string', 'max' => 64 ),
            array( 'bizrule, data', 'safe' ),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array( 'itemname, userid, bizrule, data', 'safe', 'on' => 'search' ),
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
        //'itemname0' => array(self::BELONGS_TO, 'AuthItem', 'itemname'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'itemname' => 'Itemname',
            'userid' => 'Userid',
            'bizrule' => 'Bizrule',
            'data' => 'Data',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return ActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;
        /*
          $criteria->compare('itemname',$this->itemname,true);
          $criteria->compare('userid',$this->userid,true);
          $criteria->compare('bizrule',$this->bizrule,true);
          $criteria->compare('data',$this->data,true);
         */
        return new ActiveDataProvider( get_class( $this ), array(
            'criteria' => $criteria,
        ) );
    }

    static public function saveRoles( $userid, $roles, $hasToDeleteBefore = true )
    {
        $newRoles = array( );
        if( is_string( $roles ) )
            $newRoles[] = $roles;
        else if( is_array( $roles ) )
            $newRoles = $roles;
        else
            assert( false );
        $model = new AuthAssignment;
        return $model->saveRelation( 'userid', $userid, 'itemname', $newRoles, $hasToDeleteBefore );
    }

}