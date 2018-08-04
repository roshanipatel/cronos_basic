<h1>Crear Proyecto</h1>

<?php
echo Yii::$app->controller->renderPartial( '_form',[
    'model' => $model,
    'companies' => $companies,
    'projectManagers' => $projectManagers,
    'projectCustomers' => $projectCustomers,
    'projectWorkers' => $projectWorkers,
    'projectCommercials' => $projectCommercials,
    'projectImputetypes' => $projectImputetypes,
    'projectTargets' => $projectTargets
] );
?>