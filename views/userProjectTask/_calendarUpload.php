<h1>Calendario Laboral</h1>

<div id="task-calendar"></div>
<label style="color: red"><?php echo $errorMessage ?></label>

<form action=<?= Yii::$app->urlManager->createUrl(['user-project-task/calendar-upload'])?> method="post" enctype="multipart/form-data">
    <input type="file" name="calendarUploadFile"/>
    <input type="submit" value="Cargar calendario"/>
</form>


<?php
$this->widget( 'zii.widgets.grid.CGridView', array(
    'id' => 'calendar-grid',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'selectableRows' => 0,
    'summaryText' => 'Mostrando {start}-{end} resultado(s)',
    'columns' => array(
        array(
            'header' => 'Día',
            'name' => 'day',
            'value' => '$data->day',
        ),
        array(
            'header' => 'Ámbito festivo',
            'name' => 'city',
            'value' => 'Calendar::toString($data->city)',
        )
    ),
) );
?>
