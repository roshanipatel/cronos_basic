<h1>Actualizar Gasto Proyecto <?php echo $model->project->name; ?></h1>

<?php
echo Yii::$app->controller->renderPartial( '/projectExpense/form', [
    'model' => $model,
    'projects' => $projects
       ] );
?>