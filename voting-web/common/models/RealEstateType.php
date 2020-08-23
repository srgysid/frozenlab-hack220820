<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\db\Expression;

/**
 * This is the model class for table "real_estate_type".
 *
 * @property int $id
 * @property string $name
 * @property string|null $short_name
 *
 * @property RealEstate[] $realEstates
 */
class RealEstateType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'real_estate_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 64],
            [['short_name'], 'string', 'max' => 16],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'short_name' => 'Short Name',
        ];
    }

    /**
     * Gets query for [[RealEstates]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRealEstates()
    {
        return $this->hasMany(RealEstate::className(), ['real_estate_type_id' => 'id']);
    }

//    public function getRealEstateType($real_estate_type_id)
//    {
//        $query = new Query();
//        $query->addSelect([
//            'real_estate_type.short_name as short_name'
//        ])->from('real_estate_type')
//            ->where(['real_estate_type.id' => $real_estate_type_id])
//        ;
//        $retVal = $query->one();
//        return $retVal['short_name'];
//    }

    public static function getRealEstateTypeList()
    {
        $query = new Query();
        $query->addSelect([
            'real_estate_type.id as id',
            'real_estate_type.name as name',
            'real_estate_type.short_name as short_name',
        ])->from('real_estate_type')
            ->orderBy('short_name')
        ;
        return $query->all();
    }

}
