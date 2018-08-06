<h1>Imputar Gasto Proyecto</h1>

<?php
echo Yii::$app->controller->renderPartial( '/projectExpense/_form', [
    'model' => $model,
    'projects' => []
] );
?>