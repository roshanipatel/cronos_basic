<?php

/**
 * Model for making Expense Search
 *
 * @property string $dateini
 * @property string $dateEnd
 * @property integer $projectId
 * @property array $projectIdsForSearch
 * @property integer $worker
 * @property integer $owner
 * @property integer $companyId
 * @property string $companyName
 * @property string $projectName
 * @property integer $costtype
 * @property integer $paymentMethod
 * 
 */
class ExpenseSearch extends CFormModel {
    // Search fields

    const FLD_DATE_INI = 1;
    const FLD_DATE_END = 2;
    const FLD_PROJECT_ID = 3;
    const FLD_CUSTOMER = 4;
    const FLD_OWNER = 5;
    const FLD_COSTTYPE = 6;
    const FLD_WORKER = 7;
    const FLD_PAYMENT_METHOD = 8;

    public $dateIni;
    public $dateEnd;
    public $projectId;
    public $owner;
    public $worker;
    public $companyId;
    public $companyName;
    public $projectName;
    public $costtype;
    public $status;
    public $paymentMethod;
    // Used only for building criteria
    public $projectIdsForSearch;
    public $sort;

    const DATE_FORMAT_ON_CHECK = 'dd/MM/yyyy';

    // Values for boolean search criteria
    const VALUE_ALL = -1;
    const VALUE_ONLY_SET = 1;
    const VALUE_ONLY_NOT_SET = 0;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array(ExpenseSearch::FLD_NAME_DATE_INI, 'type', 'type' => 'datetime', 'datetimeFormat' => self::DATE_FORMAT_ON_CHECK, 'message' => 'Formato de fecha inválido'),
            array(ExpenseSearch::FLD_NAME_DATE_END, 'type', 'type' => 'datetime', 'datetimeFormat' => self::DATE_FORMAT_ON_CHECK, 'message' => 'Formato de fecha inválido'),
            array(ExpenseSearch::FLD_NAME_DATE_INI . "," .
                ExpenseSearch::FLD_NAME_DATE_END . "," .
                ExpenseSearch::FLD_NAME_PROJECT_ID . "," .
                ExpenseSearch::FLD_NAME_OWNER . "," .
                ExpenseSearch::FLD_NAME_WORKER . "," .
                ExpenseSearch::FLD_NAME_COMPANY_ID . "," .
                ExpenseSearch::FLD_NAME_COMPANY_NAME . "," .
                ExpenseSearch::FLD_NAME_EXPENSE_TYPE . "," .
                ExpenseSearch::FLD_NAME_PAYMENT_METHOD. ", sort", 'safe', 'on' => 'search'),
        );
    }

    const FLD_NAME_DATE_INI = 'dateIni';
    const FLD_NAME_DATE_END = 'dateEnd';
    const FLD_NAME_PROJECT_ID = 'projectId';
    const FLD_NAME_OWNER = 'owner';
    const FLD_NAME_WORKER = 'worker';
    const FLD_NAME_WORKER_NAME = 'workerName';
    const FLD_NAME_COMPANY_ID = 'companyId';
    const FLD_NAME_COMPANY_NAME = 'companyName';
    const FLD_NAME_PROJECT_NAME = 'projectName';
    const FLD_NAME_EXPENSE_TYPE = 'costType';
    const FLD_NAME_PAYMENT_METHOD = 'paymentMethod';    

    public function attributeLabels() {
        return array(
            ExpenseSearch::FLD_NAME_DATE_INI => 'Fecha inicial',
            ExpenseSearch::FLD_NAME_DATE_END => 'Fecha final',
            ExpenseSearch::FLD_NAME_PROJECT_ID => 'Proyecto',
            ExpenseSearch::FLD_NAME_OWNER => 'Manager',
            ExpenseSearch::FLD_NAME_WORKER_NAME => 'Imputador',
            ExpenseSearch::FLD_NAME_COMPANY_ID => 'ID Cliente',
            ExpenseSearch::FLD_NAME_COMPANY_NAME => 'Cliente',
            ExpenseSearch::FLD_NAME_PROJECT_NAME => 'Proyecto',
            ExpenseSearch::FLD_NAME_EXPENSE_TYPE => 'Tipo gasto',
            ExpenseSearch::FLD_NAME_PAYMENT_METHOD => 'Forma de pago'
        );
    }

    /**
     * @return CDbCriteria
     */
    public function buildCriteria() {
        $criteria = new CDbCriteria;
        $addJoinProject = false;

        // Fields for project: if not defined project field => all projects OK
        if ((!isset($this->projectIdsForSearch) ) && ( Project::isValidID($this->projectId) )) {
            $this->projectIdsForSearch = $this->projectId;
        }
        if (isset($this->projectIdsForSearch)) {
            if (is_array($this->projectIdsForSearch) || Project::isValidID($this->projectIdsForSearch)) {
                if ($this->projectIdsForSearch === array()) {
                    $criteria->compare('t.project_id', Project::INVALID_ID);
                } else {
                    $criteria->compare('t.project_id', $this->projectIdsForSearch);
                }
            }
        }

        // Fields for dates
        if (!empty($this->dateIni)) {
            $criteria->compare('t.date_ini', '>=' . PHPUtils::convertStringToDBDateTime(PHPUtils::addHourToDateIfNotPresent($this->dateIni, "00:00")));
        }
        if (!empty($this->dateEnd)) {
            $criteria->compare('t.date_ini', '<=' . PHPUtils::convertStringToDBDateTime(PHPUtils::addHourToDateIfNotPresent($this->dateEnd, "23:59")));
        }
        if (!empty($this->costtype)) {
            $criteria->compare('t.costtype', '=' . $this->costtype);
        }
        if (!empty($this->paymentMethod)) {
            $criteria->compare('t.paymentMethod', '=' . $this->paymentMethod);
        }
        if (!empty($this->projectId)) {
            $criteria->compare('t.project_id', '=' . $this->projectId);
        }
        // Fields for status
        if ($this->status != "" && TaskStatus::isValidValue($this->status)) {
            $criteria->compare('t.status', $this->status);
        }

        // Fields for workers
        if (User::isValidID($this->worker)) {
            $criteria->compare('t.user_id', $this->worker);
        }

        // Fields for managers
        if (User::isValidID($this->owner)) {
            $criteria->addCondition("( t.project_id in (select project_id from project_manager where user_id = '" . $this->owner . "') OR "
                    . " t.user_id = '" . $this->owner . "') ");
        }

        // Fields for customers
        if (Company::isValidID($this->companyId)) {
            $criteria->compare('project.company_id', $this->companyId);
            $criteria->compare('company.id', $this->companyId);
            $addJoinProject = true;
        }

        if ($addJoinProject) {
            $criteria->together = true;
            $criteria->with = array('project', 'company');
        }

        if (empty($this->sort)) {
            $criteria->order = 't.id desc';
        }

        return $criteria;
    }

    public static function getDropdownForFlags() {
        return array(
            self::VALUE_ONLY_SET => 'Activado',
            self::VALUE_ONLY_NOT_SET => 'Desactivado',
        );
    }

}

?>
