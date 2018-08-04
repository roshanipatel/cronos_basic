<h1>Consultar horas de proyectos</h1>

<?php
/* SEARCH FORM */

Yii::$app->controller->renderPartial('/userProjectTask/_searchForm',
	[
	'taskSearch' => $taskSearch,
	'projectsProvider' => $projectsProvider,
	'usersProvider' => $usersProvider,
        'managersProvider' => $managersProvider,
	'tasksProvider' => $tasksProvider,
	'searchFieldsToHide' => $searchFieldsToHide,
	'showExportButton' => $showExportButton,
	'actionURL' => $actionURL,
	'projectStatus' => NULL,
	'onlyManagedByUser' => $onlyManagedByUser,
        'projectImputetypes' => $projectImputetypes
]);

/* END SEARCH FORM */

$columns = array(
	array(
		'header' => 'Cliente',
		'name' => 'companyName',
		'value' => '$data->project->company->name',
	),
	'project.name:text:Proyecto',
	array(
		'header' => 'Perfil',
		'name' => 'profile_id',
		'value' => 'WorkerProfiles::toString( $data->profile_id )',
	),
        array(
		'header' => 'Tipo de imputación',
		'name' =>  'imputetypeName',
                'value' => '$data->imputetype->name',
	),
	array(
		'header' => 'Día',
		'name' => 'dateIni',
		'value' => '$data->frm_date_ini',
		'htmlOptions' => array(
			'style' => 'text-align: center',
		)
	),
	array(
		'header' => 'Horas',
		'value' => '$data->getFormattedHourRange()',
		'htmlOptions' => array(
			'style' => 'text-align: center',
		)
	),
	array(
		'header' => 'Duración',
		'value' => '$data->getFormattedDuration()',
		'htmlOptions' => array(
			'style' => 'text-align: center',
		)
	),
	array(
		'name' => 'ticket_id',
		'type' => 'raw',
		'value' => 'CHtml::link($data->ticket_id, CronosUtils::getTicketUrl($data->ticket_id), array("target"=>"_blank"))',
	),
	array(
		'header' => 'Tarea',
		'name' => 'task_description',
		'value' => '$data->task_description',
	),
	array(
		'header' => 'Extra',
		'name' => 'is_extra',
		'value' => '$data->is_extra ? "Sí" : "No"',
		'htmlOptions' => array(
			'style' => 'text-align: center; width: 30px',
		)
	)
);
if(Yii::$app->user->isAdmin()){
	$columns[] = array(
		'header' => 'Facturable',
		'name' => 'is_billable',
		'value' => '$data->is_billable ? "Sí" : "No"',
		'htmlOptions' => array(
			'style' => 'text-align: center; width: 30px',
		)
	);
//	$day = array(
//            'header' => 'Día',
//			'class' => 'CLinkColumn',
//			'labelExpression' => '$data->frm_date_ini',
//            'urlExpression' =>
//				'Yii::$app->createUrl("userProjectTask/calendar", array("timestamp" => $data->date_ini->getTimestamp(), "user" => $data->user_id))',
//            'htmlOptions' => array(
//                'style' => 'text-align: center',
//            )
//        );
} else {
//	$day = array(
//            'header' => 'Final',
//            'value' => '$data->frm_date_ini',
//            'htmlOptions' => array(
//                'style' => 'text-align: center',
//            )
//        );
}



	array_splice($columns, 2, 0,
	array( array(
		'header' => 'Project Manager',
		'name' => 'managerName',
	)));


	array_splice($columns, 2, 0,
	array( array(
		'header' => 'Imputador',
		'name' => 'worker.name',
	)));


    $this->widget('zii.widgets.grid.HeaderSummaryGridView', array(
        'id' => 'user-project-task-grid',
        'dataProvider' => $tasksProvider,
        'selectableRows' => 0,  
        'summaryText' => 'Mostrando {end} de {count} resultado(s)',
        'ajaxUpdate' => FALSE,
        'filter' => NULL,
        'columns' => $columns,
        'headerTitle' => 'Resumen horas por proyecto',
        'headerSummary' => $projectSummarized,
        'visibleColumns' => count($columns)
    ));
/*
    $this->widget('zii.widgets.grid.CGridView',
                    array(
            'ajaxUpdate' => FALSE,
            'id' => 'user-project-task-grid',
            'dataProvider' => $tasksProvider,
            'filter' => NULL,
            'selectableRows' => 0,
            'summaryText' => 'Mostrando {start}-{end} resultado(s)',
            'columns' => $columns,
    ));
*/
?>

<div class="box" style="text-align:center; margin-top: 15px">
	Total horas: <strong> <?php echo $projectHours ?></strong>
	<?php /* ?>
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	  Total coste: <strong> <?php echo $projectPrice . ' €' ?></strong>

	  <?php */ ?>
</div>