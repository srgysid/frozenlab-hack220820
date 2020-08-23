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


echo '<pre>'.print_r($reestr_id, true).'</pre>';
?>

<div >
    <?php $form = ActiveForm::begin(
        [
            'enableAjaxValidation' => false,
        ]
    ); ?>

    <div class="row">

    </div>
    <div class="row">

    </div>
    <div class="row">

    </div>
    <div class="row">

    </div>

    <div class="row">
    </div>

    <div class="modal-footer">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-walive btn-success']) ?>
        <button type="button" class="btn btn-walive btn-danger" data-dismiss="modal">Закрыть</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
