<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property string $name
 * @property string $fias_guid
 *
 * @property Issue[] $issues
 */
class Region extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'region';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['kladr_guid'], 'string', 'max' => 19],
            [['pref_name'], 'string', 'max' => 24],
            [['fias_guid'], 'safe'],
            [['name'], 'filter', 'filter' => 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование региона',
            'pref_name' => 'Тип объекта',
            'fias_guid' => 'ФИАС Код объекта',
            'kladr_guid' => 'КЛАДР Код объекта',
        ];
    }

    public function getCities()
    {
        return $this->hasMany(City::className(), ['region_id' => 'id']);
    }

    public function getDistricts()
    {
        return $this->hasMany(District::className(), ['region_id' => 'id']);
    }

    public static function getFullRegion()
    {
        $out = new Query();
        $out->addSelect([
            'region.id as id',
            'region.name as region_name',
            new Expression("CONCAT(region.name, ' ', region.pref_name) as name"),
        ])->from('region')->orderBy('region_name');
        return $out->all();
    }

}
