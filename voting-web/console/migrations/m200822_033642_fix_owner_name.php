<?php

use yii\db\Migration;

/**
 * Class m200822_033642_fix_owner_name
 */
class m200822_033642_fix_owner_name extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('owner_name_key', 'owner');
//        $this->db->createCommand("ALTER TABLE owner DROP CONSTRAINT owner_name_key;")->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
