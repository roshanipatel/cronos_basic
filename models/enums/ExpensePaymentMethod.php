<?php
namespace app\models\enums;

use app\commands\enums\Enum;
/**
 * Enum with posible project status
 *
 * @author cescribano
 */
class ExpensePaymentMethod extends Enum
{
    const METHOD_CASH = 'CASH';
    const METHOD_CARD = 'CARD';
}
?>

