<?php
namespace app\models\db;

use Yii;
use app\components\ActiveRelationalRecord;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "project_imputetype".
 *
 * The followings are the available columns in table 'project_imputetype':
 * @property integer $project_id
 * @property integer $imputetype_id
 */
class ProjectImputetype extends ActiveRelationalRecord
{
	

	/**
	 * @return string the associated database table name
	 */
	public static function tableName()
	{
		return 'project_imputetype';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('imputetype_id, project_id', 'required'),
			array('imputetype_id, project_id', 'integer'),
            array( 'imputetype_id', 'exist', 'className' => 'Imputetype', 'attributeName' => 'id' ),
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
			'imputetype_id' => 'Tipo de imputación',
			'project_id' => 'Proyecto',
                    'usercost' => 'Coste/hora Proyecto',
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

		$criteria->compare('imputetype_id',$this->imputetype_id);
		$criteria->compare('project_id',$this->project_id);

		return new ActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

       /**
     * Save managers
     * @param type $project
     * @param type $managers
     * @param type $hasToDeleteBefore
     * @return type
     */
    public function saveImputetypes($project, $imputetypes) {
        return parent::saveRelation('project_id', $project, 'imputetype_id', $imputetypes);
    }
}