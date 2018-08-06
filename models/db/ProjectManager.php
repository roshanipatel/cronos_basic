<?php
namespace app\models\db;

use Yii;
use app\components\ActiveRelationalRecord;
/**
 * This is the model class for table "project_manager".
 *
 * The followings are the available columns in table 'project_manager':
 * @property integer $user_id
 * @property integer $project_id
 * @property integer $usercost
 */
class ProjectManager extends ActiveRelationalRecord {

    
    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'project_manager';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, project_id', 'required'),
            array('user_id, project_id', 'numerical', 'integerOnly' => true),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('user_id, project_id', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'user_id' => 'User',
            'project_id' => 'Project',
            'usercost' => 'Coste/hora Proyecto',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('project_id', $this->project_id);

        return new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * Save managers
     * @param type $project
     * @param type $managers
     * @param type $hasToDeleteBefore
     * @return type
     */
    public function saveManagers($project, $managers, $aCost,  $hasToDeleteBefore = true) {
        return parent::saveRelation2('project_id', $project, 'user_id', $managers, 'usercost', $aCost, $hasToDeleteBefore);
    }
}