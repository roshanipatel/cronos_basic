<h1>Gestionar Usuarios</h1>

<?php
use yii\helpers\Html;
use yii\grid\GridView;
use common\components\Common;
use app\models\enums\Roles;
?>
<?= GridView::widget([
        'id' => 'user-grid',
        'dataProvider' => $model,
        'filterModel' => $filter,
        'summary' => 'Mostrando {end} de {count} resultado(s)',
        'columns' => 
                  [
                    ['class' => 'yii\grid\SerialColumn'],
                   'username',
                    'name',
                    [
                        'class' => 'yii\grid\DataColumn', 
                        'label' => 'email',
                        'value' => function ($data) {
                           return $data->email;
                        },
      //                  'filter' => false,
                    ],
                  [
                    'class' => 'yii\grid\DataColumn', 
                    'label' => 'Empresa',
                    'value' => function ($data) {
                       return $data->company->name;
                    }
                  ],
                  [

                    'class' => 'yii\grid\DataColumn', 
                    'label' => 'role',
                    'value' => function ($data) {
                       return Roles::toString( $data->role );
                    }
        //            'filter' => false,
                  ],
                  [
                    'class' => 'yii\grid\ActionColumn',
                   // 'template'=>'{delete}{print}',
                   /* 'buttons'=> [
                            'delete' =>[
                                'visible' => '$data->canDelete()',
                            ],
                            'print' =>[
                                'visible' => 'false',
                            ],
                        ],*/
                  ],
              ]
    ]);
?>