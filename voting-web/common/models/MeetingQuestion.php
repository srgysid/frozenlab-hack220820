<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "meeting_question".
 *
 * @property int $id
 * @property int $meeting_id
 * @property int $question_id
 * @property int $order_num
 * @property string $topic
 * @property string $proposal
 *
 * @property Meeting $meeting
 * @property Question $question
 */
class MeetingQuestion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meeting_question';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meeting_id', 'question_id', 'order_num', 'topic', 'proposal', 'title_id'], 'required'],
            [['meeting_id', 'question_id', 'order_num', 'title_id'], 'default', 'value' => null],
            [['meeting_id', 'question_id', 'order_num', 'title_id'], 'integer'],
            [['topic', 'proposal'], 'string', 'max' => 255],
            [['quorum'], 'default', 'value' => 0],
            [['quorum'], 'double'],
            [['meeting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meeting::className(), 'targetAttribute' => ['meeting_id' => 'id']],
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
            'meeting_id' => 'Собрание',
            'question_id' => 'Краткое описание',
            'order_num' => 'Порядковый номер вопроса',
            'topic' => 'Описание вопроса',
            'proposal' => 'Предложение по вопросу',
            'title_id' => 'Тема',
            'quorum' => 'Кворум',
        ];
    }

    /**
     * Gets query for [[Meeting]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeeting()
    {
        return $this->hasOne(Meeting::className(), ['id' => 'meeting_id']);
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
