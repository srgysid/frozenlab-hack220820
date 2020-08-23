<?php
// https://yiiframework.com.ua/ru/doc/guide/2/runtime-routing/
// https://www.yiiframework.com/doc/api/2.0/yii-web-urlmanager#$rules-detail
return [
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['v1/login'],
        'extraPatterns' => [
            'POST' => 'index',
        ],
    ],
    'GET v1/<controller:[\w-]+>/<id:\d+>' => 'v1/<controller>/view',
    'POST v1/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => 'v1/<controller>/<action>',
    'DELETE v1/<controller:[\w-]+>/delete/<id:\d+>' => 'v1/<controller>/delete',
];