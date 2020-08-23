<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\db\Expression;

/**
 * This is the model class for table "user_profile".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $first_name
 * @property string $second_name
 * @property string $third_name
 * @property int $phone
 *
 * @property User $user
 */
class UserProfile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'phone'], 'default', 'value' => null],
            [['user_id', 'phone'], 'integer'],
            [['first_name', 'second_name', 'third_name'], 'required'],
            [['first_name', 'second_name', 'third_name'], 'string', 'max' => 255],
            [['first_name'], 'unique'],
            [['second_name'], 'unique'],
            [['third_name'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'first_name' => 'Имя',
            'second_name' => 'Фамилия',
            'third_name' => 'Отчество',
            'phone' => 'Телефон',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getFullName()
    {
        return trim($this->second_name.' '.$this->first_name.' '.$this->third_name);
    }

    public static function getUserByPhone($client_phone)
    {
        $query = new Query();
        $query->addSelect([
            new Expression("CONCAT(user_profile.second_name, ' ', user_profile.first_name, ' ', user_profile.third_name) as name"),
        ])->from('user_profile')
            ->andWhere(['phone' => $client_phone])
        ;
        return $query->one();
    }

}
