<?php

use frontend\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);

/* @var $this yii\web\View */

$this->title = Yii::t('app','Рабочее название');
$profile = Yii::$app->user->identity->userProfile;
?>
<div class="site-index">
    <p class="mt-4">Пользователь: <?= $profile->fullName ?></p>
    <p>Телефон: <?= $profile->phone ?></p>
    <?php
        if (Yii::$app->user->can('rl_admin')) {
            echo 'работает, насяльника';
        }
        else {
            echo 'ругается, насяльника';
        }
    ?>
</div>
