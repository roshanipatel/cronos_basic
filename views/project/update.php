<h1>Actualizar Proyecto <?php echo $model->name; ?></h1>

<?php
echo $this->renderPartial( '_form', array(
    'model' => $model,
    'companies' => $companies,
    'projectManagers' => $projectManagers,
    'projectCommercials' => $projectCommercials,
    'projectCustomers' => $projectCustomers,
    'projectWorkers' => $projectWorkers,
    'projectImputetypes' => $projectImputetypes,
    'projectTargets' => $projectTargets
        ) );
?>