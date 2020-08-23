<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "form_voting".
 *
 * @property int $id
 * @property string $name
 */
class FormVoting extends \yii\db\ActiveRecord
{
    const INTRAMURAL = 1;
    const DISTANT = 2;
    const FULL_TIME = 3;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'form_voting';
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
