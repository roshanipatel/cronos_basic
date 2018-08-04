<h1>Imputar Gasto Proyecto</h1>

<?php
echo $this->renderPartial( '_form', array(
    'model' => $model,
    'projects' => array()
) );
?>