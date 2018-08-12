<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/jquery-ui-1.8.8.custom.css',
        'css/bootstrap.min.css',
        'css/metisMenu.min.css',
        'css/dataTables.bootstrap.css',
        'css/dataTables.responsive.css',
        'css/sb-admin-2.css',
        'css/font-awesome.min.css',
       // 'css/morris.css',
        'css/site.css',
    ];
    public $js = [
      //  'js/jquery-ui.min.js',
        'js/metisMenu.min.js',
        'js/sb-admin-2.js',
        //'js/morris.min.js',
        //'js/morris-data.js',
        //'js/raphael.min.js',
       
       
    ];
    public $depends = [
    'fedemotta\datatables\DataTablesAsset',
    ];
}
