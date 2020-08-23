<?php
use yii\helpers\Url;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use common\models\Owner;
use yii\helpers\ArrayHelper;

//echo '<pre>'.print_r($model, true).'</pre>';
//$owners = ArrayHelper::map(Owner::getOwnerByHouse($model->house_id), 'id', 'name');
?>
<div class="row">

    <div class="col-md-12">
        <?= $form->field($model, 'owner_ids')->widget(DepDrop::classname(), [
            'type' => DepDrop::TYPE_SELECT2,
            'data' => $owners,
            'options' => [
                'id' => 'initiator-owner-id',
                'placeholder' => '--',
                'multiple' => true,
            ],
            'pluginOptions' => [
                'depends' => ['house-id'],
                'placeholder' => 'Выберете из списка',
                'url' => Url::to(['/meeting/owner-list'])
            ]
        ]) ?>
    </div>
</div>
