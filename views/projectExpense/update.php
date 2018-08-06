<h1>Actualizar Gasto Proyecto <?php echo $model->project->name; ?></h1>

<?php
echo $this->render( '/projectExpense/form', [
    'model' => $model,
    'projects' => $projects
       ] );
?>