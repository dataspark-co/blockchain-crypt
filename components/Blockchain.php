<?php

namespace app\components;

use Yii;
use yii\base\BaseObject;
use app\components\helpers\ApiHelper;
use yii\helpers\Json;

/**
 * Class Blockchain
 * @package app\components
 */
class Blockchain extends BaseObject
{
    const METHOD_CREATE_DOCUMENT = 'create_document';
    const METHOD_GET_DOCUMENT = 'get_document';
    const METHOD_SET_DOCUMENT = 'update_document';
    const METHOD_GET_DOCUMENT_ACCESS = 'get_access_list';
    const METHOD_GET_KEY = 'get_cert';

    const USER_ = '';
    const USER_CERTIFICATE_KEY = 'pub_container_key';

    //public $baseUrl = 'http://10.10.3.69:8021';
    public $baseUrl = 'http://4dd804bb.ngrok.io/';
    private $user;

    /**
     * @param $data
     * @param $access_key
     * @param $id
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function createDocument($data, $access_key, $id)
    {
        $user = $this->getUser();

        if(!$user) {
            return false;
        }

        $data = ApiHelper::sendData(
            self::METHOD_CREATE_DOCUMENT,
            [
                self::USER_CERTIFICATE_KEY => $user->certificate_key,
                'document_id' => $id,
                'data' => $data,
                'access_key' => $access_key,
            ]
        );

        if(empty($data)) {
            return false;
        } else {
            var_dump($data);
            return $data['id'];
        }
    }

    /**
     * @param $id
     * @return array|bool|mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function getEncryptedDocument($id)
    {
        $user = $this->getUser();

        if(!$user) {
            return false;
        }

        $data = ApiHelper::getData(
            self::METHOD_GET_DOCUMENT,
            [
                'document_id' => $id,
                self::USER_CERTIFICATE_KEY => $user->certificate_key
            ]
        );

        if(empty($data)) {
            return false;
        } else {
            return $data['document_id'];
        }
    }

    /**
     * @param $id
     * @return array|bool|mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function getDocumentAccess($id)
    {
        $user = $this->getUser();

        if(!$user) {
            return false;
        }

        return ApiHelper::getData(
            self::METHOD_GET_DOCUMENT_ACCESS,
            [
                'document_id' => $id,
                self::USER_CERTIFICATE_KEY => $user->certificate_key
            ]
        );
    }

    /**
     * @param $data
     * @param $access
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function saveEncryptedDocument($id, $data, $access)
    {
        $user = $this->getUser();

        $data = ApiHelper::sendData(
            self::METHOD_SET_DOCUMENT,
            [
                'document_id' => $id,
                'data' => $data,
                'access_key' => $access,
                self::USER_CERTIFICATE_KEY => $user->certificate_key
            ]
        );

        return $data;
    }

    /**
     * @return bool|mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function getCertKey()
    {
        $user = $this->getUser();

        $data = ApiHelper::sendData(
            'create_certificate',
            []
        );

        $data = Json::decode($data);

        if(empty($data)) {
            return false;
        } else {
            return $data['public_container_key'];
        }
    }

    /**
     * @return null|\yii\web\IdentityInterface
     * @throws \Exception
     * @throws \Throwable
     */
    protected function getUser()
    {
        if(empty($this->user)) {
            return Yii::$app->user->getIdentity();
        }

        return $this->user;
    }
}