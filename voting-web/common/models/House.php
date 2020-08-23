<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "house".
 *
 * @property int $id
 * @property int $street_id
 * @property string $num
 * @property int $ent
 *
 * @property Street $street
 * @property string $fullName
 * @property string $fias_guid
 */
class House extends \yii\db\ActiveRecord
{
    public $city_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'house';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['street_id', 'num'], 'required'],
            [['street_id'], 'default', 'value' => null],
            [['street_id'], 'integer'],
            [['area'], 'default', 'value' => 0],
            [['area'], 'double'],
            [['num'], 'string', 'max' => 25],
            [['street_id'], 'exist', 'skipOnError' => true, 'targetClass' => Street::className(), 'targetAttribute' => ['street_id' => 'id']],
            [['fias_guid'], 'safe'],
            [['kladr_guid'], 'string', 'max' => 19],
            [['street_id','num'], 'unique', 'targetAttribute' => ['street_id', 'num'], 'message'=> Yii::t('app', 'Комбинация параметров Улица, Номер дома уже существует.')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'street_id' => 'Улица',
            'num' => 'Номер дома',
            'area' => 'Общая площадь',
            'city_id' => 'Город',
            'fias_guid' => 'ФИАС Код объекта',
            'kladr_guid' => 'КЛАДР Код объекта',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStreet()
    {
        return $this->hasOne(Street::className(), ['id' => 'street_id']);
    }

    /**
     * Gets query for [[Meetings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeetings()
    {
        return $this->hasMany(Meeting::className(), ['house_id' => 'id']);
    }

    /**
     * Gets query for [[RealEstates]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRealEstates()
    {
        return $this->hasMany(RealEstate::className(), ['house_id' => 'id']);
    }

    public static function getHouseByStreet($streetId)
    {
        return House::find()->where(['street_id' => $streetId])->orderBy('num')->all();
    }

    public static function getFullHouseByStreet($street_id)
    {
        if (!$street_id) return [];
        $out = House::find()
            ->select([
                'id',
                'num as name',
            ])
            ->andWhere(['street_id' => $street_id])
            ->orderBy(new Expression("NULLIF(regexp_replace(house.num, '\D','','g'), '')::numeric"))
            ->asArray()->all();
        return $out;
    }

}
