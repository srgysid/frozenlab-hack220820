<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "title".
 *
 * @property int $id
 * @property string $name
 * @property string $short_name
 *
 * @property Meeting[] $meetings
 */
class Title extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'title';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'short_name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['short_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Описание',
            'short_name' => 'Краткое наименование',
        ];
    }

    /**
     * Gets query for [[MeetingQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeetingQuestions()
    {
        return $this->hasMany(MeetingQuestion::className(), ['title_id' => 'id']);
    }

    /**
     * Gets query for [[TitleQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTitleQuestions()
    {
        return $this->hasMany(TitleQuestion::className(), ['title_id' => 'id']);
    }

}
