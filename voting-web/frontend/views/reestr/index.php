<?php

use kartik\date\DatePicker;
use kartik\grid\GridView;
use kartik\select2\Select2;
use frontend\assets\AppAsset;
use yii\bootstrap4\ButtonDropdown;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

AppAsset::register($this);

$address = $modelHouse->street->city->name.', '.$modelHouse->street->pref_short.' '.$modelHouse->street->name.', д. '.$modelHouse->num;
$this->title = Yii::t('app', 'Реестры дома: '.$address);
$this->params['breadcrumbs'][] = $this->title;

//echo '<pre>'.print_r($modelOwners, true).'</pre>';
?>
<div class="flex-main-content">
    <h3><?= Html::encode($this->title) ?></h3>
    <p>
        <?= Html::a('Добавить реестр', [Url::to(['/reestr/create', 'house_id'=>$modelHouse->id])], ['class' => 'btn btn-walive btn-success']) ?>
        <?= Html::button(Yii::t('app', 'Импорт из Excel'), ['title' => 'Импорт из Excel', 'class' => 'btn btn-walive btn-success']) ?>
        <?= Html::a('Очистить фильтр', [Url::to(['/reestr/clear-filter', 'house_id'=>$modelHouse->id])], ['class' => 'btn btn-walive btn-danger']) ?>
        <?= Html::a('Дома', ['/house/index'], ['class' => 'btn btn-walive btn-primary']) ?>
    </p>
    <?php
    Modal::begin([
        'title'=>'<h3 id="modalHeader"></h3>',
        'id'=>'modal',
        'size'=>'modal-lg',
    ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
    ?>
    <div class="text-right">
        <div class="btn-group exp-btn btn-dropdown">
            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Показать <?=$dataProvider->pagination->pageSize?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu exp-btn">
                <li><?= Html::a(20, Url::current(['per-page' => 20]), ['class' => 'dropdown-item'])?></li>
                <li><?= Html::a(50, Url::current(['per-page' => 50]), ['class' => 'dropdown-item'])?></li>
                <li><?= Html::a(100, Url::current(['per-page' => 100]), ['class' => 'dropdown-item'])?></li>
            </ul>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'responsive' => false,
        'pager' => [
            'class' => '\common\widgets\LinkPagerWalive',
        ],
        'options' => ['class' => 'table-sm'],
        'columns' => [
            [
                'attribute' => 'reg_num',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['reg_num'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;text-align: center; width:40%;'
                ],
            ],

            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDate($data['created_at']).' '.Yii::$app->formatter->asTime($data['created_at']);
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;text-align: center;'
                ],
            ],

            [
                'class' => 'kartik\grid\ActionColumn',
                'hiddenFromExport' => true,
                'contentOptions' => [
                    'style' => 'width:80px;  min-width:80px; max-width:80px;'
                ],

                'buttons' => [
                    'all' => function ($url, $model) {
                        return '<div class="btn-group">'.ButtonDropdown::widget([
                            'label' => '...',
                            'options' => ['class' => 'btn btn-default dropleft'],
                            'dropdown' => [
                                'options' => ['class' => 'dropdown-menu'],
                                'items' => [
                                    [
                                        'label' => 'Строки',
                                        'url' => Url::to(['/reestr-detail/index', 'reestr_id'=>$model['id']]),
                                    ],

                                    [
                                        'label' => 'Удалить',
                                        'url' => Url::to(['/reestr/delete', 'id'=>$model['id']]),
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => Yii::t('app', 'Вы действительно хотите реестр №{0}?', $model['reg_num']),
                                                'method' => 'post',
                                            ],
                                        ]
                                    ],

                                ],
                            ],
                        ]).'</div>';

                    },
                ],
                'template' => '{all}'
            ],
        ],
    ]); ?>
</div>
