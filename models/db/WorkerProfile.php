<?php

/**
 * This is the model class for table "worker_profiles".
 *
 * The followings are the available columns in table 'worker_profiles':
 * @property string $id
 * @property string $dflt_price
 *
 * The followings are the available model relations:
 * @property Project[] $projects
 * @property User[] $users
 * @property UserProjectTask[] $userProjectTasks
 */
class WorkerProfile extends CActiveRecord
{
    const I18N_CATEGORY = 'models';

    /**
     * Returns the static model of the specified AR class.
     * @return WorkerProfiles the static model class
     */
    public static function model( $className=__CLASS__ )
    {
        return parent::model( $className );
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'worker_profiles';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array( 'id, dflt_price', 'required' ),
            //array( 'id', 'length', 'max' => 45 ),
            array( 'id', 'in', 'range' => WorkerProfiles::getValidValues(), 'strict' => true ),
            array( 'dflt_price', 'length', 'max' => 11 ),
            array( 'dflt_price', 'numerical', 'min' => 0 ),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array( 'id, dflt_price', 'safe', 'on' => 'search' ),
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
            'projects' => array( self::MANY_MANY, 'Project', 'price_per_project_and_profile(worker_profile_id, project_id)' ),
            'users' => array( self::HAS_MANY, 'User', 'worker_dflt_profile' ),
            'userProjectTasks' => array( self::HAS_MANY, 'UserProjectTask', 'profile_id' ),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'dflt_price' => 'Dflt Price',
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

        $criteria->compare( 'id', $this->id, true );
        $criteria->compare( 'dflt_price', $this->dflt_price, true );

        return new CActiveDataProvider( get_class( $this ), array(
            'criteria' => $criteria,
        ) );
    }

    public function getDataForDropDown()
    {
        $values = self::model()->findAll();
        $result = array( );
        foreach( $values as $value )
        {
            $result[$value['id']] = Yii::t( self::I18N_CATEGORY, $value['id'] );
        }
        return $result;
    }
}