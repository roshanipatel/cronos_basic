<?php
namespace app\models\db;

use Yii;
use app\components\ActiveRelationalRecord;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "project_customer".
 *
 * The followings are the available columns in table 'project_customer':
 * @property integer $user_id
 * @property integer $project_id
 */
class ProjectCustomer extends ActiveRelationalRecord
{
	

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'project_customer';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, project_id', 'required'),
			array('user_id, project_id', 'integer'),
            array( 'user_id', 'exist', 'className' => 'User', 'attributeName' => 'id' ),
            array( 'project_id', 'exist', 'className' => 'Project', 'attributeName' => 'id' ),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, project_id', 'safe', 'on'=>'search'),
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
			'user_id' => 'User',
			'project_id' => 'Project',
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
		$criteria=new CDbCriteria;

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('project_id',$this->project_id);

		return new ActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

    public function saveCustomers( $project, $customers, $hasToDeleteBefore = true )
    {
        return parent::saveRelation('project_id', $project, 'user_id', $customers, $hasToDeleteBefore );
    }
}