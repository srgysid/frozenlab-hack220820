<?php

use common\models\Issue;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;
use kartik\datetime\DateTimePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\depdrop\DepDrop;

?>

<div >

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Введите пароль пользователя'])?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'password_repeat')->passwordInput(['placeholder' => 'Повторно введите пароль пользователя'])?>
                </div>
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-kazna btn-success']) ?>
        <button type="button" class="btn btn-kazna btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
