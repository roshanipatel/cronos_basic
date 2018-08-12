<?php

use yii\helpers\Html;
use fedemotta\datatables\DataTables;
use common\components\Common;
use app\models\enums\Roles;
?>
<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">Gestionar Usuarios</h1>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        Usuarios
      </div>
      <div class="panel-body">
      <?= DataTables::widget([
              'id' => 'dataTables-example',
              'class'=>'table table-striped table-bordered table-hover',
              'dataProvider' => $model,
              'filterModel' => $filter,
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
                          /*'buttons'=> [
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
      </div>
    </div>
  </div>
</div>