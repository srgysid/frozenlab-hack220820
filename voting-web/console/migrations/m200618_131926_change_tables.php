<?php

use yii\db\Migration;

/**
 * Class m200618_131926_change_tables
 */
class m200618_131926_change_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey("fk_meeting_title", 'meeting');
        $this->dropIndex('idx_meeting_title_id', 'meeting');
        $this->dropColumn('meeting', 'title_desc');
        $this->dropColumn('meeting', 'title_id');
        $this->addColumn('meeting', 'protocol_date', 'timestamp with time zone');

        $this->addColumn('meeting_question', 'title_id', $this->integer());
        $this->addForeignKey("fk_meeting_question_title", 'meeting_question', "title_id", "title", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_question_title_id', 'meeting_question', 'title_id');

        $this->alterColumn('house', 'area', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('house', 'area', $this->integer());

        $this->dropForeignKey("fk_meeting_question_title", 'meeting_question');
        $this->dropIndex('idx_meeting_question_title_id', 'meeting_question');
        $this->dropColumn('meeting_question', 'title_id');

        $this->addColumn('meeting', 'title_id', $this->integer());
        $this->addColumn('meeting', 'title_desc', $this->string(255));
        $this->dropColumn('meeting', 'protocol_date');
        $this->addForeignKey("fk_meeting_title", 'meeting', "title_id", "title", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_meeting_title_id', 'meeting', 'title_id');
    }

}
