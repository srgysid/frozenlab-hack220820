<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "question".
 *
 * @property int $id
 * @property string $short_name
 * @property string $topic
 * @property string $proposal
 *
 * @property MeetingQuestion[] $meetingQuestions
 * @property TitleQuestion[] $titleQuestions
 */
class Question extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'question';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['short_name', 'topic', 'proposal'], 'required'],
            [['short_name'], 'string', 'max' => 64],
            [['topic', 'proposal'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'short_name' => 'Сокращенное описание вопроса',
            'topic' => 'Описание вопроса',
            'proposal' => 'Предложение по вопросу',
        ];
    }

    /**
     * Gets query for [[MeetingQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeetingQuestions()
    {
        return $this->hasMany(MeetingQuestion::className(), ['question_id' => 'id']);
    }

    /**
     * Gets query for [[TitleQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTitleQuestions()
    {
        return $this->hasMany(TitleQuestion::className(), ['question_id' => 'id']);
    }

    public static function getQuestionByTitle($title_id)
    {
        if (!$title_id) return [];

        $query = new Query();
        $query->addSelect([
            'question.id as id',
            'question.short_name as name',
            'question.short_name as short_name',
        ])->from('question')
            ->leftJoin('title_question', 'question_id = question.id')
            ->where(['title_question.title_id' => $title_id])
            ->orderBy('short_name');
        return $query->all();
    }

}
