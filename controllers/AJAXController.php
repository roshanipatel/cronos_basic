<?php
namespace app\controllers;

use Yii;
use app\components\CronosController;
use app\services\ServiceFactory;

/**
 * Contains actions for filling AJAX requests
 *
 * @author twocandles
 */
class AJAXController extends CronosController {
    const MY_LOG_CATEGORY = 'controllers.AJAXController';

    /**
     * Make sure is an ajax reqeust
     * @param
     */
    protected function beforeAction( $action ) {
        if( !parent::beforeAction( $action ) )
            return false;
        if( !Yii::$app->request->isAjaxRequest )
            throw new CHttpException( 403, 'Invalid request' );
        return true;
    }

    /**
     * Retrieves a list of customers by $term for autocomplete as an JSON encoded
     * array of id and value (ready for jqueryui autocomplete)
     * @param string $term
     * @return string
     */
    public function actionRetrieveCustomersByTermForAutocomplete( $term ) {
        $companies = ServiceFactory::createCompanyService()->getCompaniesBySubstring( $term );
        $result = array( );
        foreach( $companies as $company ) {
            $idx = count( $result );
            $result[$idx]['id'] = $company->id;
            $result[$idx]['value'] = $company->name;
        }
        echo CJSON::encode( $result );
    }

    /**
     * Retrieves a list of projects opened for a customer to fill a
     * drop down
     * @param string $customerName
     */
    public function actionRetrieveOpenProjectsFromCustomerNameAsListOptions( $customerName ) {
        
        $sFilterUser = "";
        if (!Yii::$app->user->isAdmin()) {
            $sFilterUser = (int) Yii::$app->user->id;
        }
        
        $projects = ServiceFactory::createProjectService()->findOpenProjectsFromCustomerByCustomerName( $customerName,  $sFilterUser);
        if( count( $projects ) > 0 )
            $opt = array( 'prompt' => 'Seleccione proyecto' );
        else
            $opt = array( 'prompt' => 'Cliente sin proyectos' );
        $output = CHtml::listOptions( array( ), \yii\helpers\ArrayHelper::map( $projects, 'id', 'name' ), $opt );
        echo $output;
    }

    /**
     * Retrieve the imputetypes from the project.
     * @param type $projectId
     */
    public function actionRetrieveImputetypesFromProjectAsListOptions( $projectId ) {
        
        $sFilterUser = "";
        if (!Yii::$app->user->isAdmin()) {
            $sFilterUser = (int) Yii::$app->user->id;
        }
        
        $aImputetypes = ServiceFactory::createImputetypeService()->findImputetypes( $projectId );
        if( count( $aImputetypes ) == 0 )
            $opt = array( 'prompt' => 'Proyecto sin tipos de imputación' );
        $output = CHtml::listOptions( array( ), \yii\helpers\ArrayHelper::map( $aImputetypes, 'id', 'name' ), $opt );
        echo $output;
    }
    
    /**
     * Retrieves a list of projects opened for a customer to fill a
     * drop down
     * @param string $customerName
     */
    public function actionRetrieveProjectsFromCustomerIdAsListOptions( $customerId, 
            $selectProjectPrompt, 
            $startFilter = "",
            $endFilter = "",
            $projectStatus = NULL, 
            $onlyManagedByUser = false,
            $onlyUserEnvolved = false,
            $projectStatusCom = NULL) {
        
        //Filtering by operational status.
        if( ProjectStatus::isValidValue( $projectStatus ) ) {
            $projectCriteria = new CDbCriteria( array(
                        'condition' => 't.status =: status',
                        'params' => array( 'status' => $projectStatus ) ) );
        }
        else {
            $projectCriteria = null;
        }
        
        //Filtering by comercial status
        if( ProjectStatus::isValidValue( $projectStatusCom ) ) {
            $projectCriteria = new CDbCriteria( array(
                        'condition' => 't.statuscommercial=:status',
                        'params' => array( 'status' => $projectStatusCom ) ) );
        }
        else {
            $projectCriteria = null;
        }
	
        $onlyManagedByUser = (bool)$onlyManagedByUser;
        $projects = ServiceFactory::createProjectService()->findProjectsFromCustomerByCustomerId( $customerId, 
                        Yii::$app->user, 
                        $projectCriteria, 
                        $onlyManagedByUser,
                        $startFilter,
                        $endFilter,
                        $onlyUserEnvolved);
        if( count( $projects ) > 0 ){
            $opt = array( 'prompt' => $selectProjectPrompt );
		}
        else{
            $opt = array( 'prompt' => 'Cliente sin proyectos' );
		}
        $output = CHtml::listOptions( array( ), \yii\helpers\ArrayHelper::map( $projects, 'id', 'name' ), $opt );
        echo $output;
    }

    
    /**
     * Retrieve the workers in a date.
     * @param type $startFilter
     * @param type $endFilter 
     */
    public function actionRetrieveWorkers(
            $selectWorkersPrompt,
            $startFilter = "",
            $endFilter = "",
            $onlyManagedByUser = false
            ) {
        
        $workers = ServiceFactory::createUserService()->getWorkers("name", $startFilter, $endFilter, $onlyManagedByUser);
        if( count( $workers ) > 0 ){
            $opt = array( 'prompt' => $selectWorkersPrompt );
		}
        else{
            $opt = array( 'prompt' => 'No hay trabajadores' );
		}
        $output = CHtml::listOptions( array( ), \yii\helpers\ArrayHelper::map( $workers, 'id', 'name' ), $opt );
        echo $output;
    }
    
    public function actionRetrieveCompanies(
            $selectWorkersPrompt,
            $startFilter = "",
            $endFilter = "",
            $worker = ""
            ) {
        
        $companies = ServiceFactory::createCompanyService()->findCompaniesWithProjectInTime($startFilter, $endFilter, $worker, true);
        if( count( $companies ) > 0 ){
            $opt = array( 'prompt' => $selectWorkersPrompt );
		}
        else{
            $opt = array( 'prompt' => 'No hay clientes' );
		}
        $output = CHtml::listOptions( array( ), \yii\helpers\ArrayHelper::map( $companies, 'id', 'name' ), $opt );
        echo $output;
    }
    
    /**
     * Retrieve managers
     * @param type $selectWorkersPrompt
     * @param type $startFilter
     * @param type $endFilter 
     */
    public function actionRetrieveManagers(
            $selectWorkersPrompt,
            $startFilter = "",
            $endFilter = ""
            ) {
        
        $workers = ServiceFactory::createUserService()->getWorkers("name", $startFilter, $endFilter, true);
        if( count( $workers ) > 0 ){
            $opt = array( 'prompt' => $selectWorkersPrompt );
		}
        else{
            $opt = array( 'prompt' => 'No hay managers' );
		}
        $output = CHtml::listOptions( array( ), \yii\helpers\ArrayHelper::map( $workers, 'id', 'name' ), $opt );
        echo $output;
    }
    
    public function actionRetrieveTasksForUserAndWeek() {
        $userId = isset( $_REQUEST['userId'] ) ? $_REQUEST['userId'] : null;
        $companyId = isset( $_REQUEST['companyId'] ) ? $_REQUEST['companyId'] : null;
        $date = isset( $_REQUEST['date'] ) ? $_REQUEST['date'] : null;
        // Build a task search object and set 'creator' and dateIni-dateEnd range
        $creatorId = -1;
        // If not admin, searching is based on current user
        if( Yii::$app->user->hasDirectorPrivileges() && User::find()->isValidID( $userId ) ) {
            $creatorId = (int)$userId;
        }
        else {
            $creatorId = (int)Yii::$app->user->id;
        }
        // Check date is valid. If not, take current
        if( $date !== NULL ) {
            $date = PHPUtils::convertStringToPHPDateTime( (string)$date );
        }
        if( !$date ) {
            //Yii::log( "Invalid date $date", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY );
            $date = new DateTime;
        }
        $tasks = ServiceFactory::createUserProjectTaskService()->findTasksAroundDateForUser( $creatorId, $date, $companyId );
        // Convert to array
        $tasksArray = array( );
        foreach( $tasks as $task ) {
            $adminFields = array( );
            if( Yii::$app->user->hasDirectorPrivileges() ) {
                $adminFields = array(
                    'status' => $task->status,
                    'profile' => $task->profile_id,
                );
            }
            $currentTask = array(
                'id' => $task->id,
                'start' => $task->date_ini->getTimestamp() * PHPUtils::PHP_TO_JS_TIMESTAMP_FACTOR,
                'end' => $task->date_end->getTimestamp() * PHPUtils::PHP_TO_JS_TIMESTAMP_FACTOR,
                'user_id' => $task->user_id,
                'project_id' => $task->project_id,
                'project_name' => $task->project->name,
                'imputetype_name' => $task->imputetype->name,
                'imputetype_id' => $task->imputetype->id,
                'description' => $task->task_description,
                'ticket' => $task->ticket_id,
                'customer_name' => $task->frm_customer_name,
                'customer_id' => $task->project->company_id,
				'is_extra' => $task->is_extra,
				'is_billable' => $task->is_billable,
                'readonly' => ( ! Yii::$app->user->hasDirectorPrivileges() ) && ( $task->status != TaskStatus::TS_NEW ),
            );
            // If admin then pass status and profile so they can be edited
            $canEditProfileAndStatus = Yii::$app->user->hasDirectorPrivileges();
            if( $canEditProfileAndStatus ){
                $currentTask['status'] = $task->status;
                $currentTask['profile_id'] = $task->profile_id;
            }
            $tasksArray[] = $currentTask;
        }
        echo json_encode( $tasksArray );
    }

}

?>
