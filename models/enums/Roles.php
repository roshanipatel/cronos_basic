<?php
namespace app\models\enums;

use app\commands\enums\DBEnum;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Roles extends DBEnum
{
    const UT_ADMIN = 'Admin';
    const UT_DIRECTOR_OP = 'DirectorOp';
    const UT_WORKER = 'Imputador';
    const UT_PROJECT_MANAGER = 'Aprobador';
    const UT_CUSTOMER = 'Cliente';
    const UT_ADMINISTRATIVE = 'Administrativo';
    const UT_COMERCIAL = 'Comercial';
    
    public static function DESC_UT_ADMIN() { return 'Admin'; }
    public static function DESC_UT_CUSTOMER () { return  'Cliente'; }
    public static function DESC_UT_DIRECTOR_OP () { return  'Director de operaciones'; }
    public static function DESC_UT_PROJECT_MANAGER () { return  'Jefe de proyecto'; }
    public static function DESC_UT_WORKER () { return  'Trabajador'; }
    public static function DESC_UT_ADMINISTRATIVE () { return  'Administrativo'; }
    public static function DESC_UT_COMERCIAL () { return  'Comercial'; }

    protected function getDBField()
    {
        return "name";
    }

    protected function getDBTable()
    {
        return "authitem";
    }

    protected function getDBCondition()
    {
        return "type=2";
    }

}

?>
