<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Documents */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="documents-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data')->textarea(['rows' => 15]) ?>

    <div class="row">
        <div class="col-md-6">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success btn-block']) ?>
        </div>
        <div class="col-md-6">
            <?= Html::a('Cancel', ['documents/index'], ['class' => 'btn btn-default btn-block']);?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
