<?php use yii\helpers\Html; ?>
<li><?php echo Html::a( 'Tareas<span class="fa arrow"></span>', array( 'userProjectTask/calendar' ) ) ?>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Calendario', array( 'userProjectTask/calendar' ) ) ?></li>
		<li><?php echo Html::a( 'Consultar mis horas', array( 'userProjectTask/searchTasksWorker' ) ) ?></li>
                <li><?php echo Html::a( 'Calendario laboral', array( 'userProjectTask/calendarUpload' ) ) ?></li>
	</ul>
</li>
<li><?php echo Html::a( 'Gestión<span class="fa arrow"></span>', array( 'userProjectTask/approveTasks' ) ) ?>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Aprobar Horas', array( 'userProjectTask/approveTasks' ) ) ?></li>
                <li><?php echo Html::a( 'Modificación Horas', array( 'userProjectTask/updateTasks' ) ) ?></li>
		<li><?php echo Html::a( 'Consultar horas', array( 'userProjectTask/searchTasksManager' ) ) ?></li>
		<li><?php echo Html::a( 'Consultar proyectos', array( 'project/projectOverview' ) ) ?></li>                
	</ul>
</li>
<li><?php echo Html::a( 'Gastos<span class="fa arrow"></span>', array( 'projectExpense/expenses' ) ) ?>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Imputar', array( 'projectExpense/create' ) ) ?></li>
                <li><?php echo Html::a( 'Consultar', array( 'projectExpense/expenses' ) ) ?></li>
		<li><?php echo Html::a( 'Aprobar', array( 'projectExpense/approveExpenses' ) ) ?></li>
	</ul>
</li>


