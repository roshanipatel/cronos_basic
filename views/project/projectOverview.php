<?php 
use yii\helpers\Html;
use yii\grid\GridView;
use app\services\ServiceFactory;
use yii\widgets\ActiveForm;
use app\models\enums\ProjectStatus;
use app\models\enums\ProjectCategories;
use yii\data\ActiveDataProvider;
use app\components\utils\PHPUtils;

?>
<h1>Estado de Proyectos</h1>
<?php /* * ********** SEARCH FORM  ****************** */ ?>
<?php
assert(isset($projectsProvider));
// Required fields
$showExportButton = TRUE;
$searchFieldsToHide = array();
$showManager = Yii::$app->user->hasDirectorPrivileges();

$form = ActiveForm::begin([
     'method' => 'get',
    'action' => Yii::$app->urlManager->createUrl(['projectOveriew'])
    ]); 

if ($showManager) {
    $managersProvider = ServiceFactory::createUserService()->findProjectManagers();
} else {
    $managersProvider = array();
}
?>

<table id="tableTaskSearch">
        <tr>
            <td class="title_search_field">Fecha apertura</td>
            <td class="title_search_field">Fecha cierre</td>
            <td class="title_search_field">Cliente</td>
            <td class="title_search_field">Proyecto</td>
            <?php
            if ($showManager) {
                echo '<td class="title_search_field">Manager</td>';
            }
            ?>
            <td class="title_search_field">Est. proyecto Op.</td>
            <td class="title_search_field">Est. proyecto Com.</td>
            <td class="title_search_field">Categoría</td>
            <td class="title_search_field">Informe</td>
        </tr>
        <tr>
            <?php
            echo "<td>\n";
            echo $form->field($model, 'open_time')->textInput(array(
                'maxlength' => 20,
            ));
            echo "</td>\n";
            echo "<td>\n";
            echo $form->field($model, 'close_time')->textInput(array(
                'maxlength' => 20,
            ));
            echo "</td>\n";
            echo "<td>\n";
            echo $form->field($model, 'company_id')->hiddenInput(array('id' => 'company_id'));
            echo $form->field($model, 'company_name')->textInput(array(
                'id' => 'company_name',
                'style' => 'width: 160px'
            ));
            echo "<span id=\"loadingCustomers\"></span>\n";
            echo "</td>\n";
            echo "<td>\n";
            echo $form->field($model, 'id')->dropDownList(\yii\helpers\ArrayHelper::map($projectsProvider, 'id', 'name'), array(
                'prompt' => 'Todos',
                'style' => 'width: 200px'
            ));
            echo "<span id=\"loadingProjects\"></span>\n";
            echo "</td>\n";
            if ($showManager) {
                echo "<td>\n";
                echo $form->field($model, 'manager_id')->dropDownList(\yii\helpers\ArrayHelper::map($managersProvider, 'id', 'name'), array(
                    'prompt' => 'Todos',
                    'style' => 'width: 120px'
                ));
                echo "<span id=\"loadingWorkers\"></span>\n";
                echo "</td>\n";
            }
            echo "<td>\n";
            echo $form->field($model, 'status')->dropDownList( ProjectStatus::getDataForDropDown(), array(
                'prompt' => 'Todos',
                'style' => 'width: 100px'
            ));
            echo "</td>\n";
            echo "<td>\n";
            echo $form->field($model, 'statuscommercial')->dropDownList( ProjectStatus::getDataForDropDown(), array(
                'prompt' => 'Todos',
                'style' => 'width: 100px'
            ));
            echo "</td>\n";
            echo "<td>\n";
            echo $form->field($model, 'cat_type')->dropDownList( ProjectCategories::getDataForDropDown(), array(
                'prompt' => 'Todas',
                'style' => 'width: 150px'
            ));
            echo "</td>\n";
            echo "<td>\n";
            echo $form->field($model, 'reporting')->dropDownList( array("0" => "Con Informe", "1" => "Sin Informe"), array(
                'prompt' => 'Todas',
                'style' => 'width: 90px'
            ));
            echo "</td>\n";
            ?>
        </tr>
        <tr>
            <td class="title_search_field">Tipo imputación</td>        
        </tr>
        <tr>
            <?php
            echo "<td>\n";
            echo $form->field($model, 'imputetype')->dropDownList(\yii\helpers\ArrayHelper::map($projectImputetypes, 'id', 'name'),
                            array(
                    'style' => 'width: 120px',
                    'multiple' => 'multiple',
                    'id' => 'imputetype_projects',
            ));
            
            echo "</td>\n";
            ?>
        </tr>
        <tr>
            <td colspan="9" align="center">
                <br>
                <script type="text/javascript">
                    function projectSearch( frm )
                    {
                        frm.action = '';
                        frm.target = '_self';
                        return true;
                    }
                </script>
                    <?php echo Html::submitButton( 'Buscar' , ['class'=>'btn btn-success','onClick' => 'return projectSearch( this.form );']); ?>
                
                    <?php if ($showExportButton) { ?>
                    <script type="text/javascript">
                        function exportToCSV( frm )
                        {
                            frm.action = '<?php echo Yii::$app->urlManager->createUrl("project/exportToCSV"); ?>';
                            frm.target = '_blank';
                            return true;
                        }
                    </script>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php
                    echo Html::submitButton('Exportar a CSV', array(
                        'class'=>'btn btn-success',
                        'onClick' => 'return exportToCSV( this.form );',
                    ));
                    ?>
                    <?php } ?>
            </td>
        </tr>
</table>
<script type="text/javascript">
    jQuery(document).ready((function() {
        jQuery( 'input[id^="Project_open_time"],input[id^="Project_close_time"]' )
        .attr('readonly', 'readonly')
        .datepicker(
        {
            'dateFormat': 'dd/mm/yy',
            'timeFormat': 'hh:mm',
            'monthNames': [ 'Enero', 'Febrero', 'Marzo', 'Abril',
                'Mayo', 'Junio', 'Julio', 'Agosto',
                'Setiembre', 'Octubre', 'Noviembre', 'Diciembre' ],
            'showAnim': 'fold',
            'type' : 'date',
            'dayNamesMin' : ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa' ],
            'firstDay' : 1,
            'currentText' : 'Hoy',
            'closeText' : 'Listo',
            'showButtonPanel' : true
        });
        jQuery( "div.ui-datepicker" ).css("font-size", "80%");
    }));
</script>
<?php
echo $this->render('../userProjectTask/_projectsFromCustomerAutocomplete', [
    'projectStatus' => (!isset($projectStatus)) ? NULL : $projectStatus,
    'onlyManagedByUser' => false,
    'onlyUserEnvolved' => true
]);
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var options = new Object();
        options['companyIdInputSelector'] = '#company_id';
        options['companyNameInputSelector'] = '#company_name';
        options['projectSelectSelector'] = '#Project_id';
        options['managerSelectSelector'] = '#Project_manager_id';
        defineAutocompleteCustomers( options );
    });
</script>
<?php ActiveForm::end(); ?>
<?php /* * ********** END SEARCH FORM  ****************** */ ?>

<?php
//$cs = Yii::$app->clientScript;
$this->registerJsFile('js/plugins/jquery.progressbar.min.js',['position' => \yii\web\View::POS_BEGIN]);

GridView::widget(array(
    'id' => 'project-grid',
    'dataProvider' => new ActiveDataProvider(array('query' =>$model->find())),
   // 'filterModel'=>$model,
    //'filter' => null,
    //'selectableRows' => 0,
    'summary' => 'Mostrando {end} de {count} resultado(s)',
    //'ajaxUpdate' => FALSE,
    'columns' => array(
        ['class' => 'yii\grid\SerialColumn'],
        array(

            'label' => 'Cliente',
            'value' => function ($data) {
                           return $data->company_custom;
                        },
            //'name' => 'company_custom',
           // 'value' => '$data->company_custom',
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
            'label' => 'Proyecto',
            'value' => function ($data) {
                           return $data->name;
                        },
            //'name' => 'name',
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
            //'name' => 'open_time',
            'label' => 'Apertura',
            'value' => function ($data) {
                           return PHPUtils::removeHourPartFromDate($data->open_time);
                        },
            /*'htmlOptions' => array(
                'style' => 'width: 60px'
            ),*/
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
            //'name' => 'close_time',
            'label' => 'Cierre',
            'value' => function ($data) {
                           return PHPUtils::removeHourPartFromDate($data->close_time);
                        },
            //'value' => 'PHPUtils::removeHourPartFromDate($data->close_time)',
            /*'htmlOptions' => array(
                'style' => 'width: 60px'
            ),*/
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
            //'name' => 'manager_custom',
            'visible' => $showManager,
            'label' => 'Manager',
            'value' => function ($data) {
                           return $data->manager_custom;
                        },
           // 'value' => '$data->manager_custom'
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
            //'name' => 'commercial_custom',
            'visible' => $showManager,
            'label' => 'Comercial',
            'value' => function ($data) {
                           return $data->commercial_custom;
                        },
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
            //'name' => 'status',
            'value' => function ($data) {
                           return ProjectStatus::toString($data->status);
                        },
            'filter' => ProjectStatus::getDataForDropdown(),
            /*'htmlOptions' => array(
                'style' => 'width: 50px'
            ),*/
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
            //'name' => 'statuscommercial',
            'value' => function ($data) {
                           return ProjectStatus::toString($data->statuscommercial);
                        },
            'filter' => ProjectStatus::getDataForDropdown(),
            /*'htmlOptions' => array(
                'style' => 'width: 50px'
            ),*/
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
//            'name' => 'category_name',
            'value' => function ($data) {
                           return $data->category_name;
                        },
            'filter' => ProjectCategories::getDataForDropdown(),
            //'htmlOptions' => array('style' => 'text-align: left; width:140px'),
        ),
        /*array(
            'label' => 'Horas',
            //'class' => 'ProjectHoursProgressBarColumn',
            //'htmlOptions' => array('style' => 'text-align: left; width:130px'),
        ),*/
        array(
            'class' => 'yii\grid\DataColumn',
            'value' => function ($data) {
                           return $data->totalSeconds;
                        }, 
            //'name' => 'totalSeconds',
            'label' => 'Hours Executed',
            //'htmlOptions' => array('style' => 'text-align: left; width:20px'),
        ),
        array(
            'class' => 'yii\grid\DataColumn', 
            'label' => 'Max Hours',
            'value' => function ($data) {
                           return $data->max_hours;
                        }, 
            //'htmlOptions' => array('style' => 'text-align: left; width:20px'),
        ),
        array(
            'class' => 'yii\grid\DataColumn',
            'value' => function ($data) {
                           return $data->executed;
                        },  
            //'name' => 'executed',
            'label' => '% Executed',
            //'htmlOptions' => array('style' => 'text-align: left; width:20px'),
        ),
        /*array(
            'class' => 'CButtonColumn',
            'visible' => 'Yii::$app->user->hasDirectorPrivileges()',
            'buttons' => array(
                'delete' => array(
                    'visible' => '!$data->hasTasks()',
                ),
                'update' => array(
                    'visible' => 'true',
                ),
                'view' => array(
                    'visible' => 'false',
                ),
                'print' => array(
                    'visible' => '$data->hasReport()',
                    'options' => array('target' => '_new'),
                ),
            ),
            'htmlOptions' => array(
                'style' => 'text-align: left',
            ),
            'headerHtmlOptions' => array(
                'style' => 'width: 40px',
            ),
        ),*/
       /* array(
            'class' => 'yii\grid\DataColumn', 
            'label' => 'Cliente',
           // 'name' => 'company_custom',
            'value' => function ($data) {
                return $data->company_custom;
            }
            //'value' => '$data->company_custom',
        ),*/
    )
)); 
?>
