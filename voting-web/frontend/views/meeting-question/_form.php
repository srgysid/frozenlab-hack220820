<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;

$Js = <<<SCRIPT
$(document).ready(function () {
    $('#question-id').change(function() {
        var question_id = $('#question-id').val();
        if (question_id){
            $.ajax({
                url: '/meeting-question/question-data',
                method: 'POST',
                data: {'question_id': question_id},
                success: function(data){	        
                    $('#meetingquestion-topic').val(data['topic']);
                    $('#meetingquestion-proposal').val(data['proposal']);
                }
            });
        }
    });
});
SCRIPT;
$this->registerJs($Js);

//echo '<pre>'.print_r($model, true).'</pre>';

?>

<div >

    <?php $form = ActiveForm::begin(
        [
            'id' => 'meeting-question-form-id',
            'enableAjaxValidation' => true,
        ]
    ); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'order_num')->textInput(['placeholder' => 'Порядковый номер вопроса']) ?>
        </div>

    </div>
    <div class="row">
        <div class="col-md-6">
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
        <div class="col-md-6">
            <?= $form->field($model, 'question_id')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'data' => $questions,
                'options' => ['id' => 'question-id', 'placeholder' => '--',],
                'pluginOptions' => [
                    'depends' => ['title-id'],
                    'placeholder' => 'Выберете из списка',
                    'url' => Url::to(['/meeting-question/question-list'])
                ]
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'topic')->textInput(['placeholder' => 'Описание вопроса']) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($model, 'proposal')->textInput(['placeholder' => 'Предложение по вопросу']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'quorum')->textInput(['placeholder' => 'Кворум вопроса в %']) ?>
        </div>
    </div>

    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-walive btn-success']) ?>
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
