<?php
namespace app\services;

/**
 * Description of ServiceFactory
 *
 * @author twocandles
 */
class ServiceFactory
{

    private function __construct()
    {

    }

    static private $services = array( );

    /**
     * @return UserService
     */
    static public function createUserService()
    {
        return self::createService( 'app\services\models\UserService' );
    }
    
    /**
     * 
     * @return ImputetypeService
     */
    static public function createImputetypeService()
    {
        return self::createService( 'app\services\models\ImputetypeService' );
    }

    /**
     * @return ProjectService
     */
    static public function createProjectService()
    {
        return self::createService( 'app\services\models\ProjectService' );
    }

    /**
     * @return CompanyService
     */
    static public function createCompanyService()
    {
        return self::createService( 'app\services\models\CompanyService' );
    }

    /**
     * @return WorkerProfilesService
     */
    static public function createWorkerProfilesService()
    {
        return self::createService( 'app\services\models\WorkerProfilesService' );
    }
    
    /**
     * Project Expense Service
     * @return ProjectExpenseService
     */
    static public function createProjectExpenseService()
    {
        return self::createService( 'app\services\models\ProjectExpenseService' );
    }
    
    /**
     * Project 
     * @return ExpenseSearchService
     */
    static public function createExpenseSearchService()
    {
        return self::createService( 'app\services\models\ExpenseSearchService' );
    }

    /**
     * @return UserProjectTaskService
     */
    static public function createUserProjectTaskService()
    {
        return self::createService( 'app\services\models\UserProjectTaskService' );
    }

    /**
     * @return AlertService
     */
    static public function createAlertService()
    {
        return self::createService( 'app\services\models\AlertService' );
    }

    /**
     * @return TaskSearchService
     */
    static public function createTaskSearchService()
    {
        return self::createService( 'app\services\models\TaskSearchService' );
    }

    /**
     * @return CronosService
     */
    static private function createService( $service )
    {
        if( !in_array( $service, self::$services ) )
        {
            self::$services[$service] = new $service;
        }
        return self::$services[$service];
    }

}

?>
