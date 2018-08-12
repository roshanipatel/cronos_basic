<?php use yii\helpers\Html; ?>
<li><a href="javascript:void(0);">Tareas<span class="fa arrow"></span></a>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Calendario', array( 'user-project-task/calendar' ) ) ?></li>
		<li><?php echo Html::a( 'Consultar mis horas', array( 'user-project-task/search-tasks-worker' ) ) ?></li>
                <li><?php echo Html::a( 'Calendario laboral', array( 'user-project-task/calendar-upload' ) ) ?></li>
	</ul>
</li>
<li><a href="javascript:void(0);">Gestión<span class="fa arrow"></span></a>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Aprobar Horas', array( 'user-project-task/approve-tasks' ) ) ?></li>
                <li><?php echo Html::a( 'Modificación Horas', array( 'user-project-task/update-tasks' ) ) ?></li>
		<li><?php echo Html::a( 'Consultar horas', array( 'user-project-task/search-tasks-manager' ) ) ?></li>
		<li><?php echo Html::a( 'Consultar proyectos', array( 'project/project-overview' ) ) ?></li>                
	</ul>
</li>
<li><a href="javascript:void(0);">Gastos<span class="fa arrow"></span></a>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Imputar', array( 'project-expense/create' ) ) ?></li>
                <li><?php echo Html::a( 'Consultar', array( 'project-expense/expenses' ) ) ?></li>
		<li><?php echo Html::a( 'Aprobar', array( 'project-expense/approve-expenses' ) ) ?></li>
	</ul>
</li>


