<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

//echo '<pre>'.print_r($model, true).'</pre>';

?>

<div >

    <?php $form = ActiveForm::begin(
        [
            'id' => 'question-form-id',
            'enableAjaxValidation' => true,
        ]
    ); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'short_name')->textInput(['placeholder' => 'Сокращенное описание вопроса']) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($model, 'topic')->textInput(['placeholder' => 'Описание вопроса']) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($model, 'proposal')->textInput(['placeholder' => 'Предложение по вопросу']) ?>
        </div>

    </div>

    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-walive btn-success']) ?>
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
