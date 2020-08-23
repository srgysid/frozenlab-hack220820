<?php

use yii\db\Migration;

/**
 * Class m200709_044347_add_district
 */
class m200709_044347_add_district extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('district',[
            'id' => $this->primaryKey(),
            'region_id' => $this->integer()->notNull(),
            'name' => $this->string(127),
            'pref_name' => $this->string(30),
            'pref_short' => $this->string(12),
            'fias_guid' => 'uuid',
            'kladr_guid' => $this->string(24),
        ]);

        $this->addForeignKey("fk_district_region_id", 'district', "region_id", "region", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_district_region_id', 'district', 'region_id');

        $this->addColumn('city', 'district_id', $this->integer());

        $this->addForeignKey("fk_city_district_id", 'city', "district_id", "district", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_city_district_id', 'city', 'district_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('city', 'district_id');
        $this->dropTable('district');
    }
}
