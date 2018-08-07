<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\components\CronosController;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\enums\Roles;


class SiteController extends CronosController {

	static private $indexByRole = array(
		Roles::UT_ADMIN => 'user-project-task/calendar',
        Roles::UT_DIRECTOR_OP => 'user-project-task/calendar',
		Roles::UT_CUSTOMER => 'user-project-task/searchTasksCustomer',
		Roles::UT_PROJECT_MANAGER => 'user-project-task/approveTasks',
		Roles::UT_WORKER => 'user-project-task/calendar',
        Roles::UT_ADMINISTRATIVE => 'project/projectOverview',
        Roles::UT_COMERCIAL => 'project/projectOverview'
	);

	public function allowedActions() {
        return '*';
    }
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex() {
		/*if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        else
        {
        	$model = new LoginForm();
           return $this->render('login', [
            'model' => $model,
        ]);
        }*/ 

        if (\Yii::$app->user->isGuest) {
            return $this->redirect(Yii::$app->urlManager->createUrl(['site/login']));;
        }
        return $this->render('index');
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError() {
		$exception = Yii::$app->errorHandler->exception;
	    if ($exception !== null) {
	        return $this->render('error', ['exception' => $exception]);
	    }
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin() {
        $this->layout = "login_main";
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        $model->load(Yii::$app->request->post());
        
        if ($model->login()) {
            
            return $this->redirect(Yii::$app->urlManager->createUrl([self::$indexByRole[Yii::$app->user->identity->role]]));
        }

       // $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout() {
		Yii::$app->user->logout();

        return $this->goHome();
	}
	/**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

}