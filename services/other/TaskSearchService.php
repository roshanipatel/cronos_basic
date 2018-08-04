<?php

/**
 * Some help functions for task serach duties
 *
 * @author twocandles
 */
class TaskSearchService {
	const MY_LOG_CATEGORY = 'services.other.TaskSearchService';
	/** Flags for searching */
	// Indicates a search for approving tasks. The projects are OPEN.
	const OP_APPROVE_TASKS = 0x1;

	// Indicates under which profile is the search performed.
	const SEARCH_AS_ADMIN = 0x2;
	const SEARCH_AS_MANAGER = 0x4;
	const SEARCH_AS_WORKER = 0x8;
	const SEARCH_AS_CUSTOMER = 0x10;
        const SEARCH_AS_COMMERCIAL = 0x16;
	// For convenience
	const DEFAULT_SEARCH = self::SEARCH_AS_WORKER;

	/**
	 * Given a TaskSearch and a worker profile, return an associative array
	 * with a taskProvider, projectsProvider and usersProvider
	 * The taskSearch can be modified if the specified profile has to do it
	 * @param TaskSearch $taskSearch
	 * @param CronosUser $user
	 * @param int $flags see top of the document
	 * @return array
	 */
        public function getTaskSearchFormProvidersForProfile(TaskSearch $taskSearch, CronosUser $user, $flags = self::DEFAULT_SEARCH) {
            
		$builder = $this->getBuilderForRole($flags, $taskSearch, $user);
		$providers = $builder->getProvidersForSearch($taskSearch[TaskSearch::FLD_NAME_DATE_INI], $taskSearch[TaskSearch::FLD_NAME_DATE_END]);

		$tasksCriteria = ServiceFactory::createUserProjectTaskService()->getCriteriaFromTaskSearch($taskSearch);
		$providers["tasksProvider"] = new CActiveDataProvider(
						'UserProjectTask',
						array(
							'criteria' => $tasksCriteria,
							'pagination' => array(
								'pageSize' => Yii::app()->params->default_page_size,
							),
							'sort' => $this->getSort(),
				));

		return $providers;
	}


	/**
	 * @return CSort
	 */
	private function getSort() {
		$sort = new CSort();
		$sort->attributes = array(
			'companyName' => array(
				'asc' => 'company.name ASC',
				'desc' => 'company.name DESC',
			),
			'project.name' => array(
				'asc' => 'project.name ASC',
				'desc' => 'project.name DESC',
			),
			'worker.name' => array(
				'asc' => 'worker.name ASC',
				'desc' => 'worker.name DESC',
			),
			'profile_id' => array(
				'asc' => 'profile_id ASC',
				'desc' => 'profile_id DESC',
			),
			'dateIni' => array(
				'asc' => 't.date_ini ASC',
				'desc' => 't.date_ini DESC',
			),
			'ticket_id' => array(
				'asc' => 't.ticket_id ASC',
				'desc' => 't.ticket_id DESC',
			),
			'task_description' => array(
				'asc' => 't.task_description ASC',
				'desc' => 't.task_description DESC',
			),
                        'managerName' => array(
				'asc' => 'managerName ASC',
				'desc' => 'managerName DESC',
			),
		);
		return $sort;
	}


	private function getBuilderForRole($flags, TaskSearch $taskSearch, CronosUser $user) {
		if($flags & self::OP_APPROVE_TASKS) {
			if($flags & self::SEARCH_AS_ADMIN) {
				return new TaskSearchProviderBuilderForApproveAdmin($user, $taskSearch);
			} else if($flags & self::SEARCH_AS_MANAGER) {
				return new TaskSearchProviderBuilderForApproveManager($user, $taskSearch);
			} else {
				throw new CHttpException(403, 'Acceso denegado');
			}
		} else {
                        //Once it is above x10 it is needed to reverse the sort order.
                        if($flags == self::SEARCH_AS_COMMERCIAL) {
				return new TaskSearchProviderBuilderForSearchCommercial($user, $taskSearch);
			} else if($flags == self::SEARCH_AS_ADMIN) {
				return new TaskSearchProviderBuilderForSearchAdmin($user, $taskSearch);
			} else if($flags == self::SEARCH_AS_MANAGER) {
				return new TaskSearchProviderBuilderForSearchManager($user, $taskSearch);
			} else if($flags == self::SEARCH_AS_CUSTOMER) {
				return new TaskSearchProviderBuilderForSearchCustomer($user, $taskSearch);
			} else if($flags == self::SEARCH_AS_WORKER) {
				return new TaskSearchProviderBuilderForSearchWorker($user, $taskSearch);
			} else {
				Yii::log("$flags does not contain a valid search profile", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY);
				throw new CHttpException(500, 'Error interno del sevidor');
			}
		}
	}

}

interface ITaskSearchProvidersBuilder {

	/**
	 * @return array Associative array with the following fields
	 * "projectsProvider"
	 * "usersProvider"
	 * "taskSearch"
	 */
	public function getProvidersForSearch($sStartDate = "", $sEndDate = "");
}

abstract class TaskSearchProviderBuilder implements ITaskSearchProvidersBuilder {

	private $userId;
	private $user;
	private $taskSearch;
	private $projectsCriteria;

	public function __construct(CronosUser $user, TaskSearch $taskSearch) {
		$this->userId = $user->id;
		$this->user = $user;
		$this->taskSearch = $taskSearch;
		/*
		  if( !empty( $taskSearch->companyId ) )
		  {
		  $this->projectsCriteria->addCondition( 't.company_id=:companyId' );
		  $this->projectsCriteria->params['companyId'] = $this->taskSearch->companyId;
		  } */
		$this->projectsCriteria = new CDbCriteria();
		$this->projectsCriteria->select = 't.id, t.name';
		$this->projectsCriteria->order = 't.id desc';
	}

	public function getProvidersForSearch($sStartDate = "", $sEndDate = "") {
		$result = array();
		$result["projectsProvider"] = $this->buildProjectsProvider();
		$result["usersProvider"] = $this->buildUsersProvider($sStartDate, $sEndDate);
                $result["managersProvider"] = $this->buildManagersProvider($sStartDate, $sEndDate);
		// Depending on the provider, some tweaks may be made to the task search,
		// like only approved tasks for customers
		$this->tweakTaskSearch();
		return $result;
	}

	protected function tweakTaskSearch() {
		if(!empty($this->taskSearch->projectId)) {
			$this->taskSearch->projectIdsForSearch = array($this->taskSearch->projectId);
		}
	}

	protected function buildProjectsProvider() {
		return array();
	}

	protected function buildUsersProvider($sStartDate = "", $sEndDate = "") {
		return array();
	}
        
        protected function buildManagersProvider($sStartDate = "", $sEndDate = "") {
		return array();
	}


	protected function mergeWithProjects(CDbCriteria $result) {
		if(( $this->projectsCriteria !== null ) && ( $this->projectsCriteria instanceof CDbCriteria ))
				return $result;
		else $this->projectsCriteria->mergeWith($result);
	}

	/**
	 * @return int
	 */
	protected function getUserId() {
		return $this->userId;
	}

	/**
	 * @return CronosUser
	 */
	protected function getUser() {
		return $this->user;
	}

	/**
	 * @return TaskSearch
	 */
	protected function getTaskSearch() {
		return $this->taskSearch;
	}

	/**
	 * @return CDbCriteria
	 */
	protected function getProjectsCriteria() {
		return $this->projectsCriteria;
	}

	// Some helpers

	// Return all projects
	protected function getProviderForAllProjects($onlyManagedByUser, $onlyUserEnvolved = false){
		return ServiceFactory::createProjectService()
						->findProjectsFromCustomerByCustomerId($this->getTaskSearch()->companyId, $this->getUser(),
								$this->getProjectsCriteria(), $onlyManagedByUser,
                                                        $this->getTaskSearch()->dateIni, $this->getTaskSearch()->dateEnd, $onlyUserEnvolved);
	}

	protected function getProviderForAllWorkers($sStartDate = "", $sEndDate = ""){
		return ServiceFactory::createUserService()->getWorkers("name", $sStartDate, $sEndDate);
	}
        
        protected function getProviderForAllManagers($sStartDate = "", $sEndDate = ""){
                return ServiceFactory::createUserService()->getWorkers("name", $sStartDate, $sEndDate, true);
	}

	// Tweak for project managers
	protected function setProjectsInTaskSearchForManager($onlyOpen){
		// Check that the manager has access to the project if
		// it's been specified
		if(Project::isValidID($this->getTaskSearch()->projectId)) {
			if(!ServiceFactory::createProjectService()->isManagerOfProject($this->getUserId(),
							$this->getTaskSearch()->projectId)) {
				throw new CHttpException(403, 'Acceso denegado');
			}
			self::tweakTaskSearch();
		} else {
			$managerProjects = ServiceFactory::createProjectService()->findProjectsByProjectManager($this->userId, $onlyOpen);
			// Make $taskSearch->projectId hold an array with all the projects
			// the customer has access to
//			$this->getTaskSearch()->projectIdsForSearch = array();
//			foreach($managerProjects as $project) {
//				$this->getTaskSearch()->projectIdsForSearch[] = $project->id;
//			}
                        
                        $workerProjects = ServiceFactory::createProjectService()->findProjectsByWorker($this->userId, $onlyOpen);
			// Make $taskSearch->projectId hold an array with all the projects
			// the customer has access to
//			$this->getTaskSearch()->projectIdsForSearch = array();
//			foreach($workerProjects as $project) {
//				$this->getTaskSearch()->projectIdsForSearch[] = $project->id;
//			}
		}
	}
        
        protected function setProjectsInTaskSearchForCommercial($onlyOpen){
		// Check that the manager has access to the project if
		// it's been specified
		if(Project::isValidID($this->getTaskSearch()->projectId)) {
			if(!ServiceFactory::createProjectService()->isCommercialOfProject($this->getUserId(),
							$this->getTaskSearch()->projectId)) {
				throw new CHttpException(403, 'Acceso denegado');
			}
			self::tweakTaskSearch();
		} else {
			$commercialProjects = ServiceFactory::createProjectService()->findProjectsByCommercial($this->userId, $onlyOpen);
			// Make $taskSearch->projectId hold an array with all the projects
			// the customer has access to
//			$this->getTaskSearch()->projectIdsForSearch = array();
//			foreach($commercialProjects as $project) {
//				$this->getTaskSearch()->projectIdsForSearch[] = $project->id;
//			}
		}
	}
}

class TaskSearchProviderBuilderForSearchAdmin extends TaskSearchProviderBuilder
		implements ITaskSearchProvidersBuilder {

	protected function buildProjectsProvider() {
		return parent::getProviderForAllProjects(false, true);
	}

	protected function buildUsersProvider($sStartDate = "", $sEndDate = "") {
		return parent::getProviderForAllWorkers($sStartDate, $sEndDate);
	}
        
        protected function buildManagersProvider($sStartDate = "", $sEndDate = "") {
		return parent::getProviderForAllManagers($sStartDate, $sEndDate);
	}

}

class TaskSearchProviderBuilderForSearchManager extends TaskSearchProviderBuilder
		implements ITaskSearchProvidersBuilder {

	protected function buildProjectsProvider() {
		return parent::getProviderForAllProjects(false, true);
	}

	protected function buildUsersProvider($sStartDate = "", $sEndDate = "") {
		return parent::getProviderForAllWorkers($sStartDate, $sEndDate);
	}
        
        protected function buildManagersProvider($sStartDate = "", $sEndDate = "") {
		return parent::getProviderForAllManagers($sStartDate, $sEndDate);
	}

	/**
	 * Set fixed search fields for customer
	 * @param TaskSearch $taskSearch
	 */
	protected function tweakTaskSearch() {
		parent::setProjectsInTaskSearchForManager(false);
	}

}

class TaskSearchProviderBuilderForSearchCommercial extends TaskSearchProviderBuilder
		implements ITaskSearchProvidersBuilder {

	protected function buildProjectsProvider() {
		return parent::getProviderForAllProjects(true, true);
	}

	protected function buildUsersProvider($sStartDate = "", $sEndDate = "") {
		return parent::getProviderForAllWorkers($sStartDate, $sEndDate);
	}
        
        protected function buildManagersProvider($sStartDate = "", $sEndDate = "") {
		return parent::getProviderForAllManagers($sStartDate, $sEndDate);
	}

	/**
	 * Set fixed search fields for customer
	 * @param TaskSearch $taskSearch
	 */
	protected function tweakTaskSearch() {
		parent::setProjectsInTaskSearchForCommercial(false);
	}

}

class TaskSearchProviderBuilderForSearchCustomer extends TaskSearchProviderBuilder implements ITaskSearchProvidersBuilder {

	// Cache results since they're used twice
	private $projectsCustomerHasAccessTo;

	private function getProjectsCustomerHasAccessTo() {
		if(empty($this->projectsCustomerHasAccessTo)) {
			$this->projectsCustomerHasAccessTo =
					ServiceFactory::createProjectService()->findProjectsCustomerHasAccessTo($this->getUserId());
		}
		return $this->projectsCustomerHasAccessTo;
	}

	protected function buildProjectsProvider() {
		return $this->getProjectsCustomerHasAccessTo();
	}

	/**
	 * Set fixed search fields for customer
	 * @param TaskSearch $taskSearch
	 */
	protected function tweakTaskSearch() {
		// Customer: fixed fields
		$this->getTaskSearch()->status = TaskStatus::TS_APPROVED;
		$this->getTaskSearch()->creator = null;
		// Tasks for customers are filtered based on projects, not the company
		// they belong to
		$this->getTaskSearch()->companyId = null;
		$this->getTaskSearch()->companyName = null;
		// Check that the customer has access to the project if
		// it's been specified
		if(Project::isValidID($this->getTaskSearch()->projectId)) {
			if(!ServiceFactory::createProjectService()->isCustomerOfProject($this->getUserId(),
							$this->getTaskSearch()->projectId)) {
				throw new CHttpException(403, 'Acceso denegado');
			}
			parent::tweakTaskSearch();
		} else {
			$customerProjects = $this->getProjectsCustomerHasAccessTo();
			// Make $taskSearch->projectId hold an array with all the projects
			// the customer has access to
//			$this->getTaskSearch()->projectIdsForSearch = array();
//			foreach($customerProjects as $project) {
//				$this->getTaskSearch()->projectIdsForSearch[] = $project->id;
//			}
		}
	}

}

class TaskSearchProviderBuilderForSearchWorker extends TaskSearchProviderBuilder
		implements ITaskSearchProvidersBuilder {

	protected function buildProjectsProvider() {
		return parent::getProviderForAllProjects(false, true);
	}

	/**
	 * Set fixed search fields for customer
	 * @param TaskSearch $taskSearch
	 */
	protected function tweakTaskSearch() {
		// Fixed fields for workers
		$this->getTaskSearch()->creator = $this->getUserId();
		$this->getTaskSearch()->profile = NULL;
		parent::tweakTaskSearch();
	}
}

/**
 * Parent class for "approving" providers: open projects and new tasks
 */
abstract class TaskSearchProviderBuilderForApproving extends TaskSearchProviderBuilder {

	public function __construct(CronosUser $user, TaskSearch $taskSearch) {
		parent::__construct($user, $taskSearch);
		$this->getProjectsCriteria()->scopes = 'open';
	}

	protected function tweakTaskSearch() {
		$this->getTaskSearch()->status = TaskStatus::TS_NEW;
		parent::tweakTaskSearch();
	}

	protected function buildUsersProvider($sStartDate = "", $sEndDate = "") {
		return parent::getProviderForAllWorkers($sStartDate, $sEndDate);
	}
        
        protected function buildManagersProvider($sStartDate = "", $sEndDate = "") {
		return parent::getProviderForAllManagers($sStartDate, $sEndDate);
	}
}

class TaskSearchProviderBuilderForApproveAdmin extends TaskSearchProviderBuilderForApproving
		implements ITaskSearchProvidersBuilder {

	protected function buildProjectsProvider() {
		return parent::getProviderForAllProjects(false);
	}

}

class TaskSearchProviderBuilderForApproveManager extends TaskSearchProviderBuilderForApproving
		implements ITaskSearchProvidersBuilder {

	protected function buildProjectsProvider() {
		return parent::getProviderForAllProjects(true);
	}

	/**
	 * Set fixed search fields for customer
	 * @param TaskSearch $taskSearch
	 */
	protected function tweakTaskSearch() {
		parent::tweakTaskSearch();
		parent::setProjectsInTaskSearchForManager(TRUE);
	}

}

?>
