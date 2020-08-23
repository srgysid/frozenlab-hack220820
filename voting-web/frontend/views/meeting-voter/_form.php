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
            'id' => 'meeting-voter-form-id',
            'enableAjaxValidation' => true,
        ]
    ); ?>

    <div class="row">
        <?php foreach ($modelsMeetingQuestion as $meetingQuestion):?>

            <div class="col-md-12">
                <h6>Вопрос № <?= $model->arrValue[$meetingQuestion->order_num]['order_num'].': '.$model->arrValue[$meetingQuestion->order_num]['topic']?> </h6>
            </div>
            <div class="col-md-12">
                <label>Предложение по вопросу: <?= $model->arrValue[$meetingQuestion->order_num]['proposal']?> </label>
            </div>
            <div class="col-md-3">
                <label>Выбор по вопросу:</label>
            </div>

            <div class="col-md-3">
                <?= $form->field($model, "arrValue[$meetingQuestion->order_num][choice]")->dropDownList($choiceList)->label(false)?>
            </div>
            <div class="col-md-12">
                <hr>
            </div>
        <?php endforeach;?>
    </div>
    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-walive btn-success']) ?>
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
