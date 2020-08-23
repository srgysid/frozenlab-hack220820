<?php
use yii\widgets\MaskedInput;
use kartik\datecontrol\DateControl;

//echo '<pre>'.print_r($model, true).'</pre>';
?>
<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'started_at')->widget(DateControl::classname(), [
            'options' => ['placeholder' => 'Введите дату ...'],
            'type' => DateControl::FORMAT_DATETIME,
            'displayFormat' => 'php:d.m.Y H:i',
            'saveFormat' => 'php:Y-m-d H:i:sO',
//            'displayTimezone' => Yii::$app->user->identity->userProfile->timezone,
            'widgetOptions' => [
                'pluginOptions' => [
                    'autoclose' => true,
                    'startDate' => date('Y-m-d H:i')
                ],
                'options' => ['autocomplete' => 'off']
            ]
        ]) ?>
    </div>
    <div class="col-md-8">
        <?= $form->field($model, 'meeting_place')->textInput(['placeholder' => 'Место проведения']) ?>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'distant_started_at')->widget(DateControl::classname(), [
            'options' => ['placeholder' => 'Введите дату ...'],
            'type' => DateControl::FORMAT_DATETIME,
            'displayFormat' => 'php:d.m.Y H:i',
            'saveFormat' => 'php:Y-m-d H:i:sO',
//            'displayTimezone' => Yii::$app->user->identity->userProfile->timezone,
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
        <?= $form->field($model, 'finished_at')->widget(DateControl::classname(), [
            'options' => ['placeholder' => 'Введите дату ...'],
            'type' => DateControl::FORMAT_DATETIME,
            'displayFormat' => 'php:d.m.Y H:i',
            'saveFormat' => 'php:Y-m-d H:i:sO',
//            'displayTimezone' => Yii::$app->user->identity->userProfile->timezone,
            'widgetOptions' => [
                'pluginOptions' => [
                    'autoclose' => true,
                    'startDate' => date('Y-m-d H:i')
                ],
                'options' => ['autocomplete' => 'off']
            ]
        ]) ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model, 'receiving_place')->textInput(['placeholder' => 'Место приёма бюллетеней']) ?>
    </div>
</div>
