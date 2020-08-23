<?php

use kartik\date\DatePicker;
use kartik\grid\GridView;
use kartik\select2\Select2;
use kartik\export\ExportMenu;
use backend\assets\AppAsset;
use yii\bootstrap4\ButtonDropdown;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

AppAsset::register($this);

$this->title = Yii::t('app', 'Собственники');
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    [
        'attribute'=>'type_owner_name',
        'label'=> $searchModel->getAttributeLabel('type_owner_name'),
        'vAlign'=>'middle',
        'width'=>'50px',
        'value'=>function ($data) {
            return $data['type_owner_name'];
        },
        'format'=>'raw'
    ],
    [
        'attribute'=>'name',
        'label'=> $searchModel->getAttributeLabel('name'),
        'vAlign'=>'middle',
        'value'=>function ($data) {
            return $data['name'];
        },
        'format'=>'raw'
    ],
    [
        'attribute'=>'phone',
        'label'=> $searchModel->getAttributeLabel('phone'),
        'vAlign'=>'middle',
        'value'=>function ($data) {
            return $data['phone'];
        },
        'format'=>'raw'
    ],

    [
        'attribute'=>'city_name',
        'label'=> $searchModel->getAttributeLabel('city_name'),
        'vAlign'=>'middle',
        'value'=>function ($data) {
            return $data['city_name'];
        },
        'format'=>'raw'
    ],
    [
        'attribute'=>'street_full',
        'label'=> $searchModel->getAttributeLabel('street_full'),
        'vAlign'=>'middle',
        'value'=>function ($data) {
            return $data['street_full'];
        },
        'format'=>'raw'
    ],

    [

        'attribute'=>'house_num',
        'label'=> $searchModel->getAttributeLabel('house_num'),
        'vAlign'=>'middle',
        'value'=>function ($data) {
            return $data['house_num'];
        },
        'format'=>'raw'
    ],
    [
        'attribute'=>'real_estate_num',
        'label'=> $searchModel->getAttributeLabel('real_estate_num'),
        'vAlign'=>'middle',
        'value'=>function ($data) {
            return $data['real_estate_num'];
        },
        'format'=>'raw'
    ],
    ['class' => 'yii\grid\ActionColumn'],
];
?>
<div class="flex-main-content">
    <h3><?= Html::encode($this->title) ?></h3>
    <p>
        <?= Html::button(Yii::t('app', 'Добавить собственника'), ['value'=>Url::to('/owner/create'), 'title' => 'Добавить собственника', 'class' => 'btn btn-walive btn-success modalButton']) ?>
        <?= Html::a('Очистить фильтр', ['/owner/clear-filter'], ['class' => 'btn btn-walive btn-danger']) ?>
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
        <span class="exp-btn">
            <?= ExportMenu::widget([
                'dataProvider' => $dataProvider,
                'columns' => $gridColumns
            ]);
            ?>
        </span>

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
                'attribute' => 'type_owner_name',
                'filter' => Select2::widget([
                    'theme' => Select2::THEME_DEFAULT,
                    'model' => $searchModel,
                    'attribute' => 'type_owner_ids',
                    'value' => $searchModel['type_owner_ids'],
                    'data' => $type_owner,
                    'options' => [
                        'placeholder' => Yii::t('app', 'Тип ...'),
                        'multiple' => true,
                        'class' => 'label-warning'

                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),

                'format' => 'raw',
                'value' => function ($data) {
                    return $data['type_owner_name'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:12%;text-align:left;'
                ],
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data['name'], '#', ['value'=> Url::to(['/owner/view', 'id' => $data['id']]), 'title' => 'Просмотр собственника '. $data['name'],'class'=>'modalButton']);
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;'
                ],
            ],
            [
                'attribute' => 'phone',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['phone'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:10%;'
                ],
            ],
            [
                'attribute' => 'city_name',
                'filter' => Select2::widget([
                    'theme' => Select2::THEME_DEFAULT,
                    'model' => $searchModel,
                    'attribute' => 'city_ids',
                    'value' => $searchModel['city_ids'],
                    'data' => $cities,
                    'options' => [
                        'placeholder' => Yii::t('app', 'Город ...'),
                        'multiple' => true,
                        'class' => 'label-warning'

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
                    'style' => 'white-space: normal;width:12%;'
                ],
            ],

            [
                'attribute' => 'street_full',
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
                    'style' => 'white-space: normal;width:13%;'
                ],
            ],
            [
                'format' => 'ntext',
                'attribute' => 'house_num',
                'value' => function ($data) {
                    return $data['house_num'];
                },

                'contentOptions' => [
                    'style' => 'white-space: normal;width:9%;text-align:center;'
                ],
            ],
            [
                'format' => 'ntext',
                'attribute' => 'real_estate_num',
                'value' => function ($data) {
                    return $data['real_estate_type_short_name'].' '.$data['real_estate_num'];
                },

                'contentOptions' => [
                    'style' => 'white-space: normal;width:9%;text-align:center;'
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
                                        'linkOptions' => ['value'=> Url::to(['/owner/update', 'id' => $model['id']]), 'title' => 'Редактирование собственника '. $model['name'],'class'=>'modalButton'],
                                    ],
                                    [
                                        'label' => 'Просмотр',
                                        'url' => '#',
                                        'linkOptions' => ['value'=> Url::to(['/owner/view', 'id' => $model['id']]), 'title' => 'Просмотр собственника '. $model['name'],'class'=>'modalButton'],
                                    ],
                                    [
                                        'label' => 'Удалить',
                                        'url' => '/owner/delete?id='.$model['id'],
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => Yii::t('app', 'Вы действительно хотите удалить собственника: {0}?', $model['name']),
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
