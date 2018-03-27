<?php
namespace app\widgets\redactor;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

/**
 * Class TinyMce
 * @package app\widgets\redactor
 */
class TinyMce extends InputWidget
{
    /**
     * @var array
     */
    public $clientOptions = [];

    /** 
     * @var bool
     */
    public $triggerSaveOnBeforeValidateForm = true;

    /**
     * Run widget
     * @return string
     */
    public function run()
    {
        if ($this->hasModel()) {
            $output = Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            $output = Html::textarea($this->name, $this->value, $this->options);
        }

        $this->registerClientScript();
        return $output;
    }

    /**
     * Rregister client script
     */
    protected function registerClientScript()
    {
        $js = [];
        $id = $this->options['id'];
        TinyMceAsset::register($this->view);
        $this->clientOptions['selector'] = "#{$id}";


        $options = Json::encode($this->clientOptions);
        $js[] = "tinymce.init($options);";
        
        if ($this->triggerSaveOnBeforeValidateForm) {
            $js[] = "$('#{$id}').parents('form').on('beforeValidate', function() { tinymce.triggerSave(); });";
        }
        $this->view->registerJs(implode("\n", $js));
    }
}
