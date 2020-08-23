<?php

use yii\db\Migration;

/**
 * Class m200420_082143_add_meeting
 */
class m200420_082143_add_meeting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('meeting', [
            'id' => $this->primaryKey(),
            'title_id' => $this->integer()->notNull(),
            'title_desc' => $this->string(255),
            'house_id' => $this->integer()->notNull(),
            'area' => $this->integer(),

            'type_voting_id' => $this->integer()->notNull(),
            'form_voting_id' => $this->integer()->notNull(),
            'reg_num' => $this->string(24),

            'started_at' => 'timestamp with time zone',
            'distant_started_at' => 'timestamp with time zone',
            'finished_at' => 'timestamp with time zone',
            'meeting_place' => $this->string(255),
            'receiving_place' => $this->string(255),
            'familiarization_place' => $this->string(255),
            'familiarization_date_from' => 'timestamp with time zone',
            'familiarization_date_to' => 'timestamp with time zone',
            'familiarization_time_from' => $this->string(5),
            'familiarization_time_to' => $this->string(5),

            'type_initiator' => $this->smallInteger(),
            'initiator_company_id' => $this->integer(),

            'type_administrator' => $this->smallInteger(),
            'administrator_company_id' => $this->integer(),
            'administrator_owner_id' => $this->integer(),

            'created_at' => 'timestamp with time zone not null',
            'description' => $this->text(),
        ]);

        $this->addForeignKey("fk_meeting_title", 'meeting', "title_id", "title", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_title_id', 'meeting', 'title_id');

        $this->addForeignKey("fk_meeting_house", 'meeting', "house_id", "house", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_house_id', 'meeting', 'house_id');

        $this->addForeignKey("fk_meeting_type_voting", 'meeting', "type_voting_id", "type_voting", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_type_voting_id', 'meeting', 'type_voting_id');

        $this->addForeignKey("fk_meeting_form_voting", 'meeting', "form_voting_id", "form_voting", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_form_voting_id', 'meeting', 'form_voting_id');

        $this->addForeignKey("fk_meeting_initiator_company", 'meeting', "initiator_company_id", "company", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_initiator_company_id', 'meeting', 'initiator_company_id');

        $this->addForeignKey("fk_meeting_administrator_company", 'meeting', "administrator_company_id", "company", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_administrator_company_id', 'meeting', 'administrator_company_id');

        $this->addForeignKey("fk_meeting_administrator_owner", 'meeting', "administrator_owner_id", "owner", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_administrator_owner_id', 'meeting', 'administrator_owner_id');

        $this->createTable('initiator_owner', [
            'id' => $this->primaryKey(),
            'meeting_id' => $this->integer(),
            'owner_id' => $this->integer(),
        ]);

        $this->addForeignKey("fk_initiator_owner_meeting", 'initiator_owner', "meeting_id", "meeting", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_initiator_owner_meeting_id', 'initiator_owner', 'meeting_id');

        $this->addForeignKey("fk_initiator_owner_owner", 'initiator_owner', "owner_id", "owner", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_initiator_owner_owner_id', 'initiator_owner', 'owner_id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('initiator_owner');
        $this->dropTable('meeting');
    }

}
