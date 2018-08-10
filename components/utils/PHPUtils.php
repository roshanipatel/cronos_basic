<?php
namespace app\components\utils;

use Yii;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class PHPUtils {
    const MYSQL_DATE_FORMAT_ON_CONVERSION = 'Y-m-d H:i:s';
    const DATE_FORMAT_ON_CONVERSION = 'd/m/Y';
    const DATE_TIME_FORMAT_ON_CONVERSION = 'd/m/Y H:i';
    const HOUR_TIME_ON_CONVERSION = 'H:i';
    const PHP_TO_JS_TIMESTAMP_FACTOR = 1000;

    /**
     * Remove the hour time.
     * @param type $date
     * @return type 
     */
    public static function removeHourPartFromDate($date) {
	if(!empty($date)) {
		$dt = PHPUtils::convertStringToPHPDateTime($date);
		return $dt->format(PHPUtils::DATE_FORMAT_ON_CONVERSION);
	} else {
		return $date;
	}
    }
    
    public static function extractHourFromDateTime($date) {
	if(!empty($date)) {
		$dt = PHPUtils::convertStringToPHPDateTime($date);
		return $dt->format(PHPUtils::HOUR_TIME_ON_CONVERSION);
	} else {
		return $date;
	}
    }
    
    /**
     * @param mixed
     * @return string
     */
    public static function print_rToString( $v ) {
        return print_r( $v, true );
    }

    /*     * ****************************************************** */

    // ATTENTION: this is hardcoded to MySQL format!!
    /**
     *
     * @param DateTime $dt
     * @return string
     */
    public static function convertPHPDateTimeToDBDateTime( DateTime $dt ) {
        return $dt->format( self::MYSQL_DATE_FORMAT_ON_CONVERSION );
    }

    /**
     * @param string $dt
     * @return DateTime
     */
    public static function convertDBDateTimeToPHPDateTime( $dt ) {
        assert( ( is_string( $dt ) ) && (!empty( $dt ) ) );
        return \DateTime::createFromFormat( self::MYSQL_DATE_FORMAT_ON_CONVERSION, $dt );
    }

    /**
     * Converts a string in the form 'd/m/Y H:i' to a DateTime PHP object
     * @param string $dt
     * @return DateTime
     */
    public static function convertStringToPHPDateTime( $dt ) {
        assert( ( is_string( $dt ) ) && (!empty( $dt ) ) );

        $format = self::DATE_TIME_FORMAT_ON_CONVERSION . ':s';
        if( substr_count( $dt, ":" ) == 0 ) {
            $dt .= ' 00:00:00';
        }
        // Add seconds if : appears more than once
        else if( substr_count( $dt, ":" ) == 1 ){
            $dt .= ':00';
        }
        $tempDateTime = \DateTime::createFromFormat( $format, $dt );
        if( ($tempDateTime === FALSE ) ) {
            Yii::error( 'Error convirtiendo fechas ' . $dt, 'utils.PHPUtils' );
        }
        else{

        }
        return $tempDateTime;
    }

    /**
     * @param string $dt
     * @return string
     */
    public static function convertStringToDBDateTime( $dt ) {
        
        assert( ( is_string( $dt ) ) && (!empty( $dt ) ) );
        //echo self::convertStringToPHPDateTime( $dt );die;
        return self::convertPHPDateTimeToDBDateTime( self::convertStringToPHPDateTime( $dt ) );
    }

    /**
     * @param string $dt
     * @return string
     */
    public static function convertDBDateTimeToString( $dt ) {
        assert( ( is_string( $dt ) ) && (!empty( $dt ) ) );
        return self::converPHPDateTimeToString( self::convertDBDateTimeToPHPDateTime( $dt ) );
    }

    /**
     * @param DateTime $dt
     * @return string
     */
    public static function converPHPDateTimeToString( $dt ) {
        assert( !empty( $dt ) && is_object( $dt ) );
        return $dt->format( self::DATE_TIME_FORMAT_ON_CONVERSION );
    }

    /**
     * @return string
     */
    public static function getNowAsString( $withSeconds = false ) {
        $format = self::DATE_TIME_FORMAT_ON_CONVERSION;
        if( $withSeconds !== false )
            $format .= ':s';
        return date( $format );
    }

    /**
     * Retrieves an array from a HTML select. If nothing is selected the attribute is not
     * set, so this function circumvents that
     * @return boolean
     */
    static function getArrayFromSelect( $select ) {
        return is_array( $select ) ? $select : array( );
    }

    static private function getHoursBetweenDatesDateTime( DateTime $dateIni, DateTime $dateEnd ) {
        $diff = abs( $dateEnd->getTimestamp() - $dateIni->getTimestamp() );
        // Convert to hours
        return ((float)$diff) / (60 * 60);
    }

    static private function getHoursBetweenDatesString( $dateIni, $dateEnd ) {
        assert( is_string( $dateIni ) );
        assert( is_string( $dateEnd ) );
        $di = self::convertDBDateTimeToPHPDateTime( $dateIni );
        // If can't be converted from DB format, try string format
        if( !$di ) {
            $di = self::convertStringToPHPDateTime( $dateIni );
            $de = self::convertStringToPHPDateTime( $dateEnd );
        }
        else
            $de = self::convertDBDateTimeToPHPDateTime( $dateEnd );
        if( !$di || !$de )
            return 0;
        return self::getHoursBetweenDatesDateTime( $di, $de );
    }

    /**
     * @param mixed $dateIni
     * @param mixed $dateEnd
     * @return float
     */
    static function getHoursBetweenDates( $dateIni, $dateEnd ) {
        if( is_string( $dateIni ) ) {
            return self::getHoursBetweenDatesString( $dateIni, $dateEnd );
        }
        else {
            return self::getHoursBetweenDatesDateTime( $dateIni, $dateEnd );
        }
    }

    /**
     * @param DateTime $dt
     * @return string
     */
    static function convertDateToLongString( $dt ) {
        assert( $dt instanceof DateTime );
        return $dt->format( self::DATE_TIME_FORMAT_ON_CONVERSION );
    }
    
    /**
     * 
     * @param DateTime $dt
     * @return type
     */
    static function convertDateToShortString( $dt ) {
        assert( $dt instanceof DateTime );
        return $dt->format( self::DATE_FORMAT_ON_CONVERSION );
    }

    /**
     * Given 2 datetimes returns an array with the form array( "hours" => hours, "minutes" => minutes )
     * with the diference between the two
     * @param DateTime $dateIni
     * @param DateTime $dateEnd
     * @return array
     */
    static function getHoursAndMinutesBetweenDates( DateTime $dateIni, DateTime $dateEnd ) {
        $diff = self::getHoursBetweenDatesDateTime( $dateIni, $dateEnd );
        $result = array( );
        $result["hours"] = intval( $diff );
        $result["minutes"] = round( ( $diff - $result["hours"] ) * 60 );
        return $result;
    }

    /**
     * This function removes a directory and its contents.
     * @param string $dir Directory to delete
     */
    static function rmDirContentsRecursive( $dir ) {
        if( !is_dir( $dir ) ) {
            echo "$dir is not a valid dir<br>\n";
            return false;
        }

        $files = scandir( $dir );
        array_shift( $files );    // remove '.' from array
        array_shift( $files );    // remove '..' from array

        echo "Deleting $dir contents<br>\n";

        foreach( $files as $file ) {
            $file = $dir . '/' . $file;
            echo "Deleting $file<br>\n";
            if( is_dir( $file ) ) {
                self::rmDirContentsRecursive( $file );
                if( !rmdir( $file ) )
                    echo "Error deleting directory $file<br>\n";
            }
            else {
                if( !unlink( $file ) )
                    echo "Error deleting file $file<br>\n";
            }
        }
    }

    /**
     * Returns a DateTime object representing the start of the week passed as parameter
     * @param type $date
     * @return DateTime
     */
    static public function getStartOfWeek( $date ) {
        self::ensureGetXXXOfWeekParameter( $date );
        $dayOfWeek = (int)$date->format( 'w' );
        if( $dayOfWeek == 1 ) {
            $deviation = 'today';
        }
        else {
            $deviation = 'last monday';
        }
        $date->modify( $deviation );
        $date->setTime( 0, 0, 0 );
        return $date;
    }

    /**
     * Returns a DateTime object representing the start of the week passed as parameter
     * @param type $date
     * @return DateTime
     */
    static public function getEndOfWeek( $date ) {
        self::ensureGetXXXOfWeekParameter( $date );
        $dayOfWeek = (int)$date->format( 'w' );
        if( $dayOfWeek == 0 ) {
            $deviation = 'today';
        }
        else {
            $deviation = 'next sunday';
        }
        $date->modify( $deviation );
        $date->setTime( 23, 59, 59 );
        return $date;
    }

    /**
     * @param mixed $date
     */
    static private function ensureGetXXXOfWeekParameter( &$date ) {
        if( is_string( $date ) ) {
            $date = self::convertStringToPHPDateTime( $date );
            if( $date === FALSE ) {
                $date = new DateTime();
            }
        }
        else if( !( $date instanceof DateTime ) ) {
            throw new InvalidArgumentException( 'Invalid date passed as parameter. Expecting either string or DateTime' );
        }
    }

    static public function addHourToDateIfNotPresent( $date, $hour ){
        if( strpos( $date, ":" ) === FALSE )
            return "$date $hour";
        else
            return $date;
    }

}

?>
