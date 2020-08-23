<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\db\Expression;

/**
 * This is the model class for table "street".
 *
 * @property int $id
 * @property int $city_id
 * @property string $name
 *
 * @property House[] $houses
 * @property City $city
 * @property string $fias_guid
 */
class Street extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'street';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['city_id', 'name'], 'required'],
            [['city_id'], 'default', 'value' => null],
            [['city_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['pref_name'], 'string', 'max' => 24],
            [['pref_short'], 'string', 'max' => 12],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::className(), 'targetAttribute' => ['city_id' => 'id']],
            [['fias_guid'], 'safe'],
            [['kladr_guid'], 'string', 'max' => 19],
            [['city_id', 'name'], 'unique', 'targetAttribute' => ['city_id', 'name'], 'message'=> Yii::t('app', 'Комбинация параметров Город и Наименование улицы уже существует.')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'city_id' => 'Город',
            'pref_name' => 'Префикс улицы',
            'pref_short' => 'Короткий префикс',
            'name' => 'Наименование улицы',
            'fias_guid' => 'ФИАС Код объекта',
            'kladr_guid' => 'КЛАДР Код объекта',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHouses()
    {
        return $this->hasMany(House::className(), ['street_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    public static function getStreetByCity($city_id)
    {
        if (!$city_id) return [];

        $query = new Query();
        $query->addSelect([
            'street.id as id',
            'street.name as street_name',
            new Expression("CONCAT(street.pref_short, ' ', street.name) as name"),
        ])->from('street')
            ->where(['city_id' => $city_id])
            ->orderBy('street_name');
        return $query->all();
    }

    public static function getStreetWithCity()
    {
        $query = new Query();
        $query->addSelect([
            'street.id as id',
            'street.name as street_name',
            new Expression("CONCAT(street.pref_short, ' ', street.name, ' (', city.name, ')') as name"),
        ])->from('street')
            ->leftJoin('city', 'city.id = street.city_id')
            ->orderBy('street_name');
        return $query->all();
    }

}
