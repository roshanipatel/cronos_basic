<?php

/**
 * Description of WorkerProfiles
 *
 * @author twocandles
 */
class WorkerProfiles extends DBEnum
{
    const WP_JUNIOR = '1_JUNIOR';
    const WP_SENIOR = '2_SENIOR';
    const WP_ARCHITECT = '3_ARCH';

    protected function getDBField()
    {
        return 'id';
    }

    protected function  getDBTable()
    {
        return WorkerProfile::model()->tableName();
    }
}
?>