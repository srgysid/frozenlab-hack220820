<?php

use yii\db\Migration;

/**
 * Class m200615_090746_add_real_estate_area
 */
class m200615_090746_add_real_estate_area extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('real_estate', 'area', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('real_estate', 'area');
    }

}
