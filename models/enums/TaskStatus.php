<?php

// Enum for task status
class TaskStatus extends Enum
{
    // Enum values
    const TS_NEW = 'NEW';
    const TS_APPROVED = 'APPROVED';
    const TS_REJECTED = 'REJECTED';
    const TS_ORPHAN = 'ORPHAN';

}

?>
