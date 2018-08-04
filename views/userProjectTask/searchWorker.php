<h1>Consultar mis horas</h1>

<?php
/* SEARCH FORM */

$this->renderPartial( '_searchForm',
        array(
            'taskSearch' => $taskSearch,
            'projectsProvider' => $projectsProvider,
            'usersProvider' => $usersProvider,
            'tasksProvider' => $tasksProvider,
            'searchFieldsToHide' => $searchFieldsToHide,
            'showExportButton' => $showExportButton,
            'actionURL' => $actionURL,
			'projectStatus' => NULL,
			'onlyManagedByUser' => FALSE,
        ) );

/* END SEARCH FORM */

            $this->widget( 'zii.widgets.grid.CGridView', array(
                'id' => 'user-project-task-grid',
                'dataProvider' => $tasksProvider,
                'filter' => null,
                'selectableRows' => 0,
                'summaryText' => 'Mostrando {end} de {count} resultado(s)',
                'columns' => array(
                    array(
                        'name' => 'Cliente',
                        'value' => '$data->project->company->name',
                    ),
                    'project.name:text:Proyecto',
                    array(
                        'name' => 'Fecha inicial',
                        'value' => 'PHPUtils::convertDateToLongString( $data->date_ini )',
                    ),
                    array(
                        'name' => 'Fecha final',
                        'value' => 'PHPUtils::convertDateToLongString( $data->date_end )',
                    ),
                    // Duration instead of cost!!
                    array(
                        'name' => 'Duraci&ograve;n (horas)',
                        'value' => '$data->getFormattedDuration()',
                        'htmlOptions' => array(
                            'style' => 'text-align: center',
                        ),
                    ),
                    array(
						'header' => 'Ticket',
						'class' => 'CLinkColumn',
						'labelExpression' => '$data->ticket_id',
						'urlExpression' => 'CronosUtils::getTicketUrl($data->ticket_id);',
						'linkHtmlOptions' => array( 'target' => '_blank' ),
                    ),
                    array(
                        'name' => 'Tarea',
                        'value' => '$data->task_description',
                        'sortable' => 'false',
                    ),
                ),
                    ) );
?>

<div class="box" style="text-align:center; margin-top: 15px">
	Total horas: <strong> <?php echo $projectHours ?></strong>
</div>