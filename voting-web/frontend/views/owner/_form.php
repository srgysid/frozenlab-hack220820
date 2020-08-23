<?php

use common\models\TypeOwner;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$Js = <<<SCRIPT
$(document).ready(function () {
    $('#owner-type_owner_id :radio').change(function(){
        var data_form = $('#owner-form-id').serialize();
        $.ajax({
            url: $('#owner-form-id').attr('action'),
            method: 'POST',
            data: data_form,
            success: function(data){
                $('#modalContent').html(data);
            }
        });
    });
});
SCRIPT;
$this->registerJs($Js);
//echo '<pre>'.print_r($model, true).'</pre>';
?>

<div>

    <?php $form = ActiveForm::begin(
        [
            'id' => 'owner-form-id',
            'enableAjaxValidation' => true,
            'validationUrl' => \yii\helpers\Url::to(['validate-form']),
        ]
    ); ?>

    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'type_owner_id')->radioList($type_owner) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
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

                <div class="col-md-6">
                    <?= $form->field($model, 'street_id')->widget(DepDrop::classname(), [
                        'type' => DepDrop::TYPE_SELECT2,
                        'data' => $streets,
                        'options' => ['id' => 'street-id', 'placeholder' => '--',],
                        'pluginOptions' => [
                            'depends' => ['city-id'],
                            'placeholder' => 'Выберете из списка',
                            'url' => Url::to(['/owner/street-list'])
                        ]
                    ]) ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'house_id')->widget(DepDrop::classname(), [
                        'type' => DepDrop::TYPE_SELECT2,
                        'data' => $houses,
                        'options' => ['id' => 'house-id', 'placeholder' => '--',],
                        'pluginOptions' => [
                            'depends' => ['street-id'],
                            'placeholder' => 'Выберете из списка',
                            'url' => Url::to(['/owner/house-list'])
                        ]
                    ]) ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'real_estate_id')->widget(DepDrop::classname(), [
                        'type' => DepDrop::TYPE_SELECT2,
                        'data' => $real_estate,
                        'options' => ['id' => 'real-estate-id', 'placeholder' => '--',],
                        'pluginOptions' => [
                            'depends' => ['house-id'],
                            'placeholder' => 'Выберете из списка',
                            'url' => Url::to(['/owner/real-estate-list'])
                        ]
                    ]) ?>
                </div>

                <div class="col-md-8">
                    <?= $form->field($model, 'ownership')->textInput(['placeholder' => 'Реквизиты документа']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'percent_own')->textInput(['placeholder' => 'Доля']) ?>
                </div>

            </div>
        </div>
    </div>
    <div id="type_owner_block">
        <?php
        if (isset($model->type_owner_id) && ($model->type_owner_id == TypeOwner::LEGAL_ENTITY)) {
            echo $this->render('legal_entity', [
                'model' => $model,
                'form' => $form,
            ]);
        }
        if (isset($model->type_owner_id) && ($model->type_owner_id == TypeOwner::PHYSICAL_ENTITY)) {
            echo $this->render('physical_entity', [
                'model' => $model,
                'form' => $form,
            ]);
        }
        ?>
    </div>


    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-walive btn-success']) ?>
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
