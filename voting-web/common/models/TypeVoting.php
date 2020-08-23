<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "type_voting".
 *
 * @property int $id
 * @property string $name
 */
class TypeVoting extends \yii\db\ActiveRecord
{
    const ANNUAL = 1;
    const EXTRAORDINARY = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'type_voting';
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
}
