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