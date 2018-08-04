<h1>Actualizar Proyecto <?php echo $model->name; ?></h1>

<?php
echo Yii::$app->controller->renderPartial( '_form', [
    'model' => $model,
    'companies' => $companies,
    'projectManagers' => $projectManagers,
    'projectCommercials' => $projectCommercials,
    'projectCustomers' => $projectCustomers,
    'projectWorkers' => $projectWorkers,
    'projectImputetypes' => $projectImputetypes,
    'projectTargets' => $projectTargets
        ] );
?>