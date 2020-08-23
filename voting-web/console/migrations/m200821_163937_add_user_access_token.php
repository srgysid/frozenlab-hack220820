<?php

use yii\db\Migration;

/**
 * Class m200821_163937_add_user_access_token
 */
class m200821_163937_add_user_access_token extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'access_token', $this->string(64));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'access_token');
    }

}
