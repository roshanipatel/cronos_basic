<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
     <?php $this->head() ?>
    
    <?php
    
    //$this->registerJsFile( Yii::$app->request->BaseUrl .'/js/jquery-ui.min.js',['position' => \yii\web\View::POS_HEAD]);
    ?>
    <!-- <script type="text/javascript" src=<?= Yii::$app->request->BaseUrl.'/js/jquery-1.6.2.js' ?>></script>
    <script type="text/javascript" src=<?= Yii::$app->request->BaseUrl.'/js/jquery-ui-1.8.8.custom.js' ?>></script>
    <script type="text/javascript" src=<?= Yii::$app->request->BaseUrl.'/js/jqueryslidemenu.js' ?>></script> -->
    
</head>
<body>
<?php $this->beginBody() ?>

<div id="wrapper">
    <?php

    //print_r(Yii::$app->user->identity);die;
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-default navbar-static-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'nav navbar-top-links navbar-right','style'=>'margin-bottom: 0'],
        'items' => [
            Yii::$app->user->isGuest ? (
                ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li class="dropdown">
                <a class="dropdown-toggle" >
                        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                <ul class="dropdown-menu dropdown-user><li class="divider"></li><li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li></ul></li>'
            )
        ],
    ]);
    include_once('sidebar.php');
    NavBar::end();
    ?>

    <div id="page-wrapper">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>

    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">Copyright &copy;<?= date('Y') ?> by Open3s.</p>

        <p class="pull-right">All Rights Reserved. </p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();  ?>