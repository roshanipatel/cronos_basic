<?php
use yii\helpers\Html;
// Let's make some refactoring to the top menu
$topMenus = array( "user" => "Usuarios",
                   "company"=>"Empresas",
        );
?>
<?php foreach ( $topMenus as $controller => $desc ) { ?>
    <li><a href="javascript:void(0);"><?= $desc.'<span class="fa arrow"></span>' ?></a>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Crear', array( $controller.'/create' ) ) ?></li>
            <li><?php echo Html::a( 'Gestionar', array( $controller.'/admin' ) ) ?></li>
        </ul>
    </li>
<?php } ?>
    <li><a href="javascript:void(0);">Proyectos <span class="fa arrow"></span></a>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Crear', array( 'project/create' ) ) ?></li>
            <li><?php echo Html::a( 'Gestionar', array( 'project/project-overview' ) ) ?></li>
        </ul>
    </li>
    <li><a href="javascript:void(0);">Calendario<span class="fa arrow"></span></a>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Calendario laboral', array( 'user-project-task/calendar-upload' ) ) ?></li>
        </ul>
    </li>
    <li>
        <a href="javascript:void(0);">Operaciones portal<span class="fa arrow"></span></a>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Aprobar Horas', array( 'user-project-task/approve-tasks' ) ) ?></li>
            <li><?php echo Html::a( 'Modificación Horas', array( 'user-project-task/update-tasks' ) ) ?></li>
            <li><?php echo Html::a( 'Consultar Horas', array( 'user-project-task/search-tasks-admin' ) ) ?></li>
        </ul>
    </li>
    <li><a href="javascript:void(0);">Gastos<span class="fa arrow"></span></a>
	<ul class="nav nav-second-level">  
		<li><?php echo Html::a( 'Imputar', array( 'project-expense/create' ) ) ?></li>
                <li><?php echo Html::a( 'Consultar', array( 'project-expense/expenses' ) ) ?></li>
		<li><?php echo Html::a( 'Aprobar', array( 'project-expense/approve-expenses' ) ) ?></li>
	</ul>
    </li>
    <li><a href="javascript:void(0);">Reports<span class="fa arrow"></span> </a>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Actividad', array( 'report-task/activity' ) ) ?></li>
            <li><?php echo Html::a( 'Costes', array( 'report-task/cost' ) ) ?></li>
        </ul>
    </li>


