<?php

use yii\db\Migration;

/**
 * Class m200611_141710_add_question
 */
class m200611_141710_add_question extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('question', [
            'id' => $this->primaryKey(),
            'short_name' => $this->string(64)->notNull(),
            'topic' => $this->string(255)->notNull(),
            'proposal' => $this->string(255)->notNull(),
        ]);

        $this->createTable('title_question', [
            'id' => $this->primaryKey(),
            'title_id' => $this->integer()->notNull(),
            'question_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey("fk_title_question_title", 'title_question', "title_id", "title", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_title_question_title_id', 'title_question', 'title_id');

        $this->addForeignKey("fk_title_question_question", 'title_question', "question_id", "question", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_title_question_question_id', 'title_question', 'question_id');

        $this->createTable('meeting_question', [
            'id' => $this->primaryKey(),
            'meeting_id' => $this->integer()->notNull(),
            'question_id' => $this->integer()->notNull(),
            'order_num' => $this->smallInteger()->notNull(),
            'topic' => $this->string(255)->notNull(),
            'proposal' => $this->string(255)->notNull(),
        ]);

        $this->addForeignKey("fk_meeting_question_meeting", 'meeting_question', "meeting_id", "meeting", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_question_meeting_id', 'meeting_question', 'meeting_id');

        $this->addForeignKey("fk_meeting_question_question", 'meeting_question', "question_id", "question", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_question_question_id', 'meeting_question', 'question_id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('meeting_question');
        $this->dropTable('title_question');
        $this->dropTable('question');
    }
}
