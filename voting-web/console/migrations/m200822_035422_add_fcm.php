<?php

use yii\db\Migration;

/**
 * Class m200822_035422_add_fcm
 */
class m200822_035422_add_fcm extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_fcm', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'fcm_token' => $this->string(1024),
            'created_at' => 'timestamp with time zone not null',
            'updated_at' => 'timestamp with time zone not null',
            'app_id' => $this->integer(),
            'token_hash' => $this->string(32)->notNull(),
        ]);

        $this->addForeignKey("fk_user_fcm_user", 'user_fcm', "user_id", "user", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_user_fcm_user_id', 'user_fcm', 'user_id');

        $this->createIndex('udx_user_fcm_token_hash', 'user_fcm', ['token_hash', 'app_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user_fcm');
    }

}
