<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "city".
 *
 * @property int $id
 * @property string $name
 *
 * @property Street[] $streets
 * @property Street[] $streets0
 * @property string $fias_guid
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'city';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['region_id', 'name'], 'required'],
            [['region_id', 'district_id'], 'default', 'value' => null],
            [['region_id', 'district_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['pref_name'], 'string', 'max' => 24],
            [['pref_short'], 'string', 'max' => 12],
            [['district_id'], 'exist', 'skipOnError' => true, 'targetClass' => District::className(), 'targetAttribute' => ['district_id' => 'id']],
            [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => Region::className(), 'targetAttribute' => ['region_id' => 'id']],
            [['fias_guid'], 'safe'],
            [['kladr_guid'], 'string', 'max' => 19],
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
            'name' => 'Наименование',
            'district_id' => 'Район',
            'region_id' => 'Регион',
            'fias_guid' => 'ФИАС Код объекта',
            'kladr_guid' => 'КЛАДР Код объекта',
            'pref_name' => 'Тип объекта',
            'pref_short' => 'Тип объекта (сокращенно)',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStreets()
    {
        return $this->hasMany(Street::className(), ['city_id' => 'id']);
    }

    public function getDistrict()
    {
        return $this->hasOne(District::className(), ['id' => 'district_id']);
    }

    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }

    public function getCompanies()
    {
        return $this->hasMany(Company::className(), ['city_id' => 'id']);
    }

    public static function getCityByRegion($region_id)
    {
        if (!$region_id) return [];

        $out = new Query();
        $out->addSelect([
            'city.id as id',
//            'city.name as name',
            new Expression("CONCAT(city.pref_short, ' ', city.name) as name"),
            new Expression("CONCAT(city.pref_name, ' ', city.name) as full_name"),
        ])->from('city')
            ->where(['region_id' => $region_id])
            ->orderBy('name');
        return $out->all();
    }

    public static function getCityFull()
    {
        $out = new Query();
        $out->addSelect([
            'city.id as id',
//            'city.name as name',
            new Expression("CONCAT(city.pref_short, ' ', city.name) as name"),
            new Expression("CONCAT(city.pref_name, ' ', city.name) as full_name"),
        ])->from('city')
            ->orderBy('name');
        return $out->all();
    }

}
