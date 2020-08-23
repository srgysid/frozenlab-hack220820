<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;

//echo '<pre>'.print_r($model, true).'</pre>';
?>

<div >

    <?php $form = ActiveForm::begin(
        [
            'id' => 'real-estate-form-id',
            'enableAjaxValidation' => true,
        ]
    ); ?>

    <div class="row">

        <div class="col-md-12">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'city_id')->widget(Select2::className(), [
                        'data' => $cities,
                        'options' => [
                            'id' => 'city-id',
                            'placeholder' => 'Выберете из списка'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>
                </div>

                <div class="col-md-4">
                    <?= $form->field($model, 'street_id')->widget(DepDrop::classname(), [
                        'type' => DepDrop::TYPE_SELECT2,
                        'data' => $streets,
                        'options' => ['id' => 'street-id', 'placeholder' => '--',],
                        'pluginOptions' => [
                            'depends' => ['city-id'],
                            'placeholder' => 'Выберете из списка',
                            'url' => Url::to(['/real-estate/street-list'])
                        ]
                    ]) ?>
                </div>

                <div class="col-md-4">
                    <?= $form->field($model, 'house_id')->widget(DepDrop::classname(), [
                        'type' => DepDrop::TYPE_SELECT2,
                        'data' => $houses,
                        'options' => ['id' => 'house-id', 'placeholder' => '--',],
                        'pluginOptions' => [
                            'depends' => ['street-id'],
                            'placeholder' => 'Выберете из списка',
                            'url' => Url::to(['/real-estate/house-list'])
                        ]
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'real_estate_type_id')->widget(Select2::className(), [
                        'data' => $prefShortName,
                        'options' => [
                            'placeholder' => 'Выберете из списка'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'num')->textInput(['placeholder' => 'Номер квартиры']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'area')->textInput(['placeholder' => 'Площадь помещения']) ?>
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
