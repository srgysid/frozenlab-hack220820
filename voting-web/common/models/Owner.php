<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "owner".
 *
 * @property int $id
 * @property string $name
 * @property int|null $type_owner_id
 * @property int|null $real_estate_id
 * @property string|null $ownership
 * @property string|null $passport
 * @property string|null $email
 * @property int|null $phone
 * @property float|null $percent_own
 * @property int|null $ogrn
 * @property string|null $adress
 * @property string|null $legal_form
 * @property string|null $url
 *
 * @property RealEstate $realEstate
 * @property TypeOwner $typeOwner
 */
class Owner extends \yii\db\ActiveRecord
{
    public $city_id;
    public $street_id;
    public $house_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'owner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_owner_id','name'], 'required'],
            [['type_owner_id', 'real_estate_id', 'phone', 'ogrn', 'city_id', 'street_id', 'house_id'], 'default', 'value' => null],
            [['type_owner_id', 'real_estate_id', 'phone', 'ogrn', 'city_id', 'street_id', 'house_id'], 'integer'],
            [['percent_own'], 'default', 'value' => 0],
            [['percent_own'], 'double'],
            [['name'], 'string', 'max' => 255],
            [['ownership', 'passport', 'email', 'address', 'legal_form', 'url'], 'string', 'max' => 127],
            [['email'], 'email'],
            [['url'], 'url'],
            [['real_estate_id'], 'exist', 'skipOnError' => true, 'targetClass' => RealEstate::className(), 'targetAttribute' => ['real_estate_id' => 'id']],
            [['type_owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => TypeOwner::className(), 'targetAttribute' => ['type_owner_id' => 'id']],

//            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::className(), 'targetAttribute' => ['city_id' => 'id']],
//            [['street_id'], 'exist', 'skipOnError' => true, 'targetClass' => Street::className(), 'targetAttribute' => ['street_id' => 'id']],
//            [['house_id'], 'exist', 'skipOnError' => true, 'targetClass' => House::className(), 'targetAttribute' => ['house_id' => 'id']],

//            [['real_estate_id','name'], 'unique', 'targetAttribute' => ['real_estate_id','name'], 'message'=> Yii::t('app', 'Комбинация параметров Помещение и Наименование собственника уже существует.')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование / ФИО собственника',
            'type_owner_id' => 'Тип',
            'real_estate_id' => 'Номер помещения',
//            'ownership' => 'Документ, подтверждающий право собственности',
            'ownership' => 'Право собственности',
            'passport' => 'Паспортные данные',
            'email' => 'Электронная почта',
            'phone' => 'Номер телефона',
            'percent_own' => 'Процент собственности',
            'ogrn' => 'ОГРН',
            'address' => 'Фактический адрес собственника',
            'legal_form' => 'Организационно-правовая форма',
            'url' => 'Сайт',
            'house_id' => Yii::t('app', 'Дом'),
            'street_id' => Yii::t('app', 'Улица'),
            'city_id' => Yii::t('app', 'Город'),

        ];
    }

    /**
     * Gets query for [[RealEstate]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRealEstate()
    {
        return $this->hasOne(RealEstate::className(), ['id' => 'real_estate_id']);
    }

    /**
     * Gets query for [[TypeOwner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTypeOwner()
    {
        return $this->hasOne(TypeOwner::className(), ['id' => 'type_owner_id']);
    }

    public static function getOwnerByHouse($house_id)
    {
        if (!$house_id) return [];
        $query = new Query();
        $query->addSelect([
            'owner.id as id',
            'owner.name as name'
        ])->from('owner')
            ->leftJoin('real_estate', 'real_estate.id = owner.real_estate_id')
            ->leftJoin('house', 'house.id = real_estate.house_id')
            ->where(['house.id' => $house_id])
            ->orderBy('name');
        return $query->all();
    }

}
