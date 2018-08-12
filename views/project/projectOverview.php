<?php 
use yii\helpers\Html;
use fedemotta\datatables\DataTables;
use app\services\ServiceFactory;
use yii\widgets\ActiveForm;
use app\models\enums\ProjectStatus;
use app\models\enums\ProjectCategories;
use yii\data\ActiveDataProvider;
use app\components\utils\PHPUtils;

?>
<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">Estado de Proyectos</h1>
  </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Proyecto
            </div>
            <div class="panel-body">
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
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-lg-3">
                            <?= $form->field($model, 'open_time')->textInput(array('maxlength' => 20,'placeholder'=>'Fecha apertura','id'=>'Project_open_time')); ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'close_time')->textInput(array('maxlength' => 20,'placeholder'=>'Fecha cierre','id'=>'Project_close_time')); ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'company_name')->textInput(array('maxlength' => 20,'placeholder'=>'Cliente')); ?>
                            <?= $form->field($model, 'company_id')->hiddenInput(array('id' => 'company_id'))->hiddenInput()->label(false);?>
                            <span id="loadingCustomers"></span>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'id')->dropDownList(\yii\helpers\ArrayHelper::map($projectsProvider, 'id', 'name'), array('prompt' => 'Todos','style' => 'width: 200px')); ?>
                            <span id="loadingProjects"></span>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-lg-3">
                            <?= $form->field($model, 'status')->dropDownList( ProjectStatus::getDataForDropDown(), array('prompt' => 'Todos')); ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'statuscommercial')->dropDownList( ProjectStatus::getDataForDropDown(), array('prompt' => 'Todos')); ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'cat_type')->dropDownList( ProjectCategories::getDataForDropDown(), array('prompt' => 'Todas')); ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'reporting')->dropDownList( array("0" => "Con Informe", "1" => "Sin Informe"), array('prompt' => 'Todas')); ?>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-lg-3">
                            <?= $form->field($model, 'imputetype')->dropDownList(\yii\helpers\ArrayHelper::map($projectImputetypes, 'id', 'name'),array('multiple' => 'multiple','id' => 'imputetype_projects')); ?>
                        </div>
                        <div class="col-lg-3">
                            <?php if ($showManager) { ?>
                               <?= $form->field($model, 'manager_id')->dropDownList(\yii\helpers\ArrayHelper::map($managersProvider, 'id', 'name'), array('prompt' => 'Todos')); ?>
                               <span id="loadingWorkers"></span>
                            <?php } ?>
                        </div>
                        <div class="col-lg-3">
                        </div>
                        <div class="col-lg-3">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
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
                        <?php
                        echo Html::submitButton('Exportar a CSV', array(
                            'class'=>'btn btn-success',
                            'onClick' => 'return exportToCSV( this.form );',
                        ));
                        ?>
                        <?php } ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>

<link href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet"></link>
            
<?= $this->registerJsFile(Yii::$app->request->BaseUrl .'/js/jquery-1.6.2.js',['position' => \yii\web\View::POS_BEGIN]); ?>
<script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js" ></script>
    <?php
    echo $this->render('../userProjectTask/_projectsFromCustomerAutocomplete', [
        'projectStatus' => (!isset($projectStatus)) ? NULL : $projectStatus,
        'onlyManagedByUser' => false,
        'onlyUserEnvolved' => true
    ]);
    ?>

<?php
echo $this->registerJsFile(Yii::$app->request->BaseUrl .'/js/plugins/jquery.progressbar.min.js',['position' => \yii\web\View::POS_BEGIN]);
?>

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
<div class="row">
<div class="col-lg-12">
<?php
echo DataTables::widget(array(
    'id' => 'dataTables-example',
    'dataProvider' => new ActiveDataProvider(array('query' =>$model->find())),
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
</div>
</div>
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
<script type="text/javascript">
    jQuery(document).ready(function(){
    var table = $('#datatables_dataTables-example').DataTable( {
        scrollY:        "300px",
        scrollX:        true,
        scrollCollapse: true,
        //paging:         false,
        columnDefs: [
            { width: '20%', targets: 0 }
        ],
        fixedColumns: true
    } );
} );
</script>
            </div>
        </div>
    </div>
</div>