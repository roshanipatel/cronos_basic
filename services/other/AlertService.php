<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AlertService
 *
 * @author twocandles
 */
class AlertService {
    const MY_LOG_CATEGORY = 'services.AlertService';

    /**
     * @var NotifierBuilder
     */
    private $notifierBuilder;
    private $notifierErrors;

    /**
     * The parameter allows to pass in a mock notifier builder (for testing)
     * @param NotifierBuilder $notifierBuilder
     */
    public function __construct() {
        $this->notifierBuilder = new NotifierBuilder;
        $this->notifierErrors = array();
        $this->_init();
    }

    public function getNotifierErrors(){
        return $this->notifierErrors;
    }
    private function clearErrors(){
        $this->notifierErrors = array();
    }
    private function addNotifierError($notifierName, $errorMessage){
		Yii::log( "Unable to notifiy with $notifierName the message: $errorMessage", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY );
        $this->notifierErrors[] = array(
            'name' => $notifierName,
            'message' => $errorMessage,
        );
    }

    /**
     *
     * @param string $alertCode
     * @param array $params
     */
    public function notify( $alertCode, $params ) {
        $this->clearErrors();
        try {
            $message = $this->_buildMessage( $alertCode, $params );
            $notifiers = $this->getNotifiers();
            foreach( $notifiers as $notifier ) {
                try {
                    $notifier->notify( $message, $params );
                }
                catch(Exception $e){
                    $this->addNotifierError(get_class($notifier), "Falló la llamada de notificación para el mensaje ($message).\nError: {$e->getMessage()}");
                }
            }
        }
        catch( Exception $e ) {
            $this->addNotifierError('Generic', "Falló la llamada de notificación para el mensaje ($message).\nError: {$e->getMessage()}");
        }
    }

    private function _buildMessage( $alertCode, array $params ) {
        $messages = $this->getMessages();
        if( !key_exists( $alertCode, $messages ) ) {
            throw new Exception( "Alert code ($alertCode) not found!" );
        }
        $result = $messages[$alertCode];
        if( isset( $params[Alerts::MESSAGE_REPLACEMENTS] ) )
            foreach( $params[Alerts::MESSAGE_REPLACEMENTS] as $key => $value ) {
                $result = str_replace( '{' . $key . '}', $value, $result );
            }
        return $result;
    }

    // Protected so it can be mocked
    protected function getMessages() {
        return self::$_messages;
    }

    static private $_messages;

    /**
     * @return array
     */
    private function _init() {
        if( empty( self::$_messages ) ) {
            self::_init2();
        }
    }

    private function _init2() {
        self::$_messages = Alerts::getDescriptions();
    }

    private $notifiers = NULL;

    private function getNotifiers() {
        if( $this->notifiers === NULL ) {
            $this->buildNotifiers();
        }
        return $this->notifiers;
    }

    /**
     * @return array
     */
    private function buildNotifiers() {
        $notifierClasses = $this->getNotifierClasses();
        $notifiers = array( );
        foreach( $notifierClasses as $notifierClass ) {
            try {
                $notifier = $this->notifierBuilder->buildNotifier( $notifierClass );
                if( $notifier !== null )
                    $notifiers[] = $notifier;
            }
            catch( Exception $e ) {
                Yii::log( "Notifier $notifierClass caused an error at instantiation: {$e->getMessage()}", CLogger::LEVEL_ERROR, self::MY_LOG_CATEGORY );
            }
        }
        $this->notifiers = $notifiers;
    }

    // So it can be mocked!
    protected function getNotifierClasses(){
        return Yii::app()->params->alert_notifiers;
    }

}

// Notifier builder
class NotifierBuilder {
    const MY_LOG_CATEGORY = 'services.other.NotifierBuilder';

    public function buildNotifier( $notifierName ) {
        try {
            if( !class_exists( $notifierName ) ) {
                Yii::log( "($notifierName) class could not be found", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY );
                return null;
            }
            $instance = new $notifierName;
            if( !($instance instanceof Notifier ) ) {
                Yii::log( "($notifierName) class is not an instance of Notifier", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY );
                return null;
            }
            return $instance;
        }
        catch( Exception $e ) {
            Yii::log( "($notifierName) could not be built. Exception: $e", CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY );
            return null;
        }
    }

}

/**
 * Interface the alert notifier must implement
 */
interface Notifier {

    public function notify( $message, array $params = array( ) );
}

/**
 * Class for notifying via email
 */
class EmailNotifier implements Notifier {
    const NOTIFICATION_RECEIVERS = 'notification.receivers';
    const MY_LOG_CATEGORY = 'EmailNotifier';

    // Mail sender. Let's make the config static for one-time configuration
    static private $mail;
    static private $mailConfig;

    public function notify( $message, array $params = array( ) ) {
        if( !key_exists( self::NOTIFICATION_RECEIVERS, $params ) ) {
            Yii::log( 'No receivers available for notification', CLogger::LEVEL_WARNING, self::MY_LOG_CATEGORY );
            return false;
        }
        $mailConfig = $this->getMailConfig();
        $tos = $params[self::NOTIFICATION_RECEIVERS];
        $tos = (array)$tos;
        $tos = implode(', ',$tos);
        /*
        foreach( $tos as $to ) {
            $this->sendMail( $to, $mailConfig['subject'], $message );
        }*/
        $subject = $mailConfig['subject'];
        if(isset($params['replacements']['project.name'])){
            $subject .= " proyecto '{$params['replacements']['project.name']}'";
        }
        if(isset($params['replacements']['periodo_informe'])){
            $subject .= " informe '{$params['replacements']['periodo_informe']}' del cliente"
            . " '{$params['replacements']['client.name']}' y proyecto '{$params['replacements']['project.name']}'";
        }       
        
        $this->sendMail( $tos, $subject, $message );
    }

    protected function sendMail( $to, $subject, $message ) {
        $mailConfig = $this->getMailConfig();
        
		if( @mail( $to, $subject, $message, "Content-type: text/html;charset=utf-8\r\nFrom: {$mailConfig['from']}\n" ) !== TRUE ){
			throw new SendMailException();
		}
    }

    /**
     * @return
     */
    private function getMailConfig() {
        if( empty( self::$mailConfig ) ) {
            // Default values
            self::$mailConfig = Yii::app()->params->mail;
        }
        return self::$mailConfig;
    }

}

/**
 * Class for notifying to log
 */
class LogNotifier implements Notifier {
    const MY_LOG_CATEGORY = 'LogNotifier';

    public function notify( $message, array $params = array( ) ) {
        Yii::log( 'NOTIFYING ALERT', CLogger::LEVEL_INFO, self::MY_LOG_CATEGORY );
        Yii::log( "Message: $message", CLogger::LEVEL_INFO, self::MY_LOG_CATEGORY );
        Yii::log( 'Params: ' . print_r( $params, true ), CLogger::LEVEL_INFO, self::MY_LOG_CATEGORY );
    }
}

class SendMailException extends Exception{
	public function __construct(){
		parent::__construct("No se ha podido enviar el mail. Servidor no disponible.");
	}
}
?>
