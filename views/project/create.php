<?php
$this->registerJsFile(Yii::$app->request->BaseUrl .'/js/jquery-1.6.2.js',['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile(Yii::$app->request->BaseUrl .'/js/jquery-ui-1.8.8.custom.js',['position' => \yii\web\View::POS_HEAD]); 
?>

<div class="row">
  <div class="col-lg-12">
	<h1 class="page-header">Crear Proyecto</h1>
  </div>
</div>


<?php
echo $this->render( '_form',[
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