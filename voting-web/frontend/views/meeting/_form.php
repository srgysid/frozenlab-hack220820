<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use kartik\datecontrol\DateControl;
use yii\widgets\MaskedInput;
use common\models\Meeting;
use common\models\FormVoting;
use yii\db\Query;

$Js = <<<SCRIPT
function modalReload(){
    var data_form = $('#meeting-form-id').serialize();
    $.ajax({
        url: $('#meeting-form-id').attr('action'),
        method: 'POST',
        data: data_form,
        success: function(data){
        $('#modalContent').html(data);
    }
    });
};
$(document).ready(function () {
    $('#house-id').change(function() {
        var house_id = $('#house-id').val();
        if (house_id){
            $.ajax({
                url: '/meeting/area-by-house',
                method: 'POST',
                data: {'house_id': house_id},
                success: function(data){	        
                    $('#meeting-area').val(data['area']);
                }
            });
        }
    });
    $('#street-id').change(function() {
        $('#meeting-area').val('');
    });
    $('#meeting-form_voting_id :radio').change(function(){
        modalReload();
    });
    $('#meeting-type_administrator :radio').change(function(){
        modalReload();
    });
    $('#meeting-type_initiator :radio').change(function(){
        modalReload();
    });
    $('#meeting-meeting_place').focusin(function(){
        var meeting_place = $('#meeting-meeting_place').val();
        if (meeting_place.trim() == ''){
            var house_id = $('#house-id').val();
            if (house_id){
                $.ajax({
                    url: '/meeting/current-address',
                    method: 'POST',
                    data: {'house_id': house_id},
                    success: function(data){	        
                    $('#meeting-meeting_place').val(data['address']);
                    }
                });
            }
        }
    });
    $('#meeting-receiving_place').focusin(function(){
        var receiving_place = $('#meeting-receiving_place').val();
        if (receiving_place.trim() == ''){
            var house_id = $('#house-id').val();
            if (house_id){
                $.ajax({
                    url: '/meeting/current-address',
                    method: 'POST',
                    data: {'house_id': house_id},
                    success: function(data){	        
                    $('#meeting-receiving_place').val(data['address']);
                    }
                });
            }
        }
    });
    $('#meeting-familiarization_place').focusin(function(){
        var familiarization_place = $('#meeting-familiarization_place').val();
        if (familiarization_place.trim() == ''){
            var house_id = $('#house-id').val();
            if (house_id){
                $.ajax({
                    url: '/meeting/current-address',
                    method: 'POST',
                    data: {'house_id': house_id},
                    success: function(data){	        
                    $('#meeting-familiarization_place').val(data['address']);
                    }
                });
            }
        }
    });
});
SCRIPT;
$this->registerJs($Js);

$tmpId = null;
if (!$model->isNewRecord) {
    $tmpId = $model->id;
}

//echo '<pre>'.print_r($company, true).'</pre>';
?>

<div >
    <?php $form = ActiveForm::begin(
        [
            'id' => 'meeting-form-id',
            'enableAjaxValidation' => true,
            'validationUrl' => \yii\helpers\Url::to(['validate-form', 'id'=>$tmpId]),
        ]
    ); ?>

    <div class="row">

        <?php if ($model->isNewRecord):?>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'city_id')->widget(Select2::className(), [
                        'data' => $cities,
                        'options' => [
                            'id' => 'city-id',
                            'placeholder' => 'Выберите из списка'
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
                            'placeholder' => 'Выберите из списка',
                            'url' => Url::to(['/meeting/street-list'])
                        ]
                    ]) ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'house_id')->widget(DepDrop::classname(), [
                        'type' => DepDrop::TYPE_SELECT2,
                        'data' => $houses,
                        'options' => ['id' => 'house-id', 'placeholder' => '--',],
                        'pluginOptions' => [
                            'depends' => ['street-id'],
                            'placeholder' => 'Выберите из списка',
                            'url' => Url::to(['/meeting/house-list'])
                        ]
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'area')->textInput(['placeholder' => 'чиловое']) ?>
                </div>

            </div>
        </div>
        <?php else: ?>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="city_id"><?= $model->getAttributeLabel('city_id') ?></label>
                    <input type="text" id="city_id" class="form-control" title="<?= $model->house->street->city->name?>"  value="<?= $model->house->street->city->name?>" readonly>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="street_id"><?= $model->getAttributeLabel('street_id') ?></label>
                    <input type="text" id="street_id" class="form-control" title="<?= $model->house->street->pref_short.' '.$model->house->street->name?>"  value="<?= $model->house->street->pref_short.' '.Html::encode($model->house->street->name)?>" readonly>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="house_id"><?= $model->getAttributeLabel('house_id') ?></label>
                    <input type="text" id="house_id" class="form-control" title="<?= $model->house->num?>"   value="<?= $model->house->num?>" readonly>
                </div>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'area')->textInput(['placeholder' => 'чиловое']) ?>
            </div>

        <?php endif ?>
    </div>
    <div class="row">
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'type_voting_id')->radioList($type_voting) ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'form_voting_id')->radioList($form_voting) ?>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div id="form_voting_block">
                <?php
                if (isset($model->form_voting_id) && ($model->form_voting_id == FormVoting::INTRAMURAL)) {
                    echo $this->render('intramural', [
                        'model' => $model,
                        'form' => $form,
                    ]);
                }
                if (isset($model->form_voting_id) && ($model->form_voting_id == FormVoting::DISTANT)) {
                    echo $this->render('distant', [
                        'model' => $model,
                        'form' => $form,
                    ]);
                }
                if (isset($model->form_voting_id) && ($model->form_voting_id == FormVoting::FULL_TIME)) {
                    echo $this->render('full_time', [
                        'model' => $model,
                        'form' => $form,
                    ]);
                }
                ?>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'familiarization_place')->textInput(['placeholder' => 'Место ознакомления']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'familiarization_date_from')->widget(DateControl::classname(), [
                        'options' => ['placeholder' => 'Введите дату ...',],
                        'type' => DateControl::FORMAT_DATE,
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d H:i:sO',
                        'widgetOptions' => [
                            'pluginOptions' => [
                                'autoclose' => true,
                                'startDate' => date('Y-m-d H:i')
                            ],
                            'options' => ['autocomplete' => 'off']
                        ]
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'familiarization_date_to')->widget(DateControl::classname(), [
                        'options' => ['placeholder' => 'Введите дату ...',],
                        'type' => DateControl::FORMAT_DATE,
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d H:i:sO',
                        'widgetOptions' => [
                            'pluginOptions' => [
                                'autoclose' => true,
                                'startDate' => date('Y-m-d H:i')
                            ],
                            'options' => ['autocomplete' => 'off']
                        ]
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'familiarization_time_from')->widget(MaskedInput::className(),[
                        'mask' => '12:32',
                        'definitions' => [
                            '1' => ['validator' => '[0-2]'],
                            '2' => ['validator' => '[0-9]'],
                            '3' => ['validator' => '[0-5]'],
                        ],
                        'options' => [
                            'id' => 'time_from-id',
                        ],
                        'clientOptions' => [
//                            'removeMaskOnSubmit' => true,
//                            'autoUnmask' => true,
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'familiarization_time_to')->widget(MaskedInput::className(),[
                        'mask' => '12:32',
                        'definitions' => [
                            '1' => ['validator' => '[0-2]'],
                            '2' => ['validator' => '[0-9]'],
                            '3' => ['validator' => '[0-5]'],
                        ],
                        'options' => [
                            'id' => 'time_to-id',
                        ],
                        'clientOptions' => [
//                            'removeMaskOnSubmit' => true,
//                            'autoUnmask' => true,
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'type_initiator')->radioList(Meeting::getInitiatorList()) ?>
        </div>
        <div class="col-md-9">
            <div id="initiator_block">
                <?php
                if (isset($model->type_initiator) && ($model->type_initiator == Meeting::INITIATOR_COMPANY)) {
                    echo $this->render('initiator_company', [
                        'model' => $model,
                        'form' => $form,
                        'company' => $company,
                    ]);
                }
                if (isset($model->type_initiator) && ($model->type_initiator == Meeting::INITIATOR_OWNER)) {
                    echo $this->render('initiator_owners', [
                        'model' => $model,
                        'form' => $form,
                        'owners' => $owners,
                    ]);
                }
                ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'type_administrator')->radioList(Meeting::getInitiatorList()) ?>
        </div>
        <div class="col-md-9">
            <div id="administrator_block">
                <?php
                if (isset($model->type_administrator) && ($model->type_administrator == Meeting::INITIATOR_COMPANY)) {
                    echo $this->render('administrator_company', [
                        'model' => $model,
                        'form' => $form,
                        'company' => $company,
                    ]);
                }
                if (isset($model->type_administrator) && ($model->type_administrator == Meeting::INITIATOR_OWNER)) {
                    echo $this->render('administrator_owners', [
                        'model' => $model,
                        'form' => $form,
                        'owners' => $owners,
                    ]);
                }
                ?>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-walive btn-success']) ?>
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
