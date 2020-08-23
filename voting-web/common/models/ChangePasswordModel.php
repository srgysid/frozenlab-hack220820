<?php
namespace common\models;

use Yii;
use yii\base\Model;

class ChangePasswordModel extends Model
{
    public $old_password;
    public $new_password;
    public $repeat_password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['old_password', 'new_password', 'repeat_password'], 'string', 'min' => '6', 'max' => 50],
            [['old_password', 'new_password', 'repeat_password'], 'required'],
            [['repeat_password'], 'validateRepeatPassword'],
            [['old_password'], 'validateCurrentPassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for repeat password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateRepeatPassword($attribute, $params)
    {
        if ($this->$attribute != $this->new_password) {
            $this->addError($attribute, Yii::t('app', 'Введенный пароль и повтор не совпадают'));
        }
    }

    public function validateCurrentPassword($attribute, $params)
    {
        if (!Yii::$app->user->identity->validatePassword($this->$attribute)) {
            $this->addError($attribute, Yii::t('app', 'Текущий пароль указан не верно'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'old_password' => 'Текущий пароль',
            'new_password' => 'Новый пароль',
            'repeat_password' => 'Повтор нового пароля',
        ];
    }

    public function changePassword()
    {
        $user = Yii::$app->user->identity;
        $user->setPassword($this->new_password);
        $user->generateAuthKey();
        return $user->save(false);
    }
}