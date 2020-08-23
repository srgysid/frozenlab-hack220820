<?php

use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model \common\models\Company */

//echo '<pre>'.print_r($model->phones, true).'</pre>';

//echo '<pre>'.print_r($companyPhones, true).'</pre>';
?>

<div >

    <?php $form = ActiveForm::begin(
        [
            'id' => 'company-form-id',
            'enableAjaxValidation' => true,
        ]

    ); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['placeholder' => 'Наименование компании']) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'description')->textarea(['placeholder' => 'Описание компании']) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'url')->textInput(['placeholder' => 'Адрес сайта компании']) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email компании']) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'inn')->textInput(['placeholder' => 'ИНН']) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'ogrn')->textInput(['placeholder' => 'ОГРН']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5>Адрес</h5>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'city_id')->widget(Select2::className(), [
                'data' => $cities,
                'options' => [
                    'id' => 'company-city-id',
                    'placeholder' => 'Выберете из списка'
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'street_id')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'data' => $streets,
                'options' => [
                    'id' => 'company-street-id',
                    'placeholder' => '--',
                ],
                'pluginOptions' => [
                    'depends' => ['company-city-id'],
                    'placeholder' => 'Выберете из списка',
                    'url' => Url::to(['/company/street-list']),
                    'loadingText' => 'Загрузка...',
                    'emptyMsg' => '',
                ]
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'house_id')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'data' => $houses,
                'options' => [
                    'id' => 'company-house-id',
                    'placeholder' => '--',
                ],
                'pluginOptions' => [
                    'depends' => ['company-street-id'],
                    'placeholder' => 'Выберете из списка',
                    'url' => Url::to(['/company/house-list']),
                    'loadingText' => 'Загрузка...',
                    'emptyMsg' => ' ',
                ]
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'real_estate_num')->textInput(['placeholder' => '№ офиса/пом']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5>Часы работы</h5>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'opening_hours_from')->widget(MaskedInput::className(), [
                'mask' => '99:99',
            ]) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'opening_hours_to')->widget(MaskedInput::className(), [
                'mask' => '99:99',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5>Телефоны</h5>
        </div>
        <div class="col-md-12">
            <?php foreach ($model->getCompanyPhones() as $index => $companyPhone): ?>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($companyPhone, "[$index]phone")->widget(MaskedInput::className(),[
                        'mask'=>'+7 (999) 999-99-99',
                        'clientOptions' => [
                            'removeMaskOnSubmit' => true,
//                            'autoUnmask' => true,
                        ],
                    ])
                    ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($companyPhone, "[$index]description")->textInput(['placeholder' => 'Описание']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-walive btn-success']) ?>
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
