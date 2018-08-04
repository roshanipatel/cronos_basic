<?php

/**
 * Description of CronosUtils
 *
 * @author twocandles
 */
class CronosUtils {

        const EMPTY_LABEL = "(Empty)";
    
	private function __construct() {
	}

	public static function getTicketURL($ticketId) {
		if(empty($ticketId)) {
			return "";
		} else {
			return str_replace("{ticket_id}", $ticketId, Yii::$app->params->ticket_url);
		}
	}
        
        public static function getTicketLink($ticketId) {
		if(empty($ticketId)) {
			return "";
		} else {
			$tickedId = str_replace("{ticket_id}", $ticketId, Yii::$app->params->ticket_url);
                        return Html::a("<img src='/images/gbjbifbh.png'></img>", $tickedId, array( "target" => "_blank"));
		}
	}
        
        public static function getEditLabel($ticketId, $iId) {
            if ($ticketId != "") {
                return CHTML::label($ticketId, "labticket[".$iId."]");
            } else {
                return CHTML::label(CronosUtils::EMPTY_LABEL, "labticket[".$iId."]");
            }
        }

}

?>
