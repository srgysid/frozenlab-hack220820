<?php

use common\models\TypeOwner;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\widgets\MaskedInput;

\yii\web\YiiAsset::register($this);

//echo '<pre>'.print_r($model, true).'</pre>';
?>
<style>
    .issue-view .form-control[readonly] {
        background-color: #fff;
    }
</style>
<div class="issue-view">

    <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label" for="type_owner_id"><?= $model->getAttributeLabel('type_owner_id') ?></label>
                <input type="text" id="type_owner_id" class="form-control" title="<?= $model->typeOwner->name?>"  value="<?= $model->typeOwner->name?>" readonly>
            </div>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['readonly' => true, 'title' => $model->name]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'type_real_estate')->textInput(['readonly' => true, 'title' => $model->type_real_estate]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'num')->textInput(['readonly' => true, 'title' => $model->num]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'area')->textInput(['readonly' => true, 'title' => $model->area]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'part')->textInput(['readonly' => true, 'title' => $model->part]) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($model, 'ownership')->textInput(['readonly' => true, 'title' => $model->ownership]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput(['readonly' => true, 'title' => $model->email]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'phone')->widget(MaskedInput::className(),[
                'mask'=>'+7 (999) 999-99-99',
                'options' => [
                    'readonly' => true,
                ],
                'clientOptions' => [
                    'removeMaskOnSubmit' => true,
                ],
            ])
            ?>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>
    <?php ActiveForm::end(); ?>

</div>
