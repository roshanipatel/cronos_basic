<?php
use yii\helpers\Html;
// Let's make some refactoring to the top menu
$topMenus = array( "user" => "Usuarios",
                   "company"=>"Empresas",
        );
?>
<?php foreach ( $topMenus as $controller => $desc ) { ?>
    <li><?php echo Html::a( $desc'<span class="fa arrow"></span>', array( $controller.'/admin' ) ) ?>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Crear', array( $controller.'/create' ) ) ?></li>
            <li><?php echo Html::a( 'Gestionar', array( $controller.'/admin' ) ) ?></li>
        </ul>
    </li>
<?php } ?>
    <li><?php echo Html::a( 'Proyectos <span class="fa arrow"></span>', array( 'project/admin' ) ) ?>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Crear', array( 'project/create' ) ) ?></li>
            <li><?php echo Html::a( 'Gestionar', array( 'project/projectOverview' ) ) ?></li>
        </ul>
    </li>
    <li><?php echo Html::a( 'Calendario<span class="fa arrow"></span>', array( 'userProjectTask/calendar' ) ) ?>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Calendario laboral', array( 'userProjectTask/calendarUpload' ) ) ?></li>
        </ul>
    </li>
    <li><?php echo Html::a( 'Operaciones portal<span class="fa arrow"></span>', array( 'userProjectTask/approveTasks' ) ) ?>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Aprobar Horas', array( 'userProjectTask/approveTasks' ) ) ?></li>
            <li><?php echo Html::a( 'Modificación Horas', array( 'userProjectTask/updateTasks' ) ) ?></li>
            <li><?php echo Html::a( 'Consultar Horas', array( 'userProjectTask/searchTasksAdmin' ) ) ?></li>
        </ul>
    </li>
    <li><?php echo Html::a( 'Gastos<span class="fa arrow"></span>', array( 'projectExpense/expenses' ) ) ?>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Imputar', array( 'projectExpense/create' ) ) ?></li>
                <li><?php echo Html::a( 'Consultar', array( 'projectExpense/expenses' ) ) ?></li>
		<li><?php echo Html::a( 'Aprobar', array( 'projectExpense/approveExpenses' ) ) ?></li>
	</ul>
    </li>
    <li><?php echo Html::a( 'Reports<span class="fa arrow"></span>', array() ) ?>
        <ul class="nav nav-second-level">
            <li><?php echo Html::a( 'Actividad', array( 'reportTask/activity' ) ) ?></li>
            <li><?php echo Html::a( 'Costes', array( 'reportTask/cost' ) ) ?></li>
        </ul>
    </li>


