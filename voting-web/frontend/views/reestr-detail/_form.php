<?php

use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

//echo '<pre>'.print_r($model, true).'</pre>';
?>

<div>

    <?php $form = ActiveForm::begin(
        [
            'id' => 'reestr-detail-form-id',
            'enableAjaxValidation' => true,
        ]
    ); ?>

    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'type_owner_id')->widget(Select2::className(), [
                        'data' => $type_owner,
                        'options' => [
                            'id' => 'type-owner-id',
                            'placeholder' => 'Выберете из списка'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>

                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput(['placeholder' => 'Наименование собственника']) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'type_real_estate')->textInput(['placeholder' => 'Тип помещеения']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'num')->textInput(['placeholder' => 'Номер']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'area')->widget(MaskedInput::className(),[
                        'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => ',',
                            'autoGroup' => true,
                            'removeMaskOnSubmit' => true,
                            'autoUnmask' => true,
                        ],
                    ])
                    ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'part')->textInput(['placeholder' => 'Доля']) ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'ownership')->textInput(['placeholder' => 'Право собственности']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'email')->textInput(['placeholder' => 'Адрес электронной почты']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'phone')->widget(MaskedInput::className(),[
                        'mask'=>'+7 (999) 999-99-99',
                        'clientOptions' => [
                            'removeMaskOnSubmit' => true,
                            'autoUnmask' => true,
                        ],
                    ])
                    ?>
                </div>

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-walive btn-success']) ?>
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
