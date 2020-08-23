<?php
use yii\helpers\Url;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use common\models\Owner;
use yii\helpers\ArrayHelper;

//$owners = ArrayHelper::map(Owner::getOwnerByHouse($model->house_id), 'id', 'name');

//echo '<pre>'.print_r($model, true).'</pre>';
?>
<div class="row">

    <div class="col-md-6">
        <?= $form->field($model, 'administrator_owner_id')->widget(DepDrop::classname(), [
            'type' => DepDrop::TYPE_SELECT2,
            'data' => $owners,
            'options' => ['id' => 'administrator-owner-id', 'placeholder' => '--',],
            'pluginOptions' => [
                'depends' => ['house-id'],
                'placeholder' => 'Выберете из списка',
                'url' => Url::to(['/meeting/owner-list'])
            ]
        ]) ?>
    </div>
</div>
