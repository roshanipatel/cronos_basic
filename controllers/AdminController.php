<?php

class AdminController extends CronosController
{

    public function actionDeleteAssets()
    {
        $assetsDir = Yii::app()->params->assets_dir;
        if( empty( $assetsDir ) )
            return;
        PHPUtils::rmDirContentsRecursive( $assetsDir );
    }

}

?>
