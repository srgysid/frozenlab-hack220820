<?php

use yii\db\Migration;

/**
 * Class m200819_081121_add_column_meeting_question
 */
class m200819_081121_add_column_meeting_question extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('meeting_question', 'quorum', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('meeting_question', 'quorum');
    }

}
