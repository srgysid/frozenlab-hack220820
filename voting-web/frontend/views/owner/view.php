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
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="type_owner_id"><?= $model->getAttributeLabel('type_owner_id') ?></label>
                <input type="text" id="type_owner_id" class="form-control" title="<?= $model->typeOwner->name?>"  value="<?= $model->typeOwner->name?>" readonly>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'name')->textInput(['readonly' => true, 'title' => $model->name]) ?>
        </div>
        <div class="col-md-4">
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

        <?php if (isset($model->type_owner_id) && ($model->type_owner_id == TypeOwner::LEGAL_ENTITY)):?>
            <div class="col-md-8">
                <?= $form->field($model, 'legal_form')->textInput(['readonly' => true, 'title' => $model->legal_form]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'ogrn')->textInput(['readonly' => true, 'title' => $model->ogrn]) ?>
            </div>
        <?php endif;?>
        <?php if (isset($model->type_owner_id) && ($model->type_owner_id == TypeOwner::PHYSICAL_ENTITY)):?>
            <div class="col-md-12">
                <?= $form->field($model, 'passport')->textInput(['readonly' => true, 'title' => $model->passport]) ?>
            </div>
        <?php endif;?>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="city_id"><?= $model->getAttributeLabel('city_id') ?></label>
                <input type="text" id="city_id" class="form-control" title="<?= $model->realEstate->house->street->city->name?>"  value="<?= $model->realEstate->house->street->city->name?>" readonly>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="street_id"><?= $model->getAttributeLabel('street_id') ?></label>
                <input type="text" id="street_id" class="form-control" title="<?= ($model->real_estate_id)? $model->realEstate->house->street->pref_short.' '.$model->realEstate->house->street->name: ""?>"  value="<?= ($model->real_estate_id)? $model->realEstate->house->street->pref_short.' '.Html::encode($model->realEstate->house->street->name): ""?>" readonly>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="house_id"><?= $model->getAttributeLabel('house_id') ?></label>
                <input type="text" id="house_id" class="form-control" title="<?= ($model->real_estate_id)? $model->realEstate->house->num: ""?>"   value="<?= ($model->real_estate_id)? $model->realEstate->house->num: ""?>" readonly>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="real_estate_id"><?= $model->getAttributeLabel('real_estate_id') ?></label>
                <input type="text" id="real_estate_id" class="form-control" title="<?= ($model->real_estate_id)? $model->realEstate->realEstateType->short_name.' '.$model->realEstate->num: ""?>" value="<?= ($model->real_estate_id)? $model->realEstate->realEstateType->short_name.' '.$model->realEstate->num: ""?>" readonly>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'ownership')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'percent_own')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($model, 'address')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput(['readonly' => true]) ?>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>
    <?php ActiveForm::end(); ?>

</div>
