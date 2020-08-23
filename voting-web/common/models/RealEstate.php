<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\db\Expression;

/**
 * This is the model class for table "real_estate".
 *
 * @property int $id
 * @property int $house_id
 * @property int $real_estate_type_id
 * @property string $num
 *
 * @property Owner[] $owners
 * @property House $house
 * @property RealEstateType $realEstateType
 */
class RealEstate extends \yii\db\ActiveRecord
{

    public $city_id;
    public $street_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'real_estate';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['house_id', 'real_estate_type_id', 'num'], 'required'],
            [['house_id', 'real_estate_type_id'], 'default', 'value' => null],
            [['house_id', 'real_estate_type_id'], 'integer'],
            [['house_id', 'real_estate_type_id', 'num'], 'unique', 'targetAttribute' => ['house_id', 'real_estate_type_id', 'num'], 'message'=> Yii::t('app', 'Комбинация параметров Дом, Тип помещения и Номер уже существует.')],
            [['num'], 'string', 'max' => 10],
            [['num'], 'filter', 'filter' => 'trim'],
            [['num'], 'filter', 'filter' => function ($value){return mb_strtoupper($value);}],
            [['area'], 'default', 'value' => 0],
            [['area'], 'double'],
            [['house_id'], 'exist', 'skipOnError' => true, 'targetClass' => House::className(), 'targetAttribute' => ['house_id' => 'id']],
            [['real_estate_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => RealEstateType::className(), 'targetAttribute' => ['real_estate_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'house_id' => 'Дом',
            'num' => 'Номер',
            'real_estate_type_id' => 'Тип помещения',
            'street_id' => 'Улица',
            'city_id' => 'Город',
            'area' => 'Площадь помещения',
        ];
    }

    /**
     * Gets query for [[Owners]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwners()
    {
        return $this->hasMany(Owner::className(), ['real_estate_id' => 'id']);
    }

    /**
     * Gets query for [[House]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHouse()
    {
        return $this->hasOne(House::className(), ['id' => 'house_id']);
    }

    /**
     * Gets query for [[RealEstateType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRealEstateType()
    {
        return $this->hasOne(RealEstateType::className(), ['id' => 'real_estate_type_id']);
    }

    public static function getRealEstateByHouse($house_id)
    {
        if (!$house_id) return [];

        $out = new Query();
        $out->addSelect([
            'real_estate.id as id',
            new Expression("CONCAT(real_estate_type.short_name, ' ', real_estate.num) as name"),
        ])->from('real_estate')
            ->leftJoin('real_estate_type', 'real_estate_type.id = real_estate.real_estate_type_id')
            ->where(['house_id' => $house_id])
            ->orderBy(new Expression("NULLIF(regexp_replace(real_estate.num, '\D','','g'), '')::numeric"));
        return $out->all();
    }

}
