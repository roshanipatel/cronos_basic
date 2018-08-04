<h1>Actualizar Gasto Proyecto <?php echo $model->project->name; ?></h1>

<?php
echo $this->renderPartial( '_form', array(
    'model' => $model,
    'projects' => $projects
        ) );
?>