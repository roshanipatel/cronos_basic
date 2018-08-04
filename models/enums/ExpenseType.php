<?php
namespace app\models\enums;

use app\commands\enums\Enum;
/**
 * Enum with posible project status
 *
 * @author twocandles
 */
class ExpenseType extends Enum
{
    const EXP_DIETAS = 'DIETAS';
    const EXP_TAXI = 'TAXI';
    const EXP_TRANSPORTE_PUBLICO = 'TRANSPORTE';
    const EXP_TRANSPORTE = 'TREN_AVION';
    const EXP_KM = 'KILOMETRAJE';
    const EXP_PARKING = 'PARKING';
    const EXP_ALOJAMIENTO = 'ALOJAMIENTO';
    const EXP_PEAJES = 'PEAJES';
    const EXP_OTROS = 'MATERIAL';
}
?>