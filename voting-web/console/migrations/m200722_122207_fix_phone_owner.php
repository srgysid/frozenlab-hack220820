<?php

use yii\db\Migration;

/**
 * Class m200722_122207_fix_phone_owner
 */
class m200722_122207_fix_phone_owner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('owner', 'phone', $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('owner', 'phone', $this->integer());
    }

}
