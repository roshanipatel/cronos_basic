<?php use yii\helpers\Html; ?>
<li><?php echo Html::a('Introducir horas', array('userProjectTask/calendar')) ?></li>
<li><?php echo Html::a('Consultar horas<span class="fa arrow"></span>', array('userProjectTask/searchTasksWorker')) ?>
	<ul class="nav nav-second-level">
                <li><?php echo Html::a( 'Modificación Horas', array( 'userProjectTask/updateTasks' ) ) ?></li>
	</ul>
</li>
<li><?php echo Html::a( 'Gastos<span class="fa arrow"></span>', array( 'projectExpense/expenses' ) ) ?>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Imputar', array( 'projectExpense/create' ) ) ?></li>
                <li><?php echo Html::a( 'Consultar', array( 'projectExpense/expenses' ) ) ?></li>
	</ul>
</li>
