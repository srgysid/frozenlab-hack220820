<?php
use yii\widgets\MaskedInput;
/**
 * Created by PhpStorm.
 * User: SS
 * Date: 24.04.2020
 * Time: 21:16
 */

//echo '<pre>'.print_r($model, true).'</pre>';
?>
<div class="row">

    <div class="col-md-8">
        <?= $form->field($model, 'name')->textInput(['placeholder' => 'Наименование собственника']) ?>
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
    <div class="col-md-8">
        <?= $form->field($model, 'legal_form')->textInput(['placeholder' => 'Организационно-правовая форма']) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'ogrn')->textInput(['placeholder' => 'ОГРН']) ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model, 'address')->textInput(['placeholder' => 'Фактический адрес']) ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model, 'email')->textInput(['placeholder' => 'Адрес электронной почты']) ?>
    </div>
</div>
