<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;

$Js = <<<SCRIPT
$(document).ready(function() {
    var token = $(document.getElementById('daDataToken'));
    var street_id = $(document.getElementById('street-id'));
    var house = $(document.getElementById('house-num'));
    
    var fias_guid = $(document.getElementById('house-fias_guid'));
    var kladr_guid = $(document.getElementById('house-kladr_guid'));
    
    if (!fias_guid.val()) fias_guid.val('00000000-0000-0000-0000-000000000000');
    if (!kladr_guid.val()) kladr_guid.val('0000000000000');

    street_id.change(function(){
        fias_guid.val('00000000-0000-0000-0000-000000000000');
        kladr_guid.val('0000000000000');
        house.val('');

        if (street_id.val()) {
            get_house_data();
        }
    });
    if (street_id.val()) {
        get_house_data()
    };

    function get_house_data(){
        $.ajax({
            url: '/house/street-by-id',
            method: 'POST',
            data: {'street_id': street_id.val()},
            success: function(data){	        
                house.suggestions({
                    token: token.val(),
                    type: "ADDRESS",
                    hint: false,
                    constraints: {
                        locations: {kladr_id: data['kladr_guid']},
                        restrict_value: true,
                    },
                    bounds: "house",
                    count: 10,
                    formatResult: formatResult,
                    formatSelected: formatSelected,
                    onSelect: function(suggestion) {
                        fias_guid.val(suggestion.data.fias_id);
                        kladr_guid.val(suggestion.data.kladr_id);
//                        console.log(suggestion);
                    }
                });
            }
        });
    }
    function formatResult(value, currentValue, suggestion) {
        if (suggestion.data.block != null){
            var addressValue = suggestion.data.house_type+' '+suggestion.data.house+' '+suggestion.data.block_type+' '+suggestion.data.block;
        }
        else {
            var addressValue = suggestion.data.house_type+' '+suggestion.data.house;
        }
        return addressValue;
    }
    function formatSelected(suggestion){
        if (suggestion.data.block != null){
            var addressValue = suggestion.data.house+' '+suggestion.data.block_type+' '+suggestion.data.block;
        }
        else {
            var addressValue = suggestion.data.house;
        }
        return addressValue;
    }
});

SCRIPT;
$this->registerJs($Js);

//echo '<pre>'.print_r($model, true).'</pre>';
?>

<div >

    <?php $form = ActiveForm::begin(
        [
            'id' => 'house-form-id',
            'enableAjaxValidation' => true,
        ]
    ); ?>

    <div class="row">
        <div class="col-md-12">
            <input type="hidden" id="daDataToken" value="<?= Yii::$app->params['daDataToken']?>">
        </div>

        <div class="col-md-12">
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
                            'url' => Url::to(['/house/street-list'])
                        ]
                    ]) ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'num')->textInput(['placeholder' => 'Номер дома']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'area')->textInput(['placeholder' => 'Общая площадь']) ?>
                </div>

            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'fias_guid')->textInput(['readonly' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'kladr_guid')->textInput(['readonly' => true]) ?>
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
