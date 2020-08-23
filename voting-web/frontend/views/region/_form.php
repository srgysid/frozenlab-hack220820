<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use kartik\select2\Select2;

//echo '<pre>'.print_r($model, true).'</pre>';

$Js = <<<SCRIPT
$(document).ready(function() {
    var token = $(document.getElementById('daDataToken'));
    var region = $(document.getElementById('region-name'));
    var pref_name = $(document.getElementById('region-pref_name'));
    var fias_guid = $(document.getElementById('region-fias_guid'));
    var kladr_guid = $(document.getElementById('region-kladr_guid'));
    
    if (!fias_guid.val()) fias_guid.val('00000000-0000-0000-0000-000000000000');
    if (!kladr_guid.val()) kladr_guid.val('0000000000000');

    region.suggestions({
        token: token.val(),
        type: "ADDRESS",
        hint: false,
        bounds: "region",
        formatSelected: formatSelected,
        onSelect: function(suggestion) {
            fias_guid.val(suggestion.data.fias_id);
            kladr_guid.val(suggestion.data.kladr_id);
            pref_name.val(suggestion.data.region_type_full);
//            console.log(suggestion);
        }
    });
    function formatSelected(suggestion){
        var addressValue = suggestion.data.region;  
        return addressValue;
    }

});
SCRIPT;
$this->registerJs($Js);

?>

<div >

    <?php $form = ActiveForm::begin(
        [
            'id' => 'region-form-id',
            'enableAjaxValidation' => true,
        ]
    ); ?>

    <div class="row">
        <div class="col-md-12">
            <input type="hidden" id="daDataToken" value="<?= Yii::$app->params['daDataToken']?>">
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['placeholder' => 'Наименование региона']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'pref_name')->textInput(['readonly' => true]) ?>
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
