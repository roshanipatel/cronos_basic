<?php use yii\helpers\Html; ?>
<li><?php echo Html::a('Introducir horas', array('user-project-task/calendar')) ?></li>
<li><a href="javascript:void(0);">Consultar horas<span class="fa arrow"></span></a>
	<ul class="nav nav-second-level">
                <li><?php echo Html::a( 'Modificación Horas', array( 'user-project-task/update-tasks' ) ) ?></li>
	</ul>
</li>
<li><a href="javascript:void(0);"> 'Gastos<span class="fa arrow"></span></a>
	<ul class="nav nav-second-level">
		<li><?php echo Html::a( 'Imputar', array( 'project-expense/create' ) ) ?></li>
                <li><?php echo Html::a( 'Consultar', array( 'project-expense/expenses' ) ) ?></li>
	</ul>
</li>
