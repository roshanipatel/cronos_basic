<?php 
use yii\helpers\Html;
use fedemotta\datatables\DataTables;
use common\components\Common;
use app\models\enums\Roles;
?>
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Calendario Laboral</h1>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Calendario Laboral
            </div>
            <div class="panel-body">
                <label style="color: red"><?php echo $errorMessage ?></label>
                <form action=<?= Yii::$app->urlManager->createUrl(['user-project-task/calendar-upload'])?> method="post" enctype="multipart/form-data">
                    <input type="file" name="calendarUploadFile"/>
                    <input type="submit" value="Cargar calendario"/>
                </form>
                <?php
                echo DataTables::widget([
                    'id' => 'dataTables-example',
                    'dataProvider' => $model->search(),
                    'filterModel' => $model,
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
                ]);
                ?>
            </div>
        </div>
    </div>
</div>


<h1></h1>

<div id="task-calendar"></div>



