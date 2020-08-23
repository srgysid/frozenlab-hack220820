<?php
namespace common\helpers;

use Yii;

class AuthHelper
{
    const RL_ADMIN = 'rl__admin';
    const RL_USER = 'rl_user';
    const RL_KEY_USER = 'rl_key_user';

    public static function getRoles()
    {
        return [
            'rl_admin' => 'Администратор',
            'rl_user' => 'Пользователь',
            'rl_key_user' => 'Ключевой пользователь',
        ];
    }

    public static function getUserRoles($user_id)
    {
        $roles = Yii::$app->authManager->getRolesByUser($user_id);
        $roles = array_keys($roles);
        $roles = array_filter($roles, function ($val){
            return ($val != 'guest');
        });
        $roles = array_values($roles);

        return $roles;
    }

}