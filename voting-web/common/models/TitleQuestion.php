<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "title_question".
 *
 * @property int $id
 * @property int $title_id
 * @property int $question_id
 *
 * @property Question $question
 * @property Title $title
 */
class TitleQuestion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'title_question';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title_id', 'question_id'], 'required'],
            [['title_id', 'question_id'], 'default', 'value' => null],
            [['title_id', 'question_id'], 'integer'],
            [['question_id'], 'exist', 'skipOnError' => true, 'targetClass' => Question::className(), 'targetAttribute' => ['question_id' => 'id']],
            [['title_id'], 'exist', 'skipOnError' => true, 'targetClass' => Title::className(), 'targetAttribute' => ['title_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title_id' => 'Тема',
            'question_id' => 'Вопрос',
        ];
    }

    /**
     * Gets query for [[Question]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestion()
    {
        return $this->hasOne(Question::className(), ['id' => 'question_id']);
    }

    /**
     * Gets query for [[Title]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTitle()
    {
        return $this->hasOne(Title::className(), ['id' => 'title_id']);
    }
}
