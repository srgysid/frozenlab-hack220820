<?php

namespace common\models;

use Yii;
use yii\db\Query;
/**
 * This is the model class for table "reestr_detail".
 *
 * @property int $id
 * @property int $reestr_id
 * @property string $num
 * @property float|null $area
 * @property string|null $type_real_estate
 * @property int|null $type_owner_id
 * @property string|null $part
 * @property string|null $ownership
 * @property string|null $email
 * @property int|null $phone
 *
 * @property Reestr $reestr
 * @property TypeOwner $typeOwner
 */
class ReestrDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reestr_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reestr_id', 'num', 'name'], 'required'],
            [['reestr_id', 'type_owner_id', 'phone'], 'default', 'value' => null],
            [['reestr_id', 'type_owner_id', 'phone'], 'integer'],
            [['area'], 'number'],
            [['num'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 255],
            [['type_real_estate'], 'string', 'max' => 64],
            [['part'], 'string', 'max' => 24],
            [['ownership', 'email'], 'string', 'max' => 127],
            [['reestr_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reestr::className(), 'targetAttribute' => ['reestr_id' => 'id']],
            [['type_owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => TypeOwner::className(), 'targetAttribute' => ['type_owner_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reestr_id' => 'Реестр',
            'num' => 'Номер помещения',
            'area' => 'Площадь помещения',
            'type_real_estate' => 'Тип помещения',
            'type_owner_id' => 'Тип собственника',
            'name' => 'Наименование собственника',
            'part' => 'Доля',
            'ownership' => 'Право собственности',
            'email' => 'Email',
            'phone' => 'Телефон',
        ];
    }

    /**
     * Gets query for [[Reestr]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReestr()
    {
        return $this->hasOne(Reestr::className(), ['id' => 'reestr_id']);
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

    public static function getReestrDetailByReestr($reestr_id)
    {
        if (!$reestr_id) return [];
        $query = new Query();
        $query->addSelect([
            'reestr_detail.id as id',
            'reestr_detail.name as name'
        ])->from('reestr_detail')
            ->where(['reestr_detail.reestr_id' => $reestr_id])
            ->orderBy('name');
        return $query->all();
    }
}
