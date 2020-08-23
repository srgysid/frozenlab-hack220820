<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use frontend\assets\AppAsset;

$appAsset = AppAsset::register($this);

$this->title = Yii::t('app', 'Изменение пароля');
$curentUser = Yii::$app->user->identity;

?>
<div class="change-password">

    <div class="login-box-body">
        <div class="row">
            <div  class="col-12">
                <h4><?= $curentUser->userProfile->getFullName() ?></h4>
            </div>
        </div>
        <div class="row">

            <div  class="col-12 col-md-12">
                <?php $form = ActiveForm::begin(['id' => 'change-password-form']); ?>

                <?= $form->field($model, 'old_password')->passwordInput(['placeholder' => 'Введите текущий пароль']) ?>
                <?= $form->field($model, 'new_password')->passwordInput(['placeholder' => 'Введите новый пароль']) ?>
                <?= $form->field($model, 'repeat_password')->passwordInput(['placeholder' => 'Повторите новый пароль']) ?>

                <div class="form-group">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-walive btn-walive-secondary btn-block', 'name' => 'save-button']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
