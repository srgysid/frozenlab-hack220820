<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use kartik\select2\Select2;

$Js = <<<SCRIPT
$(document).ready(function() {
    var token = $(document.getElementById('daDataToken'));
    var city_id = $(document.getElementById('city-id'));
    var street = $(document.getElementById('street-name'));
    
    var pref_short = $(document.getElementById('street-pref_short'));
    var pref_name = $(document.getElementById('street-pref_name'));
    
    var fias_guid = $(document.getElementById('street-fias_guid'));
    var kladr_guid = $(document.getElementById('street-kladr_guid'));
    
    if (!fias_guid.val()) fias_guid.val('00000000-0000-0000-0000-000000000000');
    if (!kladr_guid.val()) kladr_guid.val('0000000000000');

    city_id.change(function(){
//        console.log(region_id.val());
        fias_guid.val('00000000-0000-0000-0000-000000000000');
        kladr_guid.val('0000000000000');
        street.val('');

        if (city_id.val()) {
            get_street_data();
        }
    });
    if (city_id.val()) {
        get_street_data()
    };

    function get_street_data(){
        $.ajax({
            url: '/street/city-by-id',
            method: 'POST',
            data: {'city_id': city_id.val()},
            success: function(data){	        
                street.suggestions({
                    token: token.val(),
                    type: "ADDRESS",
                    constraints: {
                        locations: {kladr_id: data['kladr_guid']},
                        restrict_value: true,
                    },
                    bounds: "street",
                    count: 10,
                    formatResult: formatResult,
                    formatSelected: formatSelected,
                    onSelect: function(suggestion) {
                        fias_guid.val(suggestion.data.fias_id);
                        kladr_guid.val(suggestion.data.kladr_id);
                        pref_name.val(suggestion.data.street_type_full);
                        pref_short.val(suggestion.data.street_type+'.');
//                        console.log(suggestion);
                    }
                });
            }
        });
    }
    function formatResult(value, currentValue, suggestion) {
        if (suggestion.data.settlement_with_type != null){
            var addressValue = suggestion.data.settlement_with_type+', '+suggestion.data.street_type+' '+suggestion.data.street;
        }
        else {
            var addressValue = suggestion.data.street_type+' '+suggestion.data.street;
        }
        return addressValue;
    }
    function formatSelected(suggestion){
        var addressValue = suggestion.data.street;  
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
            'id' => 'strteet-form-id',
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

            </div>

        </div>

        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput(['placeholder' => 'Наименование улицы']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'pref_name')->textInput(['readonly' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'pref_short')->textInput(['readonly' => true]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'fias_guid')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'kladr_guid')->textInput(['readonly' => true]) ?>
        </div>

    </div>

    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-walive btn-success']) ?>
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
