<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Constants;
use app\models\enums\ExpenseType;
use app\components\utils\PHPUtils;
use app\models\enums\ExpensePaymentMethod;
use app\services\ServiceFactory;
use fedemotta\datatables\DataTables;
use yii\data\ActiveDataProvider;

?>
<?php

//print_r($costsProvider);die; 
        assert(isset($projectsProvider));
        assert(isset($onlyManagedByUser));
    // Required fields
        $isProjectManagerRole = Yii::$app->user->hasProjectManagerPrivileges() || Yii::$app->user->hasAdministrativePrivileges();
                $form = ActiveForm::begin([
        
                    //'action' => $actionURL,
                    'method' => 'get',
                     ]);

                if (Yii::$app->user->isProjectManager()) {
                    $workersProvider = ServiceFactory::createUserService()->findWorkersByManager(true, Yii::$app->user->id);
                } else if (Yii::$app->user->hasAdministrativePrivileges()) {
                    $workersProvider = ServiceFactory::createUserService()->findAllWorkers(true);
                } else if ($isProjectManagerRole) {
                    $workersProvider = ServiceFactory::createUserService()->findProjectWorkers(true);
                } else {
                    $workersProvider = array();

                //If project manager
                }
                ?>
                <script type="text/javascript">
                    function isAnyChecked()
                    {
                        if( jQuery("input:checkbox:checked").length == 0 )
                        {
                            alert( "Seleccione algun cost para aprobar" );
                            return false;
                        }
                        else
                            return true;
                    }
                </script>
<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">Consultar Gastos Proyectos</h1>
  </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Imputar Gasto Proyecto
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <?= $form->field($model, 'dateIni')->textInput([
                                    'class'=>"form-control", 
                                    array( 'maxlength' => 20 )])->label('Fecha apertura') ?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <?= $form->field($model, 'dateEnd')->textInput([
                                    'class'=>"form-control", 
                                       array( 'maxlength' => 20 )])->label('Fecha cierre') ?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <?= $form->field($model, 'companyId')->hiddenInput(array('id' => 'company_id'))->hiddenInput()->label(false);?>
                                <?= $form->field($model, 'companyName')->textInput([
                                    'class'=>"form-control", 
                                    array( 'id' => 'company_name')])->label('Cliente') ?>
                                    <span id="loadingCustomers"></span>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <?php echo $form->field($model, 'projectId')->dropdownList( \yii\helpers\ArrayHelper::map($projectsProvider, 'id', 'name'), array('prompt' => 'Todos'));?>
                            </div>
                            <span id="loadingProjects"></span>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <?= $form->field($model, 'costtype')->dropdownList( ExpenseType::getDataForDropDown(), array('prompt' => 'Todos'));?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <?=  $form->field($model, 'paymentMethod')->dropdownList( ExpensePaymentMethod::getDataForDropDown(), array('prompt' => 'Todos'));?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <?php if ($isProjectManagerRole) { ?>
                            <div class="form-group"> 
                                <?php echo $form->field($model, 'worker')->dropdownList( \yii\helpers\ArrayHelper::map($workersProvider, 'id', 'name'), array('prompt' => 'Todos')); ?>
                            </div>
                            <span id="loadingWorkers"></span>
                            <?php } ?>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-lg-8">
                            <script type="text/javascript">
                                function projectSearch( frm )
                                {
                                    frm.action = '';
                                    frm.target = '_self';
                                    return true;
                                }
                            </script>
                            <?php echo Html::submitButton('Buscar', array('onClick' => 'return projectSearch( this.form );','class'=>'btn btn-success'));?>
                            &nbsp;
                            <?php if ($approveCost) 
                                {
                                    echo Html::submitButton( 'Aprobar seleccionadas', array('id' => 'approve_button','class'=>'btn btn-success','submit' => '','params' => array( 'doApprove' => '1' )) ); 
                                }?>
                            &nbsp;
                            <?php echo Html::submitButton( 'Select all', array('id' => 'select_all','class'=>'btn btn-success' ) ); ?>
                            <script type="text/javascript">
                                    function exportToCSV( frm )
                                    {
                                            frm.action = '<?php echo Yii::$app->urlManager->createUrl(['project-expense/exportToCSV']); ?>';
                                            frm.target = '_blank';
                                            return true;
                                    }
                            </script>
                            &nbsp;&nbsp;&nbsp;
                            <?php echo Html::submitButton('Exportar a CSV',array('onClick' => 'return exportToCSV( this.form );','class'=>'btn btn-success'));?>
                            <script type="text/javascript">
                                jQuery(document).ready((function() {
                                    
                                    $('#approve_button').click(function() {
                                        return isAnyChecked();
                                    });
                                    
                                    $('#select_all').click(function() {
                                        $('input[type=checkbox]').each(function () {
                                            this.checked = !this.checked;
                                         });
                                        return false;
                                    });
                                    
                                    jQuery( 'input[id^="ExpenseSearch_dateIni"],input[id^="ExpenseSearch_dateEnd"]' )
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
                                $this->render('../userProjectTask/_projectsFromCustomerAutocomplete', [
                                    'onlyManagedByUser' => $onlyManagedByUser,
                                    'onlyUserEnvolved' => true
                                ]);
                                ?>
                                <script type="text/javascript">
                                    jQuery(document).ready(function(){
                                        var options = new Object();
                                        options['companyIdInputSelector'] = '#company_id';
                                        options['companyNameInputSelector'] = '#company_name';
                                        options['projectSelectSelector'] = '#ExpenseSearch_projectId';
                                        options['workerSelectSelector'] = '#ExpenseSearch_worker';
                                        
                                        defineAutocompleteCustomers( options );
                                    });
                                </script>
                                <?php

                                $aExpenses = [
                                        ['class' => 'yii\grid\SerialColumn'],
                                       [
                                            'class' => 'yii\grid\DataColumn',
                                            'label' => 'Fecha Gasto',
                                            'value' => function ($data) {
                                                 return PHPUtils::removeHourPartFromDate($data->date_ini);
                                              },
                                        ],
                                        [
                                            'class' => 'yii\grid\DataColumn',
                                            'label' => 'Cliente',
                                            'value' => function ($data) {
                                                 return $data->companyName;
                                              },
                                        ],
                                        [
                                            'class' => 'yii\grid\DataColumn',
                                            'label' => 'projectName',
                                            'value' => function ($data) {
                                                 return $data->projectName;
                                              },
                                        ],
                                        [
                                            'class' => 'yii\grid\DataColumn',
                                            'label' => 'costtype',
                                            'value' => function ($data) {
                                                 return ExpenseType::toString($data->costtype);
                                              },
                                        ],
                                        [
                                            'class' => 'yii\grid\DataColumn',
                                            'label' => 'paymentMethod',
                                            'value' => function ($data) {
                                                 return ExpensePaymentMethod::toString($data->paymentMethod);
                                              },
                                        ],
                                        [   
                                            'class' => 'yii\grid\DataColumn',
                                            'label' => 'Imputador',
                                            'value' => function ($data) {
                                                 return $data->workerName;
                                              },
                                        ],
                                        [
                                            'class' => 'yii\grid\DataColumn',
                                            'label' => 'Importe',
                                            'value' => function ($data) {
                                                 return $data->importe;
                                              },
                                        ],
                                        [
                                            'class' => 'yii\grid\ActionColumn',
                                            'template'=>'{update}{delete}{pdf}',
                                            'buttons' => [
                                                'view' => [
                                                       'visible' => 'false'
                                                        ],
                                                'delete' => [
                                                      'visible' => '$data->project->statuscommercial != ProjectStatus::PS_CLOSED',
                                                        ],      
                                                'pdf' =>[
                                                        'label'=>'Download PDF',
                                                        'visible' => '$data->pdffile != ""',
                                                        'imageUrl'=>Yii::$app->request->baseUrl.'/images/pdf.png',
                                                        'url'=>'$this->grid->controller->createUrl("/project-expense/download-pdf/$data->id")',
                                                    ],
                                                'print' =>[
                                                    'visible' => 'false',
                                                ],
                                            ],
                                        ],
                                    ];

                                if ($approveCost) {
                                    $aExpenses[] = array(
                                            'class' => 'CCheckBoxColumn',
                                            'checked' => 'false',
                                            'id' => 'toApprove',
                                            'htmlOptions' => array(
                                                'style' => 'text-align: center',
                                            )
                                        );
                                }
                                ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= DataTables::widget([

    'id' => 'project-grid',
    'dataProvider' =>$costsProvider,
    //'filter' => $model,
    //'filter' => null,
    //'selectableRows' => 0,
    //'summaryText' => 'Mostrando {end} de {count} resultado(s)',
   // 'ajaxUpdate' => FALSE,
    'columns' => $aExpenses
]);

?>
