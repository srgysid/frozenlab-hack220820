<?php

use yii\db\Migration;

/**
 * Class m200416_092359_add_dictionaries
 */
class m200416_092359_add_dictionaries extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('region', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->unique(),
            'pref_name' => $this->string(24),
            'fias_guid' => 'uuid',
            'kladr_guid' => $this->string(24),
        ]);


        $this->createTable('city', [
            'id' => $this->primaryKey(),
            'region_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'pref_name' => $this->string(24),
            'pref_short' => $this->string(12),
            'fias_guid' => 'uuid',
            'kladr_guid' => $this->string(24),
        ]);

        $this->addForeignKey("fk_city_region", 'city', "region_id", "region", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_city_region_id', 'city', 'region_id');


        $this->createTable('street', [
            'id' => $this->primaryKey(),
            'city_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'pref_name' => $this->string(24),
            'pref_short' => $this->string(12),
            'fias_guid' => 'uuid',
            'kladr_guid' => $this->string(24),
        ]);

        $this->addForeignKey("fk_street_city", 'street', "city_id", "city", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_street_city_id', 'street', 'city_id');


        $this->createTable('house', [
            'id' => $this->primaryKey(),
            'street_id' => $this->integer()->notNull(),
            'num' => $this->string(32)->notNull(),
            'area' => $this->integer(),
            'fias_guid' => 'uuid',
            'kladr_guid' => $this->string(24),
        ]);

        $this->addForeignKey("fk_house_street", 'house', "street_id", "street", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_house_street_id', 'house', 'street_id');


        $this->createTable('real_estate_type', [
            'id' => $this->primaryKey(),
            'name' => $this->string(64)->notNull()->unique(),
            'short_name' => $this->string(16),
        ]);
        $this->insert('real_estate_type', [
            'name' => 'Квартира',
            'short_name' => 'кв.',
        ]);
        $this->insert('real_estate_type', [
            'name' => 'Комната',
            'short_name' => 'к.',
        ]);
        $this->insert('real_estate_type', [
            'name' => 'Помещение',
            'short_name' => 'пом.',
        ]);


        $this->createTable('real_estate', [
            'id' => $this->primaryKey(),
            'house_id' => $this->integer()->notNull(),
            'real_estate_type_id' => $this->integer()->notNull(),
            'num' => $this->string(32)->notNull(),
        ]);

        $this->addForeignKey("fk_real_estate_house", 'real_estate', "house_id", "house", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_real_estate_house_id', 'real_estate', 'house_id');

        $this->addForeignKey("fk_real_estate_real_estate_type", 'real_estate', "real_estate_type_id", "real_estate_type", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_real_estate_real_estate_type_id', 'real_estate', 'real_estate_type_id');


        $this->createTable('type_voting', [
            'id' => $this->primaryKey(),
            'name' => $this->string(64)->notNull()->unique(),
        ]);
        $this->insert('type_voting', [
            'name' => 'Годовое',
        ]);
        $this->insert('type_voting', [
            'name' => 'Внеочередное',
        ]);


        $this->createTable('form_voting', [
            'id' => $this->primaryKey(),
            'name' => $this->string(64)->notNull()->unique(),
        ]);
        $this->insert('form_voting', [
            'name' => 'Очное',
        ]);
        $this->insert('form_voting', [
            'name' => 'Заочное',
        ]);
        $this->insert('form_voting', [
            'name' => 'Очно-заочное',
        ]);


        $this->createTable('company', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->unique(),
            'description' => $this->string(255),
            'phones' => 'jsonb',
            'city_id' => $this->integer(),
            'street_id' => $this->integer(),
            'house_id' => $this->integer(),
            'real_estate_num' => $this->string(20),
            'opening_hours_from' => $this->string(5),
            'opening_hours_to' => $this->string(5),
            'url' => $this->string(127),
            'email' => $this->string(127),
            'inn' => $this->bigInteger(),
            'ogrn' => $this->bigInteger(),
        ]);
        $this->addForeignKey("fk_company_city", 'company', "city_id", "city", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_company_city_id', 'company', 'city_id');

        $this->addForeignKey("fk_company_street", 'company', "street_id", "street", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_company_street_id', 'company', 'street_id');

        $this->addForeignKey("fk_company_house", 'company', "house_id", "house", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_company_house_id', 'company', 'house_id');


        $this->createTable('type_owner', [
            'id' => $this->primaryKey(),
            'name' => $this->string(64)->notNull()->unique(),
        ]);
        $this->insert('type_owner', [
            'name' => 'Юридическое лицо',
        ]);
        $this->insert('type_owner', [
            'name' => 'Физическое лицо',
        ]);

        $this->createTable('owner', [
            'id' => $this->primaryKey(),
//            'name' => $this->string(255)->notNull()->unique(),
            'name' => $this->string(255)->notNull(),
            'type_owner_id' => $this->integer(),
            'real_estate_id' => $this->integer(),
            'ownership' => $this->string(127),
            'passport' => $this->string(127),
            'email' => $this->string(127),
            'phone' => $this->integer(),
            'percent_own' => $this->float(),

            'ogrn' => $this->integer(),
            'address' => $this->string(127),
            'legal_form' => $this->string(127),
            'url' => $this->string(127),
        ]);
        $this->addForeignKey("fk_owner_type_owner", 'owner', "type_owner_id", "type_owner", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_owner_type_owner_id', 'owner', 'type_owner_id');

        $this->addForeignKey("fk_owner_real_estate", 'owner', "real_estate_id", "real_estate", "id", "RESTRICT", "RESTRICT");
        $this->createIndex('idx_owner_real_estate_id', 'owner', 'real_estate_id');

        $this->createTable('title', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'short_name' => $this->string(64)->notNull(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('owner');
        $this->dropTable('type_owner');
        $this->dropTable('company');
        $this->dropTable('form_voting');
        $this->dropTable('type_voting');
        $this->dropTable('real_estate');
        $this->dropTable('real_estate_type');
        $this->dropTable('house');
        $this->dropTable('street');
        $this->dropTable('city');
        $this->dropTable('region');
    }

}


