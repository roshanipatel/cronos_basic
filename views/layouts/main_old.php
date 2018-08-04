<?php
// In order to use custom jquery.js file
Yii::app()->clientScript->scriptMap = array(
    'jquery.js' => false,
    'jquery.min.js' => false,
    'jquery-ui.js' => false,
    'jquery-ui.min.js' => false,
);
?>
<?php
// Prevent browser caching
header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$baseUrl = Yii::app()->request->hostInfo . Yii::app()->request->baseUrl;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
    <head>
        <base href="<?php echo $baseUrl; ?>/"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="es" />
        <!-- blueprint CSS framework -->
        <link rel="stylesheet" type="text/css" href="css/screen.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
        <!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

        <link rel="stylesheet" type="text/css" href="css/main.css" />
        <link rel="stylesheet" type="text/css" href="css/form.css" />

        <link rel="stylesheet" type="text/css" href="css/cronos.css<?php echo "?r=" . rand() ?>"/>

        <!-- jQueryUI theme -->
        <link rel="stylesheet" type="text/css" href="css/redmond/jquery-ui-1.8.8.custom.css" />

        <!-- SLIDE MENU -->
        <link rel="stylesheet" type="text/css" href="css/jqueryslidemenu.css" />

        <!-- MULTISELECT -->
        <link rel="stylesheet" type="text/css" href="css/multiselect/ui-multiselect.css" />
        <!--[if lte IE 7]>
        <style type="text/css">
        html .jqueryslidemenu{height: 1%;} /*Holly Hack for IE7 and below*/
        </style>
        <![endif]-->

        <!-- jQuery & jQueryUI -->
        <script type="text/javascript" src="js/jquery-1.6.2.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.8.custom.js"></script>

        <!-- Slide menu -->
        <script type="text/javascript" src="js/jqueryslidemenu.js"></script>

        <title><?php echo CHtml::encode( $this->pageTitle ); ?></title>
        <script type="text/javascript">
            jQuery(document).ready(function()
            {
                jQuery( "input:submit" ).button();
                jQuery( "input:submit" ).css('font-size', '0.8em')
            });
        </script>

    </head>

    <body>

        <div class="container" id="page">
            <div id="header">
                <div id="logo"><?php echo CHtml::encode( Yii::app()->name ); ?></div>
            </div><!-- header -->

            <?php echo $content; ?>
            <?php if( isset($_SERVER['HTTP_REFERER']) && (!strpos($_SERVER['HTTP_REFERER'],"login")) ) { ?>
                <span style="float:left; position: relative; top:30px; left: 20px">
                    <!--
        <a href='<?php echo htmlspecialchars( $_SERVER['HTTP_REFERER'] ); ?>' title="Volver">
            <img alt="Volver" src="<?php echo Yii::app()->request->baseUrl; ?>/images/back.png"/>
        </a>-->
                    <a href='#' onclick="history.back();return false;" title="Volver"
                       style="color: #2E6E9E; text-decoration: none; font-weight: bold; font-size: 0.8em">
                        Volver
                    </a>
                </span>
            <?php } ?>
            <div id="footer">
                Copyright &copy; <?php echo date( 'Y' ); ?> by Open3s.<br/>
                All Rights Reserved.<br/>
            </div><!-- footer -->
        </div><!-- page -->
    </body>
</html>