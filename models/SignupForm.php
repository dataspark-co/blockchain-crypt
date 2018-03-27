<?php
namespace app\models;

use yii\base\Model;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $password;
    public $certificate_key;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * @return User|bool
     * @throws \ParagonIE\EasyRSA\Exception\InvalidKeyException
     * @throws \yii\base\Exception
     */
    public function signup()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = new User();
        $user->username = $this->username;
        $user->certificate_key = $this->certificate_key;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateSystemKeys();
        $user->setCertKey();

        return $user->save() ? $user : false;
    }
}
