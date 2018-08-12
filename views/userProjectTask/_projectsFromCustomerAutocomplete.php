<?php
/**
 * @param string $projectStatus [optional]
 * @param string $projectStatusCom [optional]
 * @param bool $managedByUser [optional]
 */
assert(isset($onlyManagedByUser));

echo $this->registerJsFile(Yii::$app->request->BaseUrl .'/js/ajax_loading_image.js',['position' => \yii\web\View::POS_HEAD]);

?>

<script type="text/javascript">

    var ajaxLoadingProjects;
    
    /**
     * 
     */
    function updateProjects(companyId, options, ajaxLoadingProjects) {
        
        if (companyId == "") {
            companyId = $("#company_id").val();
        }
        
        jQuery.ajax( {
                    'url':options['urlProjectsFromCustomer'],
                    'data': {
                        customerId: companyId,
                        startFilter: function() {
                            var startDate = "";
                            //Búsqueda en Consultar horas
                            if ($("#TaskSearch_dateIni").val() != undefined) {
                                startDate = $("#TaskSearch_dateIni").val();
                                //Busqueda en proyectos
                            } else if ($("#Project_open_time").val() != undefined) {
                                startDate = $("#Project_open_time").val();
                            } else if ($("#ExpenseSearch_dateIni").val() != undefined) {
                                startDate = $("#ExpenseSearch_dateIni").val();
                            } else if ($("#ProjectExpense_date_ini").val() != undefined) {
                                startDate = $("#ProjectExpense_date_ini").val();
                            }
                            return startDate;
                        },
                        endFilter: function() {
                            var endDate = "";
                            if ($("#TaskSearch_dateEnd").val() != undefined) {
                                endDate = $("#TaskSearch_dateEnd").val();
                            } else if ($("#Project_close_time").val() != undefined) {
                                endDate = $("#Project_close_time").val();
                            } else if ($("#ExpenseSearch_dateEnd").val() != undefined) {
                                endDate = $("#ExpenseSearch_dateEnd").val();
                            } 
                            return endDate;
                        },
                        selectProjectPrompt: 'Todos',
						onlyManagedByUser: <?php echo ($onlyManagedByUser) ? "1" : "0" ?>
						<?php if( !empty($projectStatus) ){
							echo ", projectStatus: '$projectStatus'";
						} ?>
                                                <?php if( !empty($projectStatusCom) ){
							echo ", projectStatusCom: '$projectStatusCom'";
						} ?>,             
                                               onlyUserEnvolved: <?php echo (isset($onlyUserEnvolved) && $onlyUserEnvolved) ? "1" : "0" ?>
                    },
                    'dataType':'html',
                    'cache':false,
                    'success':function(html){
                        jQuery(options['projectSelectSelector']).html(html);
                        jQuery(options['companyIdInputSelector']).attr('value', companyId);
                        ajaxLoadingProjects.hide();
                    },
                    'error':function(){
                        jQuery(options['projectSelectSelector'])
                            .empty()
                            .append('<option value="">' + options['promptAllProjects'] + '</option>');

                    },
                    'complete':function(){
                        ajaxLoadingProjects.hide();
                    }
                });
    }
    
    function updateWorkers(options, ajaxWorkers) {
        
        jQuery.ajax( {
                    'url':options['urlWorkers'],
                    'data': {
                        startFilter: function() {
                            var startDate = "";
                            //Búsqueda en Consultar horas
                            if ($("#TaskSearch_dateIni").val() != undefined) {
                                startDate = $("#TaskSearch_dateIni").val();
                                //Busqueda en proyectos
                            } else if ($("#Project_open_time").val() != undefined) {
                                startDate = $("#Project_open_time").val();
                            } else if ($("#ExpenseSearch_dateIni").val() != undefined) {
                                startDate = $("#ExpenseSearch_dateIni").val();
                            }
                            return startDate;
                        },
                        endFilter: function() {
                            var endDate = "";
                            if ($("#TaskSearch_dateEnd").val() != undefined) {
                                endDate = $("#TaskSearch_dateEnd").val();
                            } else if ($("#Project_close_time").val() != undefined) {
                                endDate = $("#Project_close_time").val();
                            } else if ($("#ExpenseSearch_dateEnd").val() != undefined) {
                                endDate = $("#ExpenseSearch_dateEnd").val();
                            }
                            return endDate;
                        },
                        selectWorkersPrompt: 'Todos',
                        onlyManagedByUser: <?php echo ($onlyManagedByUser) ? "1" : "0" ?>
                    },
                    'dataType':'html',
                    'cache':false,
                    'success':function(html){
                        jQuery(options['workerSelectSelector']).html(html);
                        ajaxWorkers.hide();
                    },
                    'error':function(){
                        jQuery(options['workerSelectSelector'])
                            .empty()
                            .append('<option value="">' + options['promptAllWorkers'] + '</option>');

                    },
                    'complete':function(){
                        ajaxWorkers.hide();
                    }
                });
    }
    
    function updateManagers(options, ajaxManagers) {
        
        jQuery.ajax( {
                    'url':options['urlManagers'],
                    'data': {
                        startFilter: function() {
                            var startDate = "";
                            //Búsqueda en Consultar horas
                            if ($("#TaskSearch_dateIni").val() != undefined) {
                                startDate = $("#TaskSearch_dateIni").val();
                                //Busqueda en proyectos
                            } else if ($("#Project_open_time").val() != undefined) {
                                startDate = $("#Project_open_time").val();
                            } else if ($("#ExpenseSearch_dateIni").val() != undefined) {
                                startDate = $("#ExpenseSearch_dateIni").val();
                            }
                            return startDate;
                        },
                        endFilter: function() {
                            var endDate = "";
                            if ($("#TaskSearch_dateEnd").val() != undefined) {
                                endDate = $("#TaskSearch_dateEnd").val();
                            } else if ($("#Project_close_time").val() != undefined) {
                                endDate = $("#Project_close_time").val();
                            } else if ($("#ExpenseSearch_dateEnd").val() != undefined) {
                                endDate = $("#ExpenseSearch_dateEnd").val();
                            }
                            return endDate;
                        },
                        selectWorkersPrompt: 'Todos'
                    },
                    'dataType':'html',
                    'cache':false,
                    'success':function(html){
                        jQuery(options['managerSelectSelector']).html(html);
                        ajaxManagers.hide();
                    },
                    'error':function(){
                        jQuery(options['managerSelectSelector'])
                            .empty()
                            .append('<option value="">' + options['promptAllManagers'] + '</option>');

                    },
                    'complete':function(){
                        ajaxManagers.hide();
                    }
                });
    }
    
    function defineAutocompleteCustomers(options)
    {
        if( ! options )
            options = new Object();
        if( ! options['loadingProjectsImgId'] )
            options['loadingProjectsImgId'] = '#loadingProjects';
        if( ! options['loadingCustomersImgId'] )
            options['loadingCustomersImgId'] = '#loadingCustomers';
        if( ! options['loadingWorkersImgId'] )
            options['loadingWorkersImgId'] = '#loadingWorkers';
        if( ! options['loadingManagersImgId'] )
            options['loadingManagersImgId'] = '#loadingManagers';
        if( ! options['companyIdInputSelector'] )
        {
            alert('Missing company id input selector. Autocomplete disabled.');
            return false;
        }
        if( ! options['companyNameInputSelector'] )
        {
            alert('Missing company name input selector. Autocomplete disabled.');
            return false;
        }
        if( ! options['projectSelectSelector'] )
        {
            alert('Missing project select selector. Autocomplete disabled.');
            return false;
        }
        if( ! options['srcImageAJAXLoader'] )
            options['srcImageAJAXLoader'] = 'images/ajax-loader-2.gif';
        if( ! options['companiesForAutocomplete'] )
            options['companiesForAutocomplete'] = '<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveCustomersByTermForAutocomplete' ) ?>';
        if( ! options['urlProjectsFromCustomer'] )
            options['urlProjectsFromCustomer'] = '<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveProjectsFromCustomerIdAsListOptions' ) ?>';
        if( ! options['urlWorkers'] )
            options['urlWorkers'] = '<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveWorkers' ) ?>';
        if( ! options['urlManagers'] )
            options['urlManagers'] = '<?php echo Yii::$app->urlManager->createUrl( 'AJAX/retrieveManagers' ) ?>';
        if( ! options['promptNoProjects'] )
            options['promptNoProjects'] = 'Cliente sin proyectos';
        if( ! options['promptAllProjects'] )
            options['promptAllProjects'] = 'Todos';
        if( ! options['promptAllWorkers'] )
            options['promptAllWorkers'] = 'Todos los trabajadores';
        if( ! options['promptAllManagers'] )
            options['promptAllManagers'] = 'Todos los managers';

        // Loading customers image AJAX
        ajaxLoadingCustomers = new AjaxImageLoader({
            id: options['loadingCustomersImgId'],
            source: options['srcImageAJAXLoader']
        });
        // Loading projects image AJAX
        ajaxLoadingProjects = new AjaxImageLoader({
            id: options['loadingProjectsImgId'],
            source: options['srcImageAJAXLoader']
        });
        // Loading projects image AJAX
        ajaxLoadingWorkers = new AjaxImageLoader({
            id: options['loadingWorkersImgId'],
            source: options['srcImageAJAXLoader']
        });
        
        ajaxLoadingManagers = new AjaxImageLoader({
            id: options['loadingManagersImgId'],
            source: options['srcImageAJAXLoader']
        });
        
        
        
        
        $('#TaskSearch_dateIni').change(function() {
            updateProjects("", options, ajaxLoadingProjects);
            updateWorkers(options, ajaxLoadingWorkers);
            updateManagers(options, ajaxLoadingManagers);
        });
        $('#TaskSearch_dateEnd').change(function() {
            updateProjects("", options, ajaxLoadingProjects);
            updateWorkers(options, ajaxLoadingWorkers);
            updateManagers(options, ajaxLoadingManagers);
        });
        $('#Project_open_time').change(function() {
            updateProjects("", options, ajaxLoadingProjects);
            updateWorkers(options, ajaxLoadingWorkers);
            updateManagers(options, ajaxLoadingManagers);
        });
        $('#Project_close_time').change(function() {
            updateProjects("", options, ajaxLoadingProjects);
            updateWorkers(options, ajaxLoadingWorkers);
            updateManagers(options, ajaxLoadingManagers);
        });
        $('#ExpenseSearch_dateIni').change(function() {
            updateProjects("", options, ajaxLoadingProjects);
            updateWorkers(options, ajaxLoadingWorkers);
            updateManagers(options, ajaxLoadingManagers);
        });
        $('#ExpenseSearch_dateEnd').change(function() {
            updateProjects("", options, ajaxLoadingProjects);
            updateWorkers(options, ajaxLoadingWorkers);
            updateManagers(options, ajaxLoadingManagers);
        });

        // Company autocomplete
        jQuery(options['companyNameInputSelector']).autocomplete({
            autofocus: true,
            source: options['companiesForAutocomplete'],
            search: function(event,ui){
                ajaxLoadingCustomers.show();
            },
            open: function(event,ui){
                ajaxLoadingCustomers.hide();
            },
            select: function(event,ui)
            {
                ajaxLoadingCustomers.hide();
                ajaxLoadingProjects.show();
                // Clean select before update
                jQuery(options['projectSelectSelector'])
                .empty()
                .append('<option value="">Actualizando...</option>');

                updateProjects(ui.item.id, options, ajaxLoadingProjects); 
            }
        })
        .blur(function(){
            if( jQuery( options['companyNameInputSelector'] ).val() == "" ){
                jQuery( options['companyIdInputSelector'] ).val("");
                jQuery( options['projectSelectSelector'] )
                .empty()
                .append('<option value="">' + options['promptAllProjects'] + '</option>');
            };
        })
        ;

    }
</script>
