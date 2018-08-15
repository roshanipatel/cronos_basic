<div class="row">
  <div class="col-lg-12">
	<h1 class="page-header">Imputar Gasto Proyecto</h1>
  </div>
</div>

<?php
echo $this->render( '/projectExpense/_form', [
    'model' => $model,
    'projects' => []
] );
?>