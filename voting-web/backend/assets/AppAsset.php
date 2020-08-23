<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@backend/assets';
//    public $basePath = '@webroot';
//    public $baseUrl = '@web';
    public $css = [
    ];
    public $js = [
    ];
    // Нужно заранее включать все используемые assets, иначе при ajax обновлениях случаются проблемы
    // https://stackoverflow.com/questions/43166647/synchronous-ajax-error-with-yii2-activeform
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}
