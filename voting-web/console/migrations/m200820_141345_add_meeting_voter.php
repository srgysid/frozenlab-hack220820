<?php

use yii\db\Migration;

/**
 * Class m200820_141345_add_meeting_voter
 */
class m200820_141345_add_meeting_voter extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('meeting_voter', [
            'id' => $this->primaryKey(),
            'meeting_id' => $this->integer()->notNull(),
            'num' => $this->string(32)->notNull(),
            'area' => $this->float(),
            'type_real_estate' => $this->string(64),
            'type_owner_id' => $this->integer(),
            'name' => $this->string(255),
            'part' => $this->string(24),
            'ownership' => $this->string(127),
            'email' => $this->string(127),
            'phone' => $this->bigInteger(),
            'vote_source' => $this->smallInteger(),
        ]);
        $this->addForeignKey("fk_meeting_voter_meeting", 'meeting_voter', "meeting_id", "meeting", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_voter_meeting_id', 'meeting_voter', 'meeting_id');

        $this->addForeignKey("fk_meeting_voter_type_owner", 'meeting_voter', "type_owner_id", "type_owner", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_voter_type_owner_id', 'meeting_voter', 'type_owner_id');


        $this->createTable('voter_meeting_question', [
            'id' => $this->primaryKey(),
            'meeting_voter_id' => $this->integer()->notNull(),
            'meeting_question_id' => $this->integer()->notNull(),
            'choice' => $this->smallInteger(),
        ]);

        $this->addForeignKey("fk_voter_meeting_question_meeting_voter", 'voter_meeting_question', "meeting_voter_id", "meeting_voter", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_voter_meeting_question_meeting_voter_id', 'voter_meeting_question', 'meeting_voter_id');

        $this->addForeignKey("fk_voter_meeting_question_meeting_question", 'voter_meeting_question', "meeting_question_id", "meeting_question", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_voter_meeting_question_meeting_question_id', 'voter_meeting_question', 'meeting_question_id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('voter_meeting_question');
        $this->dropTable('meeting_voter');
    }

}
