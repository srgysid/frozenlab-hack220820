<?php

use kartik\grid\GridView;
use kartik\select2\Select2;
use frontend\assets\AppAsset;
use yii\bootstrap4\ButtonDropdown;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

AppAsset::register($this);

$reestr = $modelReestr->reg_num.' от '.Yii::$app->formatter->asDate($modelReestr->created_at).' ('.$modelReestr->house->street->city->name.', '.$modelReestr->house->street->pref_short.' '.$modelReestr->house->street->name.', д. '.$modelReestr->house->num.')';
$this->title = Yii::t('app', 'Строки реестра: №'.$reestr);
$this->params['breadcrumbs'][] = $this->title;

//echo '<pre>'.print_r($modelOwners, true).'</pre>';
?>
<div class="flex-main-content">
    <h3><?= Html::encode($this->title) ?></h3>
    <p>
        <?= Html::button(Yii::t('app', 'Добавить строку'), ['value'=>Url::to(['/reestr-detail/create', 'reestr_id'=>$modelReestr->id]), 'title' => 'Добавить строку', 'class' => 'btn btn-walive btn-success modalButton']) ?>
        <?= Html::a('Очистить фильтр', [Url::to(['/reestr-detail/clear-filter', 'reestr_id'=>$modelReestr->id])], ['class' => 'btn btn-walive btn-danger']) ?>
        <?= Html::a('Реестры', [Url::to(['/reestr/index', 'house_id'=>$modelReestr->house->id])], ['class' => 'btn btn-walive btn-primary']) ?>
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
                'attribute' => 'type_real_estate',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['type_real_estate'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;text-align: center; width:10%;'
                ],
            ],
            [
                'attribute' => 'num',
                'format' => 'raw',
                'value' => function ($data) {
//                    return $data['num'];
                    return Html::a($data['num'], '#', ['value'=> Url::to(['/reestr-detail/view', 'id' => $data['id']]), 'title' => 'Просмотр строки: '. $data['type_real_estate'].' №'.$data['num'],'class'=>'modalButton']);
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;text-align: center; width:10%;'
                ],
            ],

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
                    return Html::a($data['name'], '#', ['value'=> Url::to(['/reestr-detail/view', 'id' => $data['id']]), 'title' => 'Просмотр строки: '. $data['type_real_estate'].' №'.$data['num'],'class'=>'modalButton']);
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;'
                ],
            ],
            [
                'attribute' => 'area',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['area'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:10%;text-align: center;'
                ],
            ],
            [
                'attribute' => 'part',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['part'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:10%;text-align: center;'
                ],
            ],
            [
                'attribute' => 'phone',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['phone'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:10%;text-align: center;'
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
                                        'linkOptions' => ['value'=> Url::to(['/reestr-detail/update', 'id' => $model['id']]), 'title' => 'Редактирование строки ','class'=>'modalButton'],
                                    ],

                                    [
                                        'label' => 'Удалить',
                                        'url' => Url::to(['/reestr-detail/delete', 'id'=>$model['id']]),
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => Yii::t('app', 'Вы действительно хотите удалить {0} №{1}?', [$model['type_real_estate'], $model['num']]),
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
