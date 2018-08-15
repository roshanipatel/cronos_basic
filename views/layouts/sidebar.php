<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
use app\models\enums\Roles;
use yii\helpers\Html;    

$userRole = Yii::$app->user->identity->role;
switch( $userRole )
{
    case Roles::UT_ADMIN:
    case Roles::UT_DIRECTOR_OP:
        $menuToInclude = 'top_menu_admin.php';
        break;
    case Roles::UT_WORKER:
        $menuToInclude = 'top_menu_worker.php';
        break;
    case Roles::UT_CUSTOMER:
        $menuToInclude = 'top_menu_customer.php';
        break;
    case Roles::UT_PROJECT_MANAGER:
        $menuToInclude = 'top_menu_manager.php';
        break;
    case Roles::UT_ADMINISTRATIVE:
    case Roles::UT_COMERCIAL:
        $menuToInclude = 'top_menu_comercial.php';
        break;
    default:
        throw new HttpException(500, 'Database integrity error' );
}
?>
<div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <!-- <li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                            </div>
                        </li> -->
                        <li>
                            <a href="index.html"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                        </li>
                        <?php include $menuToInclude ?>
                        
                        <li><?php echo Html::a( 'Salir ('.Yii::$app->user->identity->username.')', array( 'site/logout' ) ) ?></li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
