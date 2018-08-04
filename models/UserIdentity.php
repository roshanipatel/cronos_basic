<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity  extends ActiveRecord implements IdentityInterface {
	const MY_LOG_CATEGORY = 'components.UserIdentity';

	private $_id;

	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate() {
		/*
		 * HARDCODE!!
		 *
		  $users=array(
		  // username => ( password, id )
		  'demo' =>array('demo', 0),
		  'pask'=>array('pask', 1),
		  'user1'=>array('user1', 2),
		  'user2'=>array('user2', 3),
		  'user3'=>array('user3', 4),
		  'user4'=>array('user4', 5),
		  );
		  if(!isset($users[$this->username]))
		  $this->errorCode=self::ERROR_USERNAME_INVALID;
		  else if($users[$this->username][0]!==$this->password)
		  $this->errorCode=self::ERROR_PASSWORD_INVALID;
		  else
		  {
		  $this->errorCode=self::ERROR_NONE;
		  $this->_id = $users[$this->username][1];
		  }
		  return ! $this->errorCode;
		 */
		// Entering by email?
		if(strpos($this->username, "@")) {
			$user = User::model()->findByAttributes(array('email' => $this->username));
		}
		// Entering by username
		else {
			$user = User::model()->findByAttributes(array('username' => $this->username));
		}
		print_r($user);die;
		if($user === null) {
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		} else if(!$user->validatePassword($this->password)) {
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		} else if($user->role == null) {
			$this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
		} else {
			print_r($user);die;
			$this->_id = $user->id;
			$this->username = $user->username;
			$this->errorCode = self::ERROR_NONE;
			// For convenience
			$this->setState('role', $user->role);
			if(!empty($user->worker_dflt_profile)) {
				$this->setState('profile', $user->worker_dflt_profile);
			} else {
				$this->setState('profile', WorkerProfiles::WP_JUNIOR);
			}
		}
		return!$this->errorCode;
	}

	public function getId() {
		return $this->_id;
	}

}