<?php
namespace app\models\enums;

use app\commands\enums\Enum;
/**
 * Enum with posible project status
 *
 * @author twocandles
 */
class ReportingFreq extends Enum
{
    const FREQ_NONE = 'NONE';
    const FREQ_MENSUAL = 'MENSUAL';
    const FREQ_TRIMESTRAL = 'TRIMESTRAL';
    const FREQ_SEMESTRAL = 'SEMESTRAL';
    const FREQ_ANUAL = 'ANUAL';
    
    public static function getLabel($sLabel) { 
        switch($sLabel) {
            case ReportingFreq::FREQ_ANUAL: return "Anual";
            case ReportingFreq::FREQ_TRIMESTRAL: return "Trimestral";
            case ReportingFreq::FREQ_SEMESTRAL: return "Semestral";
            case ReportingFreq::FREQ_MENSUAL: return "Mensual";
            default: return "";
        }
    }
}
?>
