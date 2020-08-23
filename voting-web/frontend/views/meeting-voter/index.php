<?php

use kartik\grid\GridView;
use kartik\select2\Select2;
use frontend\assets\AppAsset;
use yii\bootstrap4\ButtonDropdown;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\helpers\MathHelper;
use common\models\MeetingVoter;

AppAsset::register($this);

$this->title = Yii::t('app', 'Голосование по собранию №'.$modelMeeting->reg_num.' (адрес: '.$modelMeeting->house->street->city->name.', '.$modelMeeting->house->street->pref_short.' '.$modelMeeting->house->street->name.', д. '.$modelMeeting->house->num.')');
$this->params['breadcrumbs'][] = $this->title;

//$area = Yii::t('app', 'Общая площадь дома (адрес: '.$modelMeeting->house->street->city->name.', '.$modelMeeting->house->street->pref_short.' '.$modelMeeting->house->street->name.', д. '.$modelMeeting->house->num.') '.$modelMeeting->house->area );
?>
<div class="flex-main-content">
    <h3><?= Html::encode($this->title) ?></h3>
    <h5>Общая площадь дома <?=$modelMeeting->house->area?> м&#178</h5>
    <p>
        <?= Html::a('Собрания', [Url::to(['/meeting/index'])], ['class' => 'btn btn-walive btn-primary']) ?>
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
                    'style' => 'white-space: normal;text-align: center; width:8%;'
                ],
            ],
            [
                'attribute' => 'num',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['num'];
//                    return Html::a($data['num'], '#', ['value'=> Url::to(['/reestr-detail/view', 'id' => $data['id']]), 'title' => 'Просмотр строки: '. $data['type_real_estate'].' №'.$data['num'],'class'=>'modalButton']);
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;text-align: center; width:8%;'
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
                    'style' => 'white-space: normal;width:10%;text-align:center;'
                ],
            ],
            [
                'attribute' => 'vote_source',
                'filter' => Select2::widget([
                    'theme' => Select2::THEME_DEFAULT,
                    'model' => $searchModel,
                    'attribute' => 'vote_source_ids',
                    'value' => $searchModel['vote_source_ids'],
                    'data' => $source,
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
                    if (($data['vote_source']!=null) && ($data['vote_source']!='')) return MeetingVoter::getSourceList()[$data['vote_source']];
                    else return '';
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:10%;text-align:center;'
                ],
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['name'];
//                    return Html::a($data['name'], '#', ['value'=> Url::to(['/reestr-detail/view', 'id' => $data['id']]), 'title' => 'Просмотр строки: '. $data['type_real_estate'].' №'.$data['num'],'class'=>'modalButton']);
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;'
                ],
            ],

            [
                'attribute' => 'cntPercent',
                'format' => 'raw',
                'value' => function ($data) {
                    if (($data['area']) and ($data['house_area'])){
                        if ($data['part']){
                            $part = MathHelper::getFloatValue($data['part']);
                            if ($part) $currentArea = round($data['area'] * $part, 2);
                            else $currentArea = $data['area'];
                        }
                        else {
                            $currentArea = $data['area'];
                        }
                        $cntPercent = round($currentArea/$data['house_area']*100, 3);
                    }

                    return $cntPercent;
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:8%;text-align: center;'
                ],
            ],
            [
                'attribute' => 'area',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['area'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:8%;text-align: center;'
                ],
            ],

            [
                'attribute' => 'part',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['part'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:8%;text-align: center;'
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
                                        'label' => ($model['vote_source'] == null ? 'Голосование' : 'Просмотр'),
                                        'url' => '#',
                                        'linkOptions' => [
                                            'value'=> ($model['vote_source'] == null ? Url::to(['/meeting-voter/vote', 'id'=>$model['id']]) : Url::to(['/meeting-voter/view', 'id'=>$model['id']])),
                                            'title' => ($model['vote_source'] == null ? Yii::t('app', 'Голосование собственника {0}', [$model['name']]) : Yii::t('app', 'Просмотр выбора собственника {0}', [$model['name']])),
                                            'class'=>'modalButton'],
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
