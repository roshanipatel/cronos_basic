<?php
namespace app\models\db;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "imputetype".
 *
 * The followings are the available columns in table 'imputetype':
 * @property integer $id
 * @property string $name
 */
class Imputetype extends ActiveRecord
{
    const MY_LOG_CATEGORY = 'models.Imputetype';
    const TABLE_IMPUTETYPE = "imputetype";

    const OPERACIONES = 1;
    
    

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'imputetype';
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
            array( 'name', 'length', 'max' => 256 ),
            array( 'name', 'unique' ),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array( 'name', 'safe', 'on' => 'search' ),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'name' => 'Nombre',
        );
    }
    
    protected function afterFind() {
        $this->name = (utf8_decode($this->name));
        return parent::afterFind();
    }
    
    /**
     * Returns if the parameter is a valid company ID
     */
    static public function isValidID( $id )
    {
        return isset( $id ) && ( ((int)$id) > 0 );
    }
    
    public static function getDefaultImputetypesFilter() {
        return array("1", "2", "3", "4", "5", "6", "8");
    }
}