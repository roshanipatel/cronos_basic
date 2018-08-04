<?php
namespace app\models\db;

use Yii;
use app\components\ActiveRelationalRecord;
/**
 * This is the model class for table "price_per_project_and_profile".
 *
 * The followings are the available columns in table 'price_per_project_and_profile':
 * @property string $worker_profile_id
 * @property integer $project_id
 * @property string $price
 */
class PricePerProjectAndProfile extends ActiveRelationalRecord
{

    

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'price_per_project_and_profile';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array( 'worker_profile_id, project_id', 'required' ),
            array( 'project_id', 'exist', 'className' => 'Project', 'attributeName' => 'id' ),
            array( 'worker_profile_id', 'length', 'max' => 45 ),
            array( 'worker_profile_id', 'exist', 'className' => 'WorkerProfile', 'attributeName' => 'id' ),
            array( 'project_id', 'exist', 'className' => 'Project', 'attributeName' => 'id' ),
            array( 'price', 'length', 'max' => 11 ),
            array( 'price', 'numerical', 'min' => 0.0 ),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array( 'worker_profile_id, project_id, price', 'safe', 'on' => 'search' ),
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
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'worker_profile_id' => 'Worker Profile',
            'project_id' => 'Project',
            'price' => 'Price',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare( 'worker_profile_id', $this->worker_profile_id, true );
        $criteria->compare( 'project_id', $this->project_id );
        $criteria->compare( 'price', $this->price, true );

        return new CActiveDataProvider( get_class( $this ), array(
            'criteria' => $criteria,
        ) );
    }

}