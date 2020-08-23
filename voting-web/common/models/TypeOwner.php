<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "type_owner".
 *
 * @property int $id
 * @property string $name
 *
 * @property Owner[] $owners
 */
class TypeOwner extends \yii\db\ActiveRecord
{
    const LEGAL_ENTITY = 1;
    const PHYSICAL_ENTITY = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'type_owner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 64],
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
        ];
    }

    /**
     * Gets query for [[Owners]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwners()
    {
        return $this->hasMany(Owner::className(), ['type_owner_id' => 'id']);
    }
}
