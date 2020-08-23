<?php

use kartik\date\DatePicker;
use kartik\grid\GridView;
use kartik\select2\Select2;
use backend\assets\AppAsset;
use yii\bootstrap4\ButtonDropdown;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;

AppAsset::register($this);

$this->title = Yii::t('app', 'Собрания');
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .modal-lg{
        max-width: 1100px;
    }
</style>

<div class="flex-main-content">
    <h3><?= Html::encode($this->title) ?></h3>
    <p>
        <?= Html::button(Yii::t('app', 'Добавить собрание'), ['value'=>Url::to('/meeting/create'), 'title' => 'Добавить собрание', 'class' => 'btn btn-walive btn-success modalButton']) ?>
        <?= Html::a('Очистить фильтр', ['/meeting/clear-filter'], ['class' => 'btn btn-walive btn-danger']) ?>
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
        'options' => ['class' => 'table-sm',],
        'columns' => [
            [
                'attribute' => 'reg_num',
                'format' => 'raw',
                'label' => $searchModel->getAttributeLabel('reg_num'),
                'value' => function($model) {
                    return Html::a($model['reg_num'], '#', ['value'=> Url::to(['/meeting/view', 'id' => $model['id']]), 'title' => 'Просмотр собрания '. $model['reg_num'],'class'=>'modalButton']);
                },
                'contentOptions' => [
                    'style' => 'white-space: normal; width:15%;text-align:center;'
                ],
            ],
            [
                'attribute' => 'created_at_from',
                'label' => $searchModel->getAttributeLabel('created_at'),
                'filterType' => GridView::FILTER_DATE,
                'filterWidgetOptions' => [
                    'type' => DatePicker::TYPE_RANGE,
                    'separator' => 'по',
                    'attribute2' => 'created_at_to',
                    'options' => [
                        'autocomplete' => 'off1',
                    ],
                    'options2' => [
                        'autocomplete' => 'off1'
                    ],
                    'pluginOptions' => [
//                        'format' => 'yyyy-mm-dd',
                        'autoclose' => true,
                    ]
//                    'saveFormat' => 'php:Y-m-d'
                ],
                'value' => function($row) {
                    return Yii::$app->formatter->asDate($row['created_at']);
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;'
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
                    'style' => 'white-space: normal;width:20%;'
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
                    'style' => 'white-space: normal;width:20%;'
                ],
            ],
            [
                'format' => 'ntext',
                'attribute' => 'house_num',
                'value' => function ($data) {
                    return $data['house_num'];
                },

                'contentOptions' => [
                    'style' => 'white-space: normal;width:15%;text-align:center;'
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
                                        'linkOptions' => ['value'=> Url::to(['/meeting/update', 'id' => $model['id']]), 'title' => Yii::t('app', 'Редактирование собрания {0}', [$model['reg_num']]),'class'=>'modalButton'],
                                    ],
                                    [
                                        'label' => 'Удалить',
                                        'url' => '/meeting/delete?id='.$model['id'],
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => Yii::t('app', 'Вы действительно хотите удалить собрание {0} для {1}?', [$model['reg_num'], $model['street_full'].' '.$model['house_num']]),
                                                'method' => 'post',
                                            ],
                                        ]
                                    ],
                                    [
                                        'label' => 'Вопросы собрания',
                                        'url' => '/meeting-question/index?meeting_id='.$model['id'],
                                    ],
                                    [
                                        'label' => 'Печать бюллетеня',
                                        'url' => '#',
//                                        'linkOptions' => ['target' => '_blank'],
                                    ],
                                    [
                                        'label' => 'Реестр',
                                        'url' => Url::to(['/reestr-detail/reestr-meeting', 'meeting_id'=>$model['id']]),
                                    ],
                                    [
                                        'label' => 'Голосование',
                                        'url' => Url::to(['/meeting-voter/index', 'meeting_id'=>$model['id']]),
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
