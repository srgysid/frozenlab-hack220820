<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use kartik\select2\Select2;

//echo '<pre>'.print_r($model, true).'</pre>';

?>

<div >

    <?php $form = ActiveForm::begin(
        [
            'id' => 'title-question-form-id',
            'enableAjaxValidation' => true,
        ]
    ); ?>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'title_id')->widget(Select2::className(), [
                'data' => $titles,
                'options' => [
                    'id' => 'title-id',
                    'placeholder' => 'Выберите из списка'
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>
        </div>

        <div class="col-md-12">
            <?= $form->field($model, 'question_id')->widget(Select2::className(), [
                'data' => $questions,
                'options' => [
                    'id' => 'question-id',
                    'placeholder' => 'Выберите из списка'
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>
        </div>

    </div>

    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-walive btn-success']) ?>
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
