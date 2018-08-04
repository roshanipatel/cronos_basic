<?php
namespace app\models\db;

use Yii;
use app\components\ActiveRelationalRecord;
/**
 * This is the model class for table "project_reporting".
 *
 * The followings are the available columns in table 'project_reporting':
 * @property integer $role_id
 * @property integer $project_id
 */
class ProjectReporting extends ActiveRelationalRecord
{
	

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'project_reporting';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('role_id, project_id', 'required'),
			array('project_id', 'numerical', 'integerOnly'=>true),
                        array( 'project_id', 'exist', 'className' => 'Project', 'attributeName' => 'id' ),
                        array( 'role_id', 'exist', 'className' => 'Role', 'attributeName' => 'id' ),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('project_id', 'safe', 'on'=>'search'),
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
			'role_id' => 'Rol',
			'project_id' => 'Project'
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
		$criteria=new CDbCriteria;
                
		$criteria->compare('project_id',$this->project_id);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

    public function saveProjectReporting( $project, $role, $hasToDeleteBefore = true )
    {
        return parent::saveRelation('project_id', $project, 'role_id', $role, $hasToDeleteBefore );
    }
}