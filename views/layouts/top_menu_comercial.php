<?php
use yii\helpers\Html;
// Let's make some refactoring to the top menu
$topMenus = array( "user" => "Usuarios",
                   "company"=>"Empresas",
        );
?>
    <li><?php echo Html::a( 'Tareas<span class="fa arrow"></span>', array( 'user-project-task/calendar' ) ) ?>
            <ul class="nav nav-second-level">
                    <li><?php echo Html::a( 'Calendario', array( 'user-project-task/calendar' ) ) ?></li>
            </ul>
    </li>
    <li><?php echo Html::a( 'Proyectos<span class="fa arrow"></span>', array( 'project/admin' ) ) ?>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Gestionar', array( 'project/project-overview' ) ) ?></li>
        </ul>
    </li>
    <li><?php echo Html::a( 'Operaciones portal<span class="fa arrow"></span>', array( 'user-project-task/search-tasks-commercial' ) ) ?>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Consultar Horas', array( 'user-project-task/search-tasks-commercial' ) ) ?></li>
            <li><?php echo Html::a( 'Modificación Horas', array( 'user-project-task/update-tasks' ) ) ?></li>
        </ul>
    </li>
    <li><?php echo Html::a( 'Gastos<span class="fa arrow"></span>', array( 'projectExpense/expenses' ) ) ?>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Imputar', array( 'projectExpense/create' ) ) ?></li>
                <li><?php echo Html::a( 'Consultar', array( 'projectExpense/expenses' ) ) ?></li>
	</ul>
    </li>
    

