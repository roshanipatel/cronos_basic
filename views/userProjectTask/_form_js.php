
<?php

use yii\helpers\Json;
$this->registerJsFile(Yii::$app->request->BaseUrl .'/js/jquery.min.js',['position' => \yii\web\View::POS_BEGIN]);
$this->registerJsFile( Yii::$app->request->BaseUrl .'/js/jquery-ui-1.8.8.custom.js',['position' => \yii\web\View::POS_BEGIN]);
//$this->registerJsFile( Yii::$app->request->BaseUrl ."/js/jquery-1.6.2.min.js" );
$this->registerJsFile( Yii::$app->request->BaseUrl .'/js/date.js' ,['position' => \yii\web\View::POS_BEGIN]);
$this->registerJsFile( Yii::$app->request->BaseUrl .'/js/ajax_loading_image.js',['position' => \yii\web\View::POS_BEGIN]);


// Build an array of customer names
$customerNames = array();
$aColorCompanies = array();
foreach( $customers as $customer ) {
    $customerNames[] = $customer->name;
    $aColorCompanies[$customer->id] = $customer->getColor();
}
$Jsoncolors = "'".Json::encode( $aColorCompanies )."'";
$JsoncustomerNames = "'".Json::encode( $customerNames )."'";

?>

<?php  $this->registerJs( '  

    function testTicket()
    {
        var baseUrl = "'. Yii::$app->params['ticket_url'] .'";
        var ticketId = jQuery("#UserProjectTask_ticket_id").val().trim();
        if( ticketId == "" )
            alert("Introduzca un ticket válido");
        else
            window.open( baseUrl.replace( "{ticket_id}", ticketId ) );
    }
    
    function checkTaskHours(){
        var resultObj = {};
        var hourIni = Date.parseExact(jQuery("#UserProjectTask_frm_hour_ini").val(), "HH:mm");
        var hourEnd = Date.parseExact(jQuery("#UserProjectTask_frm_hour_end").val(), "HH:mm");
        if( hourIni.getTime() > hourEnd.getTime() ){
            resultObj["success"] = false;
            resultObj["hourIni"] = hourIni;
            resultObj["hourEnd"] = hourEnd;
        }
        else{
            resultObj["success"] = true;
        }
        return resultObj;
    }
    
    function validateTaskForm(){
        if( isNaN(parseInt(jQuery("#customer_projects").val())) ){
            alert("Seleccione un proyecto");
            return false;
        }
        var checkTaskHoursResult = checkTaskHours();
        if( ! checkTaskHoursResult["success"] )
        {
            alert("La hora inicial debe ser anterior a la final");
            return false;
        }
        return true;
    }

    function checkHourSemantics(which){
        var checkTaskHoursResult = checkTaskHours();
        if( ! checkTaskHoursResult["success"] ){
            if( which == "hour_ini" ){
                jQuery("#UserProjectTask_frm_hour_end").val(jQuery("#UserProjectTask_frm_hour_ini").val());
            }
            else{
                jQuery("#UserProjectTask_frm_hour_ini").val(jQuery("#UserProjectTask_frm_hour_end").val());
            }
        }
    }
    
    // Populate select with projects
    function populateProjectsSelect(url,data,projectId, projectName){
        ajaxLoadingProjects.show();
        // Clean select before update
        jQuery(\'#customer_projects\')
        .empty()
        .append("<option value=\'\'>Actualizando proyectos...</option>");

        jQuery.ajax( {
            "url": url,
            "data": data,
            "dataType":"html",
            "cache":false,
            "success":function(html){
                jQuery("#customer_projects").html(html);
                if( projectId ){
                    if (html.search("value=" + projectId ) <= 0) {
                        jQuery("#customer_projects").append("<option value= projectId >" + projectName + "</option>");
                    }
                    jQuery("#customer_projects").val(projectId);
                }
            },
            "error":function(){
                alert("Respuesta inválida. Seleccione otro cliente.");
                jQuery("#customer_projects")
                .empty()
                .append("<option value=\'\'>Seleccione cliente...</option>");

            },
            "complete":function(){
                ajaxLoadingProjects.hide();
            }
        });
    }
    
    function populateImputetypeSelect(url,data,imputetypeId, imputetypeName){
        ajaxLoadingImputetypes.show();
        // Clean select before update
        jQuery("#imputetype_projects")
        .empty()
        .append("<option value=\'\'>Actualizando tipos de imputación...</option>");

        jQuery.ajax( {
            "url": url,
            "data": data,
            "dataType":"html",
            "cache":false,
            "success":function(html){
                jQuery("#imputetype_projects").empty().html(html);
                if( imputetypeId ){
                    if (html.search("value="+ imputetypeId ) <= 0) {
                        jQuery("#imputetype_projects").append("<option value=imputetypeId>" + imputetypeName + "</option>");
                    }
                    jQuery("#imputetype_projects").val(imputetypeId);
                }
            },
            "error":function(){
                alert("Respuesta inválida. Seleccione otro tipo de imputación.");
                jQuery("#imputetype_projects")
                .empty()
                .append("<option value=\'\'>Seleccione tipo de imputación...</option>");

            },
            "complete":function(){
                ajaxLoadingImputetypes.hide();
            }
        });
    }
     
    var ajaxLoadingProjects;
    var ajaxLoadingImputetypes;
    jQuery(document).ready(function(){	
        // Loading projects image AJAX
        ajaxLoadingProjects = new AjaxImageLoader({
            id: "loadingProjects",
            source: "images/ajax-loader.gif"
        });
                
        ajaxLoadingImputetypes = new AjaxImageLoader({
            id: "loadingImputetypes",
            source: "images/ajax-loader.gif"
        });
    });

   
        var companyColors =  '.$Jsoncolors .';
        var customersAutocomplete = {
            names:  '.$JsoncustomerNames.'
        };
        // Company autocomplete
        jQuery("#company_name").autocomplete({
            source: customersAutocomplete.names,
            select: function(event,ui)
            {
                var url = "'. Yii::$app->urlManager->createUrl( 'AJAX/retrieveOpenProjectsFromCustomerNameAsListOptions' ).'";
                var data = { customerName: ui.item.value };
                populateProjectsSelect(url,data,false);
            }
        });
        
        $("#customer_projects").change(function(event,ui)
            {
                var url = "'. Yii::$app->urlManager->createUrl( 'AJAX/retrieveImputetypesFromProjectAsListOptions' ).'";
                var data = { projectId: $("#customer_projects").val() };
                populateImputetypeSelect(url,data,false);
            }
        );
    
    // Loading image for task insertion/update
    var ajaxSavingTask = new AjaxImageLoader({
        id: "savingTasks",
        source: "images/ajax-loader.gif"
    });
');