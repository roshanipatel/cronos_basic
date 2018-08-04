<?php
namespace app\models\enums;

use app\commands\enums\Enum;
/**
 * Enum with posible project status
 *
 * @author twocandles
 */
class ProjectStatus extends Enum
{
    const PS_OPEN = 'OPEN';
    const PS_CLOSED = 'CLOSED';
}
?>
