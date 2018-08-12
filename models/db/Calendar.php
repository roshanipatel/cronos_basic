<?php
namespace app\models\db;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use app\models\db\Calendar;

/**
 * This is the model class for table "calendar".
 *
 * The followings are the available columns in table 'company':
 * @property integer $id
 * @property string $day
 * @property string $city
 *
 * The followings are the available model relations:
 */
class Calendar extends ActiveRecord
{
    
    const FESTIVO_NACIONAL = "-1";
    const FESTIVO_BARCELONA = "0";
    const FESTIVO_MADRID = "1";
    const FESTIVO_ELIMINAR = "-2";
    
    public static function toString($iFestivo) {
    
        if ($iFestivo == Calendar::FESTIVO_BARCELONA) {
            return "Barcelona";
        } else if ($iFestivo == Calendar::FESTIVO_NACIONAL) {
            return "Nacional";
        } else if ($iFestivo == Calendar::FESTIVO_MADRID) {
            return "Madrid";
        } else if ($iFestivo == Calendar::FESTIVO_ELIMINAR) {
            return "";
        }
    }
    
    const MY_LOG_CATEGORY = 'models.Calendar';
    const TABLE_NAME = "calendar";

    

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return Calendar::TABLE_NAME;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array( 'day, city', 'safe', 'on' => 'search' ),
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
            'day' => 'Día',
            'city' => 'Ciudad',
        );
    }

    /**
     * Make sure the admin company doesn't get deleted
     * @return <type>
     */
    public function beforeDelete()
    {
        return parent::beforeDelete();
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return ActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.
        $criteria =  Calendar::find();
       /* $criteria = new CDbCriteria( array(
                    'order' => 't.day asc',
                        ) );*/

        $criteria->andFilterWhere([
                'or',
                ['like', 'city', $this->city],
            ]);
        $criteria->andFilterWhere([
                'or',
                ['like', 'day', $this->day],
            ]);
        $criteria->orderBy = 't.day asc';
        return new ActiveDataProvider( array(
            'query' => $criteria,
                ) );
    }

    /**
     * Returns if the parameter is a valid company ID
     */
    static public function isValidID( $id )
    {
        return isset( $id ) && ( ((int)$id) > 0 );
    }
}