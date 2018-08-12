<h1>Gestionar Proyectos</h1>

<?php
use yii\helpers\Html;
use yii\grid\GridView;
use common\components\Common;
use app\models\enums\Roles;
use app\models\enums\ProjectStatus;
use app\models\enums\ProjectCategories;

?>
<?= GridView::widget(array(
    'id' => 'project-grid',
    'dataProvider' => $model->search(),
    'filterModel'=>$model,
    //'selectableRows' => 0,
    'summary' => 'Mostrando {start}-{end} resultado(s)',
    'columns' => array(
        //'code',
        'name',
        array(
            'class' => 'yii\grid\DataColumn', 
            'label' => 'Empresa',
            'name' => 'company_name',
            'value' => '$data->company->name',
        ),
        array(
            'name' => 'status',
            'value' => 'ProjectStatus::toString($data->status)',
			//'filter' => ProjectStatus::getDataForDropdown(),
			'htmlOptions' => array(
				'style' => 'width: 100px'
			),
        ),
        array(
            'name' => 'cat_type',
            'value' => '(empty($data->cat_type))?"":ProjectCategories::toString($data->cat_type)',
			'filter' => ProjectCategories::getDataForDropdown(),
        ),
       /* array(
            'class' => 'CButtonColumn',
            'buttons' => array(
                'delete' => array(
                    'visible' => '!$data->hasTasks()',
                ),
                'print' => array(
                    'visible' => 'false',
                ),
            ),
            'htmlOptions' => array( 'style' => 'text-align: left' ),
        ),*/
    ),
) ); ?>
