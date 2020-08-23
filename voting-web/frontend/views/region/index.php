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

$this->title = Yii::t('app', 'Регионы');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flex-main-content">
    <h3><?= Html::encode($this->title) ?></h3>
    <p>
        <?= Html::button(Yii::t('app', 'Добавить регион'), ['value'=>Url::to('/region/create'), 'title' => 'Добавить регион', 'class' => 'btn btn-walive btn-success modalButton']) ?>
        <?= Html::a('Очистить фильтр', ['/region/clear-filter'], ['class' => 'btn btn-walive btn-danger']) ?>
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
                'format' => 'ntext',
                'attribute' => 'name',
                'value' => function ($data) {
                    return $data['name'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;'
                ],
            ],
            [
                'format' => 'ntext',
                'attribute' => 'pref_name',
                'value' => function ($data) {
                    return $data['pref_name'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:15%;'
                ],
            ],
            [
                'format' => 'ntext',
                'attribute' => 'fias_guid',
                'value' => function ($data) {
                    return $data['fias_guid'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:30%;'
                ],
            ],
            [
                'format' => 'ntext',
                'attribute' => 'kladr_guid',
                'value' => function ($data) {
                    return $data['kladr_guid'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:15%;'
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
                                        'linkOptions' => ['value'=> Url::to(['/region/update', 'id' => $model['id']]), 'title' => 'Редактирование региона','class'=>'modalButton'],
                                    ],
                                    [
                                        'label' => 'Удалить',
                                        'url' => '/region/delete?id='.$model['id'],
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => Yii::t('app', 'Вы действительно хотите удалить регион: {0}?', $model['name']),
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
