<?php

namespace bariew\nodeTree;
use yii\web\AssetBundle;

class ARTreeAssets extends AssetBundle
{
    public $sourcePath = '@vendor/kotelnikov/yii2-node-tree/views/jsTree';
    public $js = [
        'src/jstree.js',
        'src/jstree.dnd.js',
        'src/jstree.search.js',
        'src/jstree.types.js',
        'src/jstree.state.js',
        'src/jstree.contextmenu.js',
        'src/jstree.checkbox.js',
        'src/jstree.wholerow.js',
        'artree.js'
    ];
    public $css = [
        'src/themes/xenon/style.css',
        'artree.css'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}