<?php

use frontend\assets\AppAsset;
use common\widgets\MenuRole;
use kartik\sidenav\SideNav;
use yii\helpers\Url;

$appAsset = AppAsset::register($this);

$img_path = $appAsset->baseUrl.'/images/green_home_300.png';
$img_path_s = $appAsset->baseUrl.'/images/green_home.png';
//$img_path_s = $appAsset->baseUrl.'/images/logo_small.png';

//$session = Yii::$app->session;
//$class_menu = '';
//$class_menu = $session['class_menu'];
?>

<div class="sidebar-header">
    <div class="text-right">
        <button type="button" title="Сложить меню" id="sidebarCollapse" class="btn btn-default">
            <i class="fas fa-bars"></i>
        </button>
<!--        <button type="button" title="Сложить меню" id="sidebarCollapse" class="btn btn-default sidebar-toggle">-->
<!--            <i class="fas fa-bars"></i>-->
<!--        </button>-->

    </div>
    <div class="text-center">
        <h3>
            <a href="<?=Url::home()?>" title="Главная">
                <img align="center" width="150px" src="<?=$img_path?>">
            </a>
        </h3>
        <strong>
            <a href="<?=Url::home()?>" title="Главная">
                <img width="50px" src="<?=$img_path_s?>">
            </a>
        </strong>
    </div>
</div>


<?= MenuRole::widget(
    [
        'type' => MenuRole::TYPE_DEFAULT,
        'encodeLabels' => false,
        'iconPrefix' => 'fas fa-',
        'indItem' => false,
        'indMenuOpen' => '<i class="fas fa-caret-up"></i>',
        'indMenuClose' => '<i class="fas fa-caret-down"></i>',
        'items' => [
            [
                'label' => '<span class="CTAs">Собрания</span>',
                'url' => ['/meeting/index'],
                'icon' => 'file-alt',
                'access' => ['rl_admin', 'rl_key_user', 'rl_user'],
            ],
            [
                'label' => '<span class="CTAs">Пользователи</span>',
                'url' => ['/employee/index'],
                'icon' => 'graduation-cap',
                'access' => ['rl_admin', 'rl_key_user'],
            ],
//            [
//                'label' => '<span class="CTAs">Пункт 3</span>',
//                'url' => '#',
//                'icon' => 'tachometer-alt'
//            ],

            [
                'label' => '<span class="CTAs">Справочники</span>',
                'url' => '#',
                'icon' => 'book',
                'access' => ['rl_admin', 'rl_key_user'],
                'items' => [
                    [
                        'label' => 'Темы вопросов',
                        'url' => ['/title/index']
                    ],
                    [
                        'label' => 'Вопросы',
                        'url' => ['/question/index']
                    ],
                    [
                        'label' => 'Вопросы по темам',
                        'url' => ['/title-question/index']
                    ],
                    [
                        'label' => 'Компании',
                        'url' => ['/company/index']
                    ],
                    [
                        'label' => 'Регионы',
                        'url' => ['/region/index']
                    ],
                    [
                        'label' => 'Районы',
                        'url' => ['/district/index']
                    ],

                    [
                        'label' => 'Города',
                        'url' => ['/city/index']
                    ],
                    [
                        'label' => 'Улицы',
                        'url' => ['/street/index']
                    ],
                    [
                        'label' => 'Дома',
                        'url' => ['/house/index']
                    ],
                    [
                        'label' => 'Помещения',
                        'url' => ['/real-estate/index']
                    ],
                    [
                        'label' => 'Собственники',
                        'url' => ['/owner/index']
                    ],

                ]
            ],

    ],

    ]
);
?>

