<?php
use frontend\assets\AppAsset;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

$asset = AppAsset::register($this);
$fav_url = $asset->baseUrl.'/images/favicons';
?>
<style>
    /*.login-page{*/
        /*height: auto;*/
        /*background: #d2d6de;*/
    /*}*/
</style>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" sizes="180x180" href="<?= $fav_url ?>/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $fav_url ?>/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $fav_url ?>/favicon-16x16.png">
    <link rel="manifest" href="<?= $fav_url ?>/site.webmanifest">

    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="login-page">

<?php $this->beginBody() ?>

    <?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
