<?php
use yii\helpers\ArrayHelper;
use common\models\Company;
use kartik\select2\Select2;

//echo '<pre>'.print_r($company, true).'</pre>';
?>
<div class="row">

    <div class="col-md-6">
        <?= $form->field($model, 'initiator_company_id')->widget(Select2::className(), [
            'data' => $company,
            'options' => [
                'id' => 'initiator-company-id',
                'placeholder' => 'Выберете из списка'
            ],
            'pluginOptions' => [
                    'allowClear' => true
                ],
            ])
        ?>
    </div>
</div>
