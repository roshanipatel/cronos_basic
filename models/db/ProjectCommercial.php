<?php
namespace app\models\db;

use Yii;
use app\components\ActiveRelationalRecord;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "project_commercial".
 *
 * The followings are the available columns in table 'project_customer':
 * @property integer $user_id
 * @property integer $project_id
 * @property integer $usercost
 */
class ProjectCommercial extends ActiveRelationalRecord
{
	
	/**
	 * @return string the associated database table name
	 */
	public static function tableName()
	{
		return 'project_commercial';
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
		$criteria=ProjectCommercial::find();

		$criteria->andFilterWhere([
                'or',
                ['like', 'user_id',$this->user_id],
                ['like','project_id',$this->project_id],
            ]);
		/*$criteria->compare('user_id',$this->user_id);
		$criteria->compare('project_id',$this->project_id);
*/
		return new ActiveDataProvider(array(
			'query'=>$criteria,
		));
	}

    public function saveCommercial( $project, $commercial, $aCost, $hasToDeleteBefore = true )
    {
        return parent::saveRelation2('project_id', $project, 'user_id', $commercial, 'usercost', $aCost, $hasToDeleteBefore );
    }
}