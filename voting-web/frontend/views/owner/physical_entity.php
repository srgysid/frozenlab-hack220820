<?php
use yii\widgets\MaskedInput;
?>
<div class="row">
    <div class="col-md-8">
        <?= $form->field($model, 'name')->textInput(['placeholder' => 'ФИО собственника']) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'phone')->widget(MaskedInput::className(),[
            'mask'=>'+7 (999) 999-99-99',
            'clientOptions' => [
                'removeMaskOnSubmit' => true,
                'autoUnmask' => true,
            ],
        ])
        ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model, 'passport')->textInput(['placeholder' => 'Паспортные данные']) ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model, 'address')->textInput(['placeholder' => 'Фактический адрес собственника']) ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model, 'email')->textInput(['placeholder' => 'Адрес электронной почты']) ?>
    </div>
</div>
