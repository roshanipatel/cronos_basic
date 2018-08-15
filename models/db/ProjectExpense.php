<?php
namespace app\models\db;

use Yii;
use yii\db\ActiveRecord;
use yii\data\Sort;
use yii\data\ActiveDataProvider;
use app\models\enums\ExpenseType;
use app\models\enums\ExpensePaymentMethod;

/**
 * This is the model class for table "user_project_cost".
 *
 * The followings are the available columns in table 'user_project_cost':
 * @property string $companyName
 * @property string $companyId
 * @property integer $id
 * @property integer $user_id
 * @property integer $project_id
 * @property string $status
 * @property string $date_ini
 * @property string $costtype
 * @property string $importe
 * @property string $origen
 * @property string $destino
 * @property string $pdffile
 * @property string $comentario
 * @property string $transporttype
 * @property string $company
 * @property string $paymentMethod
 * 
 * The followings are the available model relations:
 * @property User $worker
 * @property Project $project
 */
class ProjectExpense extends ActiveRecord {

    const MY_LOG_CATEGORY = 'models.ProjectExpense';
    const DATE_FORMAT_ON_CHECK = 'dd/MM/yyyy';
    //const ONLY_DATE_ON_CONVERSION = 'd/m/Y';
    //const ONLY_TIME_ON_CONVERSION = 'H:i';
    const SCENARIO_COST_SEARCH = 'SCN_COST_SEARCH';
    const SCENARIO_NO_VALIDATION = 'SCN_NO_VALIDATION';

    // Form transitional fields
    public $companyName;
    public $projectName;
    public $workerName;
    public $companyId;

    

    /**
     * @return string the associated database table name
     */
    public static function tableName() {
        return ProjectExpense::TABLE_USER_PROJECT_COST;
    }
    
    const TABLE_USER_PROJECT_COST = 'user_project_cost';

    public function init() {
        if ($this->scenario == self::SCENARIO_NO_VALIDATION) {
            $this->date_ini = new DateTime;
        }
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array(['user_id', 'project_id'], 'required'),
            array(['user_id', 'project_id'], 'integer'),
            array('user_id', 'exist', 'targetClass' => '\app\models\User', 'targetAttribute' => 'id'),
            array('project_id', 'exist', 'targetClass' => '\app\models\db\Project', 'targetAttribute' => 'id'),
            array('companyName', 'exist', 'targetClass' => '\app\models\db\Company', 'targetAttribute' => 'name'),
            array('companyId', 'exist', 'targetClass' => '\app\models\db\Company', 'targetAttribute' => 'id'),
            array('importe', 'integer', 'min'=>0, 'max'=>100000),
            array('costtype', 'in', 'range' => ExpenseType::getValidValues()),
            array('paymentMethod', 'in', 'range' => ExpensePaymentMethod::getValidValues()),
            array('date_ini', 'datetime', 'format' => self::DATE_FORMAT_ON_CHECK, 'message' => 'Formato de fecha inválida'),
            array(['status', 'origen', 'destino', 'motivo', 'comentario', 'company', 'transporttype'], 'string', 'max' => 1024)
        );
    }
    
    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'worker' => array(self::BELONGS_TO, 'User', 'user_id', 'select' => 'worker.name'),
            'project' => array(self::BELONGS_TO, 'Project', 'project_id', 'select' => 'project.name, project.company_id, project.status')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'user_id' => 'Usuario',
            'project_id' => 'Proyecto',
            'status' => 'Estado',
            'date_ini' => 'Fecha gasto',
            'costtype' => 'Tipo de gasto', 
            'importe' => 'Importe',
            'origen' => 'Origen',
            'destino' => 'Destino',
            'motivo' => 'Motivo',
            'pdffile' => 'Fichero PDF',
            'comentario' => 'Comentario',
            'transporttype' => 'Tipo transporte',
            'company' => 'Proveedor',
            'companyName' => 'Cliente',
            'paymentMethod' => 'Forma de pago',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return ActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        $criteria = ServiceFactory::createProjectExpenseService()->getCriteriaFromModel($this);
        $criteria->orderBy = "";
        $criteria->select = "*";
        $criteria->from = ProjectExpense::TABLE_USER_PROJECT_COST;
        return new ActiveDataProvider(array(
                    'query' => $criteria,
                    'pagination' => array(
                        'pageSize' => Yii::$app->params['default_page_size'],
                    ),
                    'sort' => $this->getSort(),
                ));
    }
    
    private function getSort() {
        $sort = new Sort();
        $sort->attributes = array(
            'date_ini' => array(
                'asc' => 't.date_ini ASC',
                'desc' => 't.date_ini DESC',
            ),
            'status' => array(
                'asc' => 't.status ASC',
                'desc' => 't.status DESC'
            )
        );
        return $sort;
    }

    public function afterFind() {
        // Convert database dates back to PHP
        $this->date_ini = PHPUtils::convertDBDateTimeToString($this->date_ini);
        
        // Fill companyName
        $this->companyName = $this->project->company->name;
        $this->companyId = $this->project->company->id;
        $this->importe = number_format($this->importe, 2, ",", "");
        
        
        return parent::afterFind();
    }

    public function beforeSave() {
        $this->date_ini = PHPUtils::convertStringToDBDateTime($this->date_ini);
        
        if (!parent::beforeSave()) {
            return false;
        }
        
        return true;
    }
    
    public function canRefuse() {
        return ($this->status === TaskStatus::TS_APPROVED)
                && ($this->project->status === ProjectStatus::PS_OPEN);
    }

}
