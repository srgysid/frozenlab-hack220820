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

$address = ', по адресу: '.$modelMeeting->house->street->city->name.', '.$modelMeeting->house->street->pref_short.' '.$modelMeeting->house->street->name.', д. '.$modelMeeting->house->num;;
$this->title = Yii::t('app', 'Вопросы собрания №'.$modelMeeting->reg_num.$address);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flex-main-content">
    <h3><?= Html::encode($this->title) ?></h3>
    <p>
        <?= Html::button(Yii::t('app', 'Добавить вопрос собрания'), ['value'=>Url::to(['/meeting-question/create', 'meeting_id'=>$modelMeeting->id]), 'title' => 'Добавить вопрос собрания', 'class' => 'btn btn-walive btn-success modalButton']) ?>
        <?= Html::a('Очистить фильтр', [Url::to(['/meeting-question/clear-filter', 'meeting_id'=>$modelMeeting->id])], ['class' => 'btn btn-walive btn-danger']) ?>
        <?= Html::a('Собрания', ['/meeting/index'], ['class' => 'btn btn-walive btn-primary']) ?>
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
                'attribute' => 'order_num',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['order_num'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;text-align: center; width:10%;'
                ],
            ],
            [
                'attribute' => 'title_short_name',
                'label' => $searchModel->getAttributeLabel('title_id'),
                'filter' => Select2::widget([
                    'theme' => Select2::THEME_KRAJEE_BS4,
                    'model' => $searchModel,
                    'attribute' => 'title_ids',
                    'value' => $searchModel['title_ids'],
                    'data' => $titles,
                    'options' => [
                        'placeholder' => Yii::t('app', 'Тема ...'),
                        'multiple' => true,
                        'class' => 'label-warning'

                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['title_short_name'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:20%;'
                ],
            ],
            [
                'attribute' => 'question_short_name',
                'label' => $searchModel->getAttributeLabel('question_id'),
                'filter' => Select2::widget([
                    'theme' => Select2::THEME_KRAJEE_BS4,
                    'model' => $searchModel,
                    'attribute' => 'question_ids',
                    'value' => $searchModel['question_ids'],
                    'data' => $questions,
                    'options' => [
                        'placeholder' => Yii::t('app', 'Вопрос ...'),
                        'multiple' => true,
                        'class' => 'label-warning'

                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['question_short_name'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;width:20%;'
                ],
            ],

            [
                'attribute' => 'topic',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['topic'];
                },
                'contentOptions' => [
                    'style' => 'white-space: normal;'
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
                                        'linkOptions' => ['value'=> Url::to(['/meeting-question/update', 'id' => $model['id']]), 'title' => 'Редактирование вопроса','class'=>'modalButton'],
                                    ],
                                    [
                                        'label' => 'Удалить',
                                        'url' => '/meeting-question/delete?id='.$model['id'],
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => Yii::t('app', 'Вы действительно хотите удалить вопрос №{0}, "{1}"?', [$model['order_num'], $model['question_short_name']]),
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
