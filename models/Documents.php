<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use ParagonIE\EasyRSA\EasyRSA;
use ParagonIE\EasyRSA\PrivateKey;
use ParagonIE\EasyRSA\PublicKey;
use app\models\query\DocumentsQuery;

/**
 * This is the model class for table "{{%documents}}".
 *
 * @property int $id
 * @property string $title
 * @property string $remote_id
 */
class Documents extends ActiveRecord
{
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
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%documents}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remote_id'], 'string'],
            [['title'], 'string', 'max' => 100],
            [['title', 'data'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'remote_id' => 'Remote ID',
            'data' => 'Data',
        ];
    }

    /**
     * after find record
     */
    public function afterFind()
    {
        parent::afterFind();

        if(!$this->isNewRecord) {
            $blockchain = Yii::$app->blockchain;
            $this->_access = $blockchain->getDocumentAccess($this->remote_id);
            $this->data = $blockchain->getEncryptedDocument($this->remote_id);

//            var_dump($this->_access);
//            var_dump($this->data);die;

            if($this->getUserAccess()) {
                $this->decrypt();
            } else {
                return false;
            }
        }
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function beforeSave($insert)
    {
        $blockchain = Yii::$app->blockchain;
        $this->encrypt();
        if($this->isNewRecord) {
            $this->remote_id = $this->gen_uuid();

            $result = $blockchain->createDocument($this->data, $this->getAccess(), $this->remote_id);

            if(empty($result)) {
                return false;
            }
        } else {
            $result = $blockchain->saveEncryptedDocument($this->remote_id, $this->data, $this->getAccess());

            if(!$result) {
                return false;
            }
        }

        return parent::beforeSave($insert);
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
     * @inheritdoc
     * @return DocumentsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DocumentsQuery(get_called_class());
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
                [
                    'pub_container_key' => $user->certificate_key,
                    'access_key' => EasyRSA::encrypt($this->encryptKey, $key),
                ],
            ];
        } else {
            if(is_array($this->_access)) {
                foreach ($this->_access As $idx => $access) {
                    $user = User::findOne($access['certificate_key']);
                    if($user) {
                        $key = new PublicKey($user->user_public_key);
                        $this->_access[$idx]['access_key'] = EasyRSA::encrypt($this->encryptKey, $key);
                    }
                }
            }
        }
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function getRandomAccessKey()
    {
        $user = $this->getUser();
        $key = new PublicKey($user->user_public_key);
        return EasyRSA::encrypt($this->encryptKey, $key);
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
        $blockchain = Yii::$app->blockchain;

        foreach ($this->_access AS $data) {
            if($data['pub_container_key'] == $user->certificate_key) {
                $this->decryptKey = $data[$blockchain::USER_ACCESS_KEY];
                return true;
            }
        }

        return false;
    }

    protected function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
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
