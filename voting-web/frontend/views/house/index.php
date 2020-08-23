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

$this->title = Yii::t('app', 'Дома');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flex-main-content">
    <h3><?= Html::encode($this->title) ?></h3>
    <p>
        <?= Html::button(Yii::t('app', 'Добавить дом'), ['value'=>Url::to('/house/create'), 'title' => 'Добавить дом', 'class' => 'btn btn-walive btn-success modalButton']) ?>
        <?= Html::a('Очистить фильтр', ['/house/clear-filter'], ['class' => 'btn btn-walive btn-danger']) ?>
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
                'attribute' => 'city_name',
                'filter' => Select2::widget([
                    'theme' => Select2::THEME_KRAJEE_BS4,
                    'model' => $searchModel,
                    'attribute' => 'city_ids',
                    'value' => $searchModel['city_ids'],
                    'data' => $cities,
                    'options' => [
                        'placeholder' => Yii::t('app', 'Город ...'),
                        'multiple' => true,
                        'class' => 'label-warning',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['city_name'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:15%;'
                ],
            ],
            [
                'attribute' => 'street_name',
                'filter' => Select2::widget([
                    'theme' => Select2::THEME_DEFAULT,
                    'model' => $searchModel,
                    'attribute' => 'street_ids',
                    'value' => $searchModel['street_ids'],
                    'data' => $streetsGrid,
                    'options' => [
                        'placeholder' => Yii::t('app', 'Улица ...'),
                        'multiple' => true,
                        'class' => 'label-warning'

                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['street_full'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;'
                ],
            ],

            [
                'format' => 'ntext',
                'attribute' => 'num',
                'value' => function ($data) {
                    return $data['num'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal; width:10%;text-align:center;'
                ],
            ],
            [
                'format' => 'ntext',
                'attribute' => 'area',
                'value' => function ($data) {
                    return $data['area'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:10%;text-align:center;'
                ],
            ],
            [
                'format' => 'ntext',
                'attribute' => 'fias_guid',
                'value' => function ($data) {
                    return $data['fias_guid'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:23%;'
                ],
            ],
            [
                'format' => 'ntext',
                'attribute' => 'kladr_guid',
                'value' => function ($data) {
                    return $data['kladr_guid'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:14%;'
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
                                        'label' => 'Редактировать',
                                        'url' => '#',
                                        'linkOptions' => ['value'=> Url::to(['/house/update', 'id' => $model['id']]), 'title' => 'Редактирование дома ','class'=>'modalButton'],
                                    ],
                                    [
                                        'label' => 'Удалить',
                                        'url' => '/house/delete?id='.$model['id'],
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => Yii::t('app', 'Вы действительно хотите дом: {0} {1}?', [$model['street_full'], $model['num']]),
                                                'method' => 'post',
                                            ],
                                        ]
                                    ],
                                    [
                                        'label' => 'Реестры',
                                        'url' => '/reestr/index?house_id='.$model['id'],
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
