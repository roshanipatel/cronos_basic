<h1>Crear Proyecto</h1>

<?php
echo $this->renderPartial( '_form', array(
    'model' => $model,
    'companies' => $companies,
    'projectManagers' => $projectManagers,
    'projectCustomers' => $projectCustomers,
    'projectWorkers' => $projectWorkers,
    'projectCommercials' => $projectCommercials,
    'projectImputetypes' => $projectImputetypes,
    'projectTargets' => $projectTargets
) );
?>