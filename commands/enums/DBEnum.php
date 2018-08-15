<?php
namespace app\commands\enums;

use Yii;
use app\commands\enums\Enum;
/**
 * Description of DBEnum
 *
 * @author twocandles
 */
abstract class DBEnum extends Enum
{
    static private $MY_LOG_CATEGORY = 'components.enums.DBEnum';
    // Flag to check if integrity db has been checked, only necessary
    // if enums are stored in DB. Default implementation never check
    private $_isDBChecked;

    // Returns if $value is a valid enum value
    protected function _isValidValue( $value )
    {
        if( !isset( $this->_isDBChecked ) )
            $this->_checkDBIntegrity();

        return parent::_isValidValue( $value );
    }

    // Returns an array with the enum values (for validation)
    protected function _getValidValues()
    {
        if( !isset( $this->_isDBChecked ) )
            $this->_checkDBIntegrity();

        return parent::_getValidValues();
    }

    // Returns an array ready to be used by a dropdown box
    protected function _getDataForDropDown()
    {
        if( !isset( $this->_isDBChecked ) )
            $this->_checkDBIntegrity();

        return parent::_getDataForDropDown();
    }

    private function _isEnumValueInDBResults( $value, $dbResults )
    {
        foreach( $dbResults as $result )
        {
            if( $value == $result[$this->getDBField()] )
                return true;
        }
        return false;
    }

    /**
     * Check date integrity for enum in DB
     * @return boolean
     */
    protected function _checkDBIntegrity()
    {
        // Get enum values in DB
        $command = (new \yii\db\Query())
                        ->select( $this->getDBField() )
                        ->from( $this->getDBTable() );
        // Let's see if there's a condition
        if( $this->getDBCondition() != '' )
            $command->where( $this->getDBCondition() );
        // Query values
        $dbValues = $command->all();;
        // Get declared enum values
        $enumValues = parent::_getValidValues();
        // Check that number of items match
        //$dbVals = implode(";",$dbValues);
        //$enumVals = implode(";",$enumValues);
        if( count( $enumValues ) != count( $dbValues ) )
        {
            Yii::error( 'Enum "' . $this->_getEnumName() . '" integrity failed. Enum count values mismatch',__METHOD__ );
            throw new Exception( "Failed integrity check for enum in DB");
        }
        // Check that all constants are inside the DB
        // Hard to reproduce since it's impossible if the Enum value is a PK
        // in the table
        foreach( $enumValues as $value )
        {
            if( !( $this->_isEnumValueInDBResults( $value, $dbValues ) ) )
            {
                Yii::error( 'Enum "' . $this->_getEnumName() . '" integrity failed. Value "' . $value . '" not found in DB', __METHOD__ );
                throw new Exception( "Failed integrity check for enum in db" );
            }
        }
        // Check that all db values are valid enum constants
        foreach( $dbValues as $value )
        {
            if( !parent::_isValidValue( $value[$this->getDBField()] ) )
            {
                Yii::error( 'Enum "' . $this->_getEnumName() . '" integrity failed. Value "' . $value . '" not found in Enum', __METHOD__ );
                throw new Exception( "Failed integrity check for enum in db" );
            }
        }
        $this->_isDBChecked = true;
        return true;
    }

    protected abstract function getDBField();

    protected abstract function getDBTable();

    protected function getDBCondition()
    {
        return "";
    }

}

?>
