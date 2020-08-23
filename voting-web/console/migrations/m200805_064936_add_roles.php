<?php

use yii\db\Migration;

/**
 * Class m200805_064936_add_roles
 */
class m200805_064936_add_roles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->initRole('rl_user');
        $this->initRole('rl_key_user');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->deleteRole('rl_user');
        $this->deleteRole('rl_key_user');
    }

    public function initRole($roleName)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        if (!$role) {
            $role = $auth->createRole($roleName);    // create role if not exists yet
            $auth->add($role);
        }
        return $role;
    }

    public function deleteRole($roleName)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        if ($role) {
            $auth->removeChildren($role);
            $auth->remove($role);
        }
    }

}
