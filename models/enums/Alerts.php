<?php
namespace app\models\enums;

use app\commands\enums\Enum;
/**
 * Description of Alerts
 *
 * @author twocandles
 */
final class Alerts extends Enum
{
    const PROJECT_HOURS_WARNING = 'PRJ_HOURS_THRESHOLD';
    const PROJECT_HOURS_EXCEEDED = 'PRJ_HOURS_EXCEEDED';
    const TASK_REFUSED_BY_CUSTOMER = 'TSK_REFUSED_C';
    const PROJECT_CLOSED_OPERATIONAL = 'PRJ_CLOSED_OP';
    const PROJECT_CLOSED_COMMERCIAL = 'PRJ_CLOSED_COM';
    const PROJECT_OPENED_OPERATIONAL = 'PRJ_OPENED_OP';
    const PROJECT_OPENED_COMMERCIAL = 'PRJ_OPENED_COM';
    const USER_PROJECT_TASK_PENDING = 'USR_PRJ_TASK_PEND';
    const PROJECT_REPORTING = 'USR_PRJ_REPORT';

    const MESSAGE_REPLACEMENTS = 'replacements';

    static public function getDescriptions()
    {
        return array(
            Alerts::PROJECT_OPENED_COMMERCIAL => (
                <<<MSG
Mensaje automático generado por Cronos:

El proyecto {project.name} del cliente {customer.name} ha sido abierto a nivel comercial.
MSG
                ),
            
            Alerts::PROJECT_OPENED_OPERATIONAL => (
                <<<MSG
Mensaje automático generado por Cronos:

El proyecto {project.name} del cliente {customer.name} ha sido abierto a nivel operacional.
MSG
                ),
        Alerts::PROJECT_CLOSED_COMMERCIAL => (
                <<<MSG
Mensaje automático generado por Cronos:

El proyecto {project.name} del cliente {customer.name} ha sido cerrado a nivel comercial y ya puede ser facturado.
MSG
                ),
            
            Alerts::PROJECT_CLOSED_OPERATIONAL => (
                <<<MSG
Mensaje automático generado por Cronos:

El proyecto {project.name} del cliente {customer.name} ha sido cerrado a nivel operacional. Recuerde enviar la encuesta de calidad del proyecto   
MSG
                ),
            
            Alerts::PROJECT_HOURS_WARNING =>
                <<<MSG
Esto es un AVISO de CRONOS:

El proyecto {project.name} del cliente {customer.name} ha superado el umbral de horas de aviso.
Horas imputadas: {project.hours_after}
Umbral de aviso: {project.warn_hours_threshold}
MSG
                ,
            Alerts::PROJECT_HOURS_EXCEEDED =>
                <<<MSG
Esto es un AVISO de CRONOS:

El proyecto {project.name} del cliente {customer.name} ha superado LAS HORAS TOTALES asignadas al proyecto.
Horas imputadas: {project.hours_after}
Horas totales imputables: {project.max_hours}
MSG
                ,
            Alerts::TASK_REFUSED_BY_CUSTOMER =>
                <<<MSG
Esto es un AVISO de CRONOS:

La TAREA ({task.description}) del proyecto ({project.name}) ha sido RECHAZADA
por el usuario ({user.name})
MSG
                ,
            Alerts::USER_PROJECT_TASK_PENDING => 
                <<<MSG
Esto es un AVISO de CRONOS:

Por favor {user.name}, recuerde imputar las horas del periodo del {dia_inicio} al {dia_final}. 
    Se encuentran imputadas {horas_realizadas} sobre un total de {horas_teoricas}.
MSG
                ,
                Alerts::PROJECT_REPORTING => 
                <<<MSG
Esto es un AVISO de CRONOS:

Se le adjunta el informe {periodo_informe} del proyecto {project.name}.
                
                <a target="_blank" href="https://{server}/index.php/user-project-task/report?h={secure}&project={project}&open_time={range_ini}&close_time={range_fi}">Abrir informe</a>
MSG
        );
    }
}


?>
