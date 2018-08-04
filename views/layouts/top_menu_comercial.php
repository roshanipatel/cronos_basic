<?php
use yii\helpers\Html;
// Let's make some refactoring to the top menu
$topMenus = array( "user" => "Usuarios",
                   "company"=>"Empresas",
        );
?>
    <li><?php echo Html::a( 'Tareas<span class="fa arrow"></span>', array( 'userProjectTask/calendar' ) ) ?>
            <ul class="nav nav-second-level">
                    <li><?php echo Html::a( 'Calendario', array( 'userProjectTask/calendar' ) ) ?></li>
            </ul>
    </li>
    <li><?php echo Html::a( 'Proyectos<span class="fa arrow"></span>', array( 'project/admin' ) ) ?>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Gestionar', array( 'project/projectOverview' ) ) ?></li>
        </ul>
    </li>
    <li><?php echo Html::a( 'Operaciones portal<span class="fa arrow"></span>', array( 'userProjectTask/searchTasksCommercial' ) ) ?>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Consultar Horas', array( 'userProjectTask/searchTasksCommercial' ) ) ?></li>
            <li><?php echo Html::a( 'Modificación Horas', array( 'userProjectTask/updateTasks' ) ) ?></li>
        </ul>
    </li>
    <li><?php echo Html::a( 'Gastos<span class="fa arrow"></span>', array( 'projectExpense/expenses' ) ) ?>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Imputar', array( 'projectExpense/create' ) ) ?></li>
                <li><?php echo Html::a( 'Consultar', array( 'projectExpense/expenses' ) ) ?></li>
	</ul>
    </li>
    

