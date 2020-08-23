<?php

use yii\helpers\Html;
use yii\helpers\Url;
//use yii\bootstrap4\ActiveForm;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\helpers\ArrayHelper;
use common\helpers\AuthHelper;
use kartik\depdrop\DepDrop;

//$form->field($model, 'username', ['addon' => ['append' => ['content' => '@'.Yii::$app->params['username_domain']]],])->textInput()
?>

<div class="employee-form">

    <?php $form = ActiveForm::begin(
        [
            'id' => 'employee-form-id',
            'enableAjaxValidation' => true,
        ]
    ); ?>

    <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'username')->textInput()?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($model, 'email')->textInput()?>
                    </div>

                    <div class="col-md-12">
                        <?= $form->field($model, 'second_name')->textInput()?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($model, 'first_name')->textInput()?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($model, 'third_name')->textInput()?>
                    </div>
                    <?php if ($model->scenario == \common\models\Employee::SCENARIO_REGISTER): ?>
                        <div class="col-md-12">
                            <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Введите пароль пользователя'])?>
                        </div>
                        <div class="col-md-12">
                            <?= $form->field($model, 'password_repeat')->passwordInput(['placeholder' => 'Повторно введите пароль пользователя'])?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'phone')->widget(MaskedInput::className(),[
                            'mask'=>'+7 (999) 999-99-99',
                            'clientOptions' => [
                                'removeMaskOnSubmit' => true,
                                'autoUnmask' => true,
                            ],
                        ])
                        ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($model, 'role')
                            ->radioList($roles, [
                                'item' => function ($index, $label, $name, $checked, $value) {
                                    return '<label class="' . ($checked ? ' active' : '') . '">' .
                                        Html::radio($name, $checked, ['value' => $value, 'class' => 'project-status-btn']) . $label . '</label><br>';
                                },
                            ])
                        ?>
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
