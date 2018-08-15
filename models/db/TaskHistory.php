<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "task_history".
 *
 * The followings are the available columns in table 'task_history':
 * @property integer $id
 * @property integer $user_id
 * @property integer $user_project_task_id
 * @property string $timestamp
 * @property string $status
 * @property string $comment
 *
 * The followings are the available model relations:
 * @property User $user
 * @property UserProjectTask $userProjectTask
 */
class TaskHistory extends Model
{

    
    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'task_history';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array( 'user_id, user_project_task_id, timestamp, status, comment', 'required' ),
            array( 'user_id, user_project_task_id', 'integer' ),
            array( 'user_id', 'exist', 'className' => 'User', 'attributeName' => 'id' ),
            array( 'user_project_task_id', 'exist', 'className' => 'UserProjectTask', 'attributeName' => 'id' ),
            array( 'status', 'in', 'range' => TaskStatus::getValidValues() ),
            array( 'comment', 'string', 'max' => 1024 ),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array( 'id, user_id, user_project_task_id, timestamp, status, comment', 'safe', 'on' => 'search' ),
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
            'user' => array( self::BELONGS_TO, 'User', 'user_id' ),
            'userProjectTask' => array( self::BELONGS_TO, 'UserProjectTask', 'user_project_task_id' ),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'user_project_task_id' => 'User Project Task',
            'timestamp' => 'Timestamp',
            'status' => 'Status',
            'comment' => 'Comment',
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
        $criteria = TaskHistory::find();

//        $criteria->where(['user_id'=>$this->user_id,'project_id'=>$this->project_id]);

        return new ActiveDataProvider(array(
            'query'=>$criteria,
        ));
       /* $criteria = new CDbCriteria;

        return new ActiveDataProvider( get_class( $this ), array(
            'criteria' => $criteria,
                ) );*/
    }

    public static function beforeSave()
    {
        // Convert DateTime's to MySql datetime
        $this->timestamp = PHPUtils::convertStringToDBDateTime( $this->timestamp );
        return parent::beforeSave();
    }

    public static function afterFind()
    {
        // Convert database dates back to PHP
        $this->timestamp = PHPUtils::convertDBDateTimeToString( $this->timestamp );
        return parent::afterFind();
    }
}