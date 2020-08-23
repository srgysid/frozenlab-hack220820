<?php

use yii\db\Migration;

/**
 * Class m200721_034719_add_reestr
 */
class m200721_034719_add_reestr extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('reestr', [
            'id' => $this->primaryKey(),
            'house_id' => $this->integer()->notNull(),
            'reg_num' => $this->string(24),
            'created_at' => 'timestamp with time zone not null',
        ]);
        $this->addForeignKey("fk_reestr_house", 'reestr', "house_id", "house", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_reestr_house_id', 'reestr', 'house_id');


        $this->createTable('reestr_detail', [
            'id' => $this->primaryKey(),
            'reestr_id' => $this->integer()->notNull(),
            'num' => $this->string(32)->notNull(),
            'area' => $this->float(),
            'type_real_estate' => $this->string(64),
            'type_owner_id' => $this->integer(),
            'name' => $this->string(255),
            'part' => $this->string(24),
            'ownership' => $this->string(127),
            'email' => $this->string(127),
            'phone' => $this->bigInteger(),
        ]);
        $this->addForeignKey("fk_reestr_detail_reestr", 'reestr_detail', "reestr_id", "reestr", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_reestr_detail_reestr_id', 'reestr_detail', 'reestr_id');

        $this->addForeignKey("fk_reestr_detail_type_owner", 'reestr_detail', "type_owner_id", "type_owner", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_reestr_detail_type_owner_id', 'reestr_detail', 'type_owner_id');


        $this->createTable('reestr_meeting', [
            'id' => $this->primaryKey(),
            'reestr_id' => $this->integer()->notNull(),
            'meeting_id' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey("fk_reestr_meeting_reestr", 'reestr_meeting', "reestr_id", "reestr", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_reestr_meeting_reestr_id', 'reestr_meeting', 'reestr_id');

        $this->addForeignKey("fk_reestr_meeting_meeting", 'reestr_meeting', "meeting_id", "meeting", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_reestr_meeting_meeting_id', 'reestr_meeting', 'meeting_id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('reestr_meeting');
        $this->dropTable('reestr_detail');
        $this->dropTable('reestr');
    }

}
