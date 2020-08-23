<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\db\Expression;

/**
 * This is the model class for table "district".
 *
 * @property int $id
 * @property int $region_id
 * @property string|null $name
 * @property string|null $pref_name
 * @property string|null $pref_short
 * @property string|null $fias_guid
 * @property string|null $kladr_guid
 *
 * @property City[] $cities
 * @property Region $region
 */
class District extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'district';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['region_id', 'name'], 'required'],
            [['region_id'], 'default', 'value' => null],
            [['region_id'], 'integer'],
            [['fias_guid'], 'string'],
            [['name'], 'string', 'max' => 127],
            [['pref_name'], 'string', 'max' => 30],
            [['pref_short'], 'string', 'max' => 12],
            [['kladr_guid'], 'string', 'max' => 24],
            [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => Region::className(), 'targetAttribute' => ['region_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'region_id' => 'Регион',
            'name' => 'Наименование',
            'pref_name' => 'Тип объекта',
            'pref_short' => 'Тип объекта (сокращенно)',
            'fias_guid' => 'ФИАС Код объекта',
            'kladr_guid' => 'КЛАДР Код объекта',
        ];

    }

    /**
     * Gets query for [[Cities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCities()
    {
        return $this->hasMany(City::className(), ['district_id' => 'id']);
    }

    /**
     * Gets query for [[Region]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }

    public static function getDistrictByRegion($region_id)
    {
        if (!$region_id) return [];

        $query = new Query();
        $query->addSelect([
            'district.id as id',
            'district.name as district_name',
            new Expression("CONCAT(district.pref_short, ' ', district.name) as name"),
        ])->from('district')
            ->where(['region_id' => $region_id])
            ->orderBy('district_name');
        return $query->all();
    }

}
