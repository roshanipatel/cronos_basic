<h1>Imputar Gasto Proyecto</h1>

<?php
echo $this->render( '/projectExpense/_form', [
    'model' => $model,
    'projects' => []
] );
?>