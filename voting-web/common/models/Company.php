<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Url;

/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $phones
 * @property int|null $city_id
 * @property int|null $street_id
 * @property int|null $house_id
 * @property string|null $real_estate_num
 * @property string|null $opening_hours_from
 * @property string|null $opening_hours_to
 * @property string|null $url
 * @property string|null $email
 * @property int|null $inn
 * @property int|null $ogrn
 *
 * @property City $city
 * @property House $house
 * @property Street $street
 */
class Company extends ActiveRecord
{
    const MAX_PHONES = 3;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'description'], 'string', 'max' => 255],
            [['name', 'description'], 'filter', 'filter' => 'trim'],

            [['phones'], 'safe'],
            [['city_id', 'street_id', 'house_id'], 'default', 'value' => null],
            [['city_id', 'street_id', 'house_id'], 'integer'],
            [['real_estate_num'], 'string', 'max' => 20],
            [['opening_hours_from', 'opening_hours_to'], 'string', 'max' => 5],
            [['opening_hours_from', 'opening_hours_to'], 'default', 'value' => null],
            [['opening_hours_from', 'opening_hours_to'], 'validateTime'],

            [['url', 'email'], 'string', 'max' => 127],
            [['url'], 'url'],
            [['email'], 'email'],

            [['name'], 'unique'],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::className(), 'targetAttribute' => ['city_id' => 'id']],
            [['house_id'], 'exist', 'skipOnError' => true, 'targetClass' => House::className(), 'targetAttribute' => ['house_id' => 'id']],
            [['street_id'], 'exist', 'skipOnError' => true, 'targetClass' => Street::className(), 'targetAttribute' => ['street_id' => 'id']],

            [['inn'], 'string', 'max' => 12],
            [['inn'], 'match', 'pattern' => '/^\d{12}$/','enableClientValidation'=> false],
            [['inn'], 'string', 'max' => 13],
            [['ogrn'], 'match', 'pattern' => '/^\d{13}$/','enableClientValidation'=> false],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование компании',
            'description' => 'Описание',
            'phones' => 'Телефонные номера',
            'city_id' => 'Город',
            'street_id' => 'Улица',
            'house_id' => 'Дом',
            'real_estate_num' => 'Офис',
            'opening_hours_from' => 'Начало работы',
            'opening_hours_to' => 'Окончание работы',
            'url' => 'Сайт компании',
            'email' => 'Email',
            'inn' => 'ИНН',
            'ogrn' => 'ОГРН',
        ];
    }

    public function validateTime($attribute, $params)
    {
        $value = $this->$attribute;
        if (empty($value)) {
            $this->$attribute = null;
            return;
        } else {
            $time_parts = explode(':', $value);
            if (count($time_parts) != 2) {
                $this->addError($attribute, 'Формат времени указан не верно');
                return;
            }
            if (!(is_numeric($time_parts[0]) && ($time_parts[0] >= 0) && ($time_parts[0] <= 23))) {
                $this->addError($attribute, 'Часы указаны не верно');
                return;
            }
            if (!(is_numeric($time_parts[0]) && ($time_parts[1] >= 0) && ($time_parts[1] <= 59))) {
                $this->addError($attribute, 'Минуты указаны не верно');
                return;
            }
        }
    }

    /**
     * Gets query for [[City]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
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
     * Gets query for [[Street]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStreet()
    {
        return $this->hasOne(Street::className(), ['id' => 'street_id']);
    }

    public function getCompanyPhones()
    {
        $companyPhones = [];
        for ($i=0; $i<self::MAX_PHONES; $i++) {
            $companyPhones[] = (isset($this->phones[$i]) ? new CompanyPhone($this->phones[$i]) : new CompanyPhone());
        }
        return $companyPhones;
    }

    /**
     * @param $companyPhones[] CompanyPhone
     */
    public function setCompanyPhones($companyPhones)
    {
        $phones = [];
        foreach ($companyPhones as $companyPhone) {
            $phones[] = [
                "phone" => ($companyPhone->phone ?? null),
                "description" => ($companyPhone->description ?? null),
            ];
        }
        $this->phones = $phones;
    }

}
