<?php
namespace app\widgets\redactor;

use yii\web\AssetBundle;

/**
 * Class TinyMceAsset
 * @package app\widgets\redactor
 */
class TinyMceAsset extends AssetBundle
{
    public $sourcePath = '@vendor/tinymce/tinymce';

    public $js = [
        'tinymce.min.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];
} 
