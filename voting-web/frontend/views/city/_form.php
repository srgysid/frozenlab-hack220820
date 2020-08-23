<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;

//echo '<pre>'.print_r($model, true).'</pre>';
$Js = <<<SCRIPT
$(document).ready(function() {
    var token = $(document.getElementById('daDataToken'));
    var region_id = $(document.getElementById('region-id'));
    var city = $(document.getElementById('city-name'));
    
    var fias_guid = $(document.getElementById('city-fias_guid'));
    var kladr_guid = $(document.getElementById('city-kladr_guid'));
    var pref_name = $(document.getElementById('city-pref_name'));
    var pref_short = $(document.getElementById('city-pref_short'));
    
    if (!fias_guid.val()) fias_guid.val('00000000-0000-0000-0000-000000000000');
    if (!kladr_guid.val()) kladr_guid.val('0000000000000');
    if (!pref_name.val()) pref_name.val('');
    if (!pref_short.val()) pref_short.val('');

    region_id.change(function(){
//        console.log(region_id.val());
        fias_guid.val('00000000-0000-0000-0000-000000000000');
        kladr_guid.val('0000000000000');
        pref_name.val('');
        pref_short.val('');
        city.val('');

        if (region_id.val()) {
            get_city_data();
        }
    });
    if (region_id.val()) {
        get_city_data()
    };

    function get_city_data(){
        $.ajax({
            url: '/city/region-by-id',
            method: 'POST',
            data: {'region_id': region_id.val()},
            success: function(data){	        
                city.suggestions({
                    token: token.val(),
                    type: "ADDRESS",
                    constraints: {
                        locations: {kladr_id: data['kladr_guid']},
                        restrict_value: true,
                    },
                    bounds: "city",
                    count: 10,
                    formatResult: formatResult,
                    formatSelected: formatSelected,
                    onSelect: function(suggestion) {
                        fias_guid.val(suggestion.data.fias_id);
                        kladr_guid.val(suggestion.data.kladr_id);
                        pref_name.val(suggestion.data.city_type_full);
                        pref_short.val(suggestion.data.city_type+'.');
                        console.log(suggestion);
                    }
                });
            }
        });
    }
    function formatResult(value, currentValue, suggestion) {
        var addressValue = suggestion.data.city_with_type;
        return addressValue;
    }
    function formatSelected(suggestion){
        var addressValue = suggestion.data.city;  
        return addressValue;
    }
});

SCRIPT;
$this->registerJs($Js);

?>

<div >

    <?php $form = ActiveForm::begin(
        [
            'id' => 'city-form-id',
            'enableAjaxValidation' => true,
        ]
    ); ?>

    <div class="row">
        <div class="col-md-12">
            <input type="hidden" id="daDataToken" value="<?= Yii::$app->params['daDataToken']?>">
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'region_id')->widget(Select2::className(), [
                'data' => $regions,
                'options' => [
                    'id' => 'region-id',
                    'placeholder' => 'Выберите из списка'
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'district_id')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'data' => $districts,
                'options' => ['id' => 'district-id', 'placeholder' => '--',],
                'pluginOptions' => [
                    'depends' => ['region-id'],
                    'placeholder' => 'Выберите из списка',
                    'url' => Url::to(['/city/district-list'])
                ]
            ]) ?>
        </div>

    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['placeholder' => 'Название города']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'pref_name')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'pref_short')->textInput(['readonly' => true]) ?>
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
