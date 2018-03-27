<?php

namespace app\models;

use Yii;
use yii\base\Model;
use ParagonIE\EasyRSA\EasyRSA;
use ParagonIE\EasyRSA\PrivateKey;
use ParagonIE\EasyRSA\PublicKey;

/**
 * Class Documents
 * @package app\models
 */
class Documents extends Model
{
    public $id;
    public $data;
    public $encryptKey;
    public $decryptKey;
    public $_access = [];


    /**
     * Initialize
     */
    public function init()
    {
        parent::init();

        $this->encryptKey = Yii::$app->security->generateRandomString();
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['data', 'required'],
            [['id', 'data'], 'safe']
        ];
    }

    /**
     * Attribute labels
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'data' => 'Text'
        ];
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function save()
    {
        $this->encrypt();
        return Yii::$app->blockchain->saveEncryptedDocument($this->data, $this->getAccess());
    }

    /**
     * @return bool
     */
    public function encrypt()
    {
        $this->data = utf8_encode(
            Yii::$app->getSecurity()->encryptByKey($this->data, $this->encryptKey)
        );

        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function decrypt()
    {
        if(empty($this->decryptKey)) {
            return false;
        }

        $user = $this->getUser();
        $privateKey = new PrivateKey($user->user_private_key);

        try {
            $secretKey = EasyRSA::decrypt($this->decryptKey, $privateKey);
        } catch (\Exception $e) {
            return false;
        }

        $this->data = Yii::$app->getSecurity()->decryptByKey(
            utf8_decode($this->data),
            $secretKey
        );

        return true;
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function getAccess()
    {
        $user = $this->getUser();
        if(empty($this->_access)) {
            $key = new PublicKey($user->user_public_key);
            return [
                'id' => $user->getId(),
                'public_key' => $user->user_public_key,
                'key' => EasyRSA::encrypt($this->encryptKey, $key),
            ];
        } else {
            if(is_array($this->_access)) {
                foreach ($this->_access As $access) {

                }
            }
        }
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function getUserAccess()
    {
        if(empty($this->_access)) {
            return false;
        }

        $user = $this->getUser();

        foreach ($this->_access AS $data) {
            if($data['id'] == $user->id) {
                $this->decryptKey = $data['key'];
                return true;
            }
        }

        return false;
    }

    /**
     * @return null|\yii\web\IdentityInterface
     * @throws \Exception
     * @throws \Throwable
     */
    protected function getUser()
    {
        return Yii::$app->user->getIdentity();
    }
}