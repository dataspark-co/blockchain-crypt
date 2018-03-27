<?php

namespace app\components\helpers;

use Yii;
use yii\httpclient\Client;

/**
 * Class ApiHelper
 * @package app\components\modules\pools\components\helpers
 */
class ApiHelper
{
    /**
     * @param $method
     * @param array $data
     * @return array|mixed
     */
    public static function getData($method, $data = [])
    {
        $client = self::getClient();

        $_data['jsonrpc'] = '2.0';
        $_data['method'] = $method;
        $_data['params'] = $data;

        $response = $client->post(Yii::$app->blockchain->baseUrl, $_data)->send();

        if ($response->isOk) {
            return $response->data['result'];
        }

        return false;
    }

    /**
     * @param $method
     * @param array $data
     * @return bool|mixed
     */
    public static function sendData($method, $data = [])
    {
        $client = self::getClient();

        $_data["id"] = "test";
        $_data['jsonrpc'] = '2.0';
        $_data['method'] = $method;
        $_data['params'] = (!empty($data)) ? array_values($data) : [];

        $response = $client->post(Yii::$app->blockchain->baseUrl, $_data)->send();

        if ($response->isOk) {
            return (isset($response->data['result'])) ? $response->data['result'] : $response->data;
        }

        return false;
    }

    /**
     * @return Client
     */
    private static function getClient()
    {
        return new Client([
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);
    }

}