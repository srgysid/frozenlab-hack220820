<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use frontend\assets\AppAsset;

$appAsset = AppAsset::register($this);

$this->title = Yii::t('app', 'Вход в систему');
$this->params['breadcrumbs'][] = $this->title;

$img_path = $appAsset->baseUrl.'/images/green_home_300.png';

?>
<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        color: #fff;
        /*border-color: #048B70;*/
        /*background-color: #048B70;*/
        border-color: #36b6b5;
        background-color: #36b6b5;

    }
</style>
<div class="login-box">

    <div class="login-box-body">
        <div class="row">
                <div class="col-md-12 login-title">
                    <h2><img align="center" width="300px" src="<?=$img_path?>"></h2>
                </div>

                <div  class="col-md-12 ">
                    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                    <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Введите ваш логин'])->label('Имя пользователя')?>

                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Введите ваш пароль'])->label('Пароль') ?>

                    <?= $form->field($model, 'rememberMe')->checkbox()->label('Запомнить Меня') ?>

                    <div class="form-group">
                        <?= Html::submitButton('Войти', ['class' => 'btn btn-walive btn-walive-secondary btn-block', 'name' => 'login-button']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
        </div>
    </div>
</div>
