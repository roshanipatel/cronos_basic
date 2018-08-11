<?php
use yii\helpers\Html;
// Let's make some refactoring to the top menu
$topMenus = array( "user" => "Usuarios",
                   "company"=>"Empresas",
        );
?>
    <li>
        <a href="javascript:void(0);">Tareas<span class="fa arrow"></span></a>
            <ul class="nav nav-second-level">
                    <li><?php echo Html::a( 'Calendario', array( 'user-project-task/calendar' ) ) ?></li>
            </ul>
    </li>
    <li>
        <a href="javascript:void(0);">Proyectos<span class="fa arrow"></span></a>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Gestionar', array( 'project/project-overview' ) ) ?></li>
        </ul>
    </li>
    <li>
        <a href="javascript:void(0);">Operaciones portal<span class="fa arrow"></span></a>

        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Consultar Horas', array( 'user-project-task/search-tasks-commercial' ) ) ?></li>
            <li><?php echo Html::a( 'Modificación Horas', array( 'user-project-task/update-tasks' ) ) ?></li>
        </ul>
    </li>
    <li>
        <a href="javascript:void(0);">Gastos<span class="fa arrow"></span></a>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Imputar', array( 'project-expense/create' ) ) ?></li>
                <li><?php echo Html::a( 'Consultar', array( 'project-expense/expenses' ) ) ?></li>
	</ul>
    </li>
    

