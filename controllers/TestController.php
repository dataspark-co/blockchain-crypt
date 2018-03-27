<?php

namespace app\controllers;

use app\components\helpers\ApiHelper;
use Yii;
use yii\helpers\Json;
use yii\rest\Controller;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * Class TestController
 * @package app\controllers
 */
class TestController extends Controller
{
    /**
     * Behaviors list
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            [
                'class' => 'yii\filters\ContentNegotiator',
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        var_dump(Yii::$app->blockchain->getCertKey());die;
    }

    public function actionDocumentsList()
    {
        return [
            ['id' => '84ckrMbZkJbf2hUEYOQBS6zoP4n4Mpk6'],
            ['id' => '76uGgpd2O3Yep3bVgRNXNwVJBJs2ldLy'],
            ['id' => 'y3HuvOAPD7PNszjpcjbIAi2ySlUi_dcx'],
            ['id' => 'knL0WCuuXlCdseblzd7o56yCt3VtgqrX'],
            ['id' => 'LMG-5Tt9r4Ual-SBv9nrdMVZhHSrmH7j'],
        ];
    }

    public function actionCreateDocument($data)
    {
        return [
            'id' => '84ckrMbZkJbf2hUEYOQBS6zoP4n4Mpk6'
        ];
    }

    public function actionGetDocument()
    {
        $file = $this->getFile();
        return $file['data'];
    }

    public function actionGetDocumentAccess()
    {
        $file = $this->getFile();
        return [$file['access']];
    }

    protected function getFile()
    {
        $id = '84ckrMbZkJbf2hUEYOQBS6zoP4n4Mpk6';
        $content = file_get_contents(\Yii::getAlias('@runtime/' . $id . '.json'));
        return Json::decode($content);

    }
}