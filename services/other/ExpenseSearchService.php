<?php
namespace app\services\other;

use yii;
use yii\data\Sort;
use yii\data\ActiveDataProvider;
use app\services\ServiceFactory;
use app\models\db\ProjectExpense;

/**
 * Some help functions for task search duties
 *
 * @author twocandles
 */
class ExpenseSearchService {

    const MY_LOG_CATEGORY = 'services.other.ExpenseSearchService';

    /**
     * Given a ExpenseSearch and a worker profile, return an associative array
     * with a taskProvider, projectsProvider and usersProvider
     * The taskSearch can be modified if the specified profile has to do it
     * @param ExpenseSearch $expenseSearch
     * @param CronosUser $user
     * @param int $flags see top of the document
     * @return array
     */
    public function getExpenseSearchFormProviders($expenseSearch) {
        $providers = array();
        $tasksCriteria = ServiceFactory::createProjectExpenseService()->getCriteriaFromExpenseSearch($expenseSearch);
        //$tasksCriteria->from = ProjectExpense::TABLE_USER_PROJECT_COST;
        $providers["costsProvider"] = new ActiveDataProvider(
                array(
            'query' => $tasksCriteria,
            'pagination' => array(
                'pageSize' => \Yii::$app->params['default_page_size'],
            ),
            'sort' => $this->getSort(),
        ));

        return $providers;
    }

    /**
     * Retrieves CSV content with the criteria specified in ExpenseSearch
     * @param ExpenseSearch $search
     * @param CronosUser $user user launching the search
     * @return string
     */
    public function getCSVContentFromSearch(ExpenseSearch $search) {
        
        $tasksCriteria = ServiceFactory::createProjectExpenseService()->getCriteriaFromExpenseSearch($search);
        Yii::import('ext.csv.CSVExport');
        $data = ProjectExpense::findAll($tasksCriteria);
        $csv = new CSVExport($data);
        $csv->includeColumnHeaders = true;
        $csv->headers = array(
            "Fecha Gasto",
            'Cliente',
            'Proyecto',
            'Tipo de gasto',
            'Forma de pago',
            'Imputador',
            'Importe'
        );
        
        // Callback for giving an appropiate result format (array)
        $csv->callback = function( $row ) {
            assert($row instanceof ProjectExpense);
            $results = array();
            $results[] = PHPUtils::removeHourPartFromDate($row->date_ini);
            $results[] = $row->companyName;
            $results[] = $row->projectName;
            $results[] = ExpenseType::toString($row->costtype);
            $results[] = ExpensePaymentMethod::toString($row->paymentMethod);
            $results[] = $row->workerName;
            $results[] = $row->importe;
            return $results;
        };
        
        $sep = ( empty(Yii::$app->params['csv_separator']) ) ? ',' : Yii::$app->params['csv_separator'];
        return $csv->toCSV(null, $sep);
    }

    /**
     * @return CSort
     */
    private function getSort() {
        $sort = new Sort();
        $sort->attributes = array(
            'companyName' => array(
                'asc' => 'com.name ASC',
                'desc' => 'com.name DESC',
            ),
            'projectName' => array(
                'asc' => 'proj.name ASC',
                'desc' => 'proj.name DESC',
            ),
            'workerName' => array(
                'asc' => 'us.name ASC',
                'desc' => 'us.name DESC',
            ),
            'dateIni' => array(
                'asc' => 't.date_ini ASC',
                'desc' => 't.date_ini DESC',
            ),
            'importe' => array(
                'asc' => 't.importe ASC',
                'desc' => 't.importe DESC',
            ),
            'costtype' => array(
                'asc' => 't.costtype ASC',
                'desc' => 't.costtype DESC',
            )
        );
        return $sort;
    }

}

?>
