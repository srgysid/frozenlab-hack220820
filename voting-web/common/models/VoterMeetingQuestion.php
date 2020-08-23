<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "voter_meeting_question".
 *
 * @property int $id
 * @property int $meeting_voter_id
 * @property int $meeting_question_id
 * @property int|null $choice
 *
 * @property MeetingQuestion $meetingQuestion
 * @property MeetingVoter $meetingVoter
 */
class VoterMeetingQuestion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'voter_meeting_question';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meeting_voter_id', 'meeting_question_id'], 'required'],
            [['meeting_voter_id', 'meeting_question_id', 'choice'], 'default', 'value' => null],
            [['meeting_voter_id', 'meeting_question_id', 'choice'], 'integer'],
            [['meeting_question_id'], 'exist', 'skipOnError' => true, 'targetClass' => MeetingQuestion::className(), 'targetAttribute' => ['meeting_question_id' => 'id']],
            [['meeting_voter_id'], 'exist', 'skipOnError' => true, 'targetClass' => MeetingVoter::className(), 'targetAttribute' => ['meeting_voter_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'meeting_voter_id' => 'Meeting Voter ID',
            'meeting_question_id' => 'Meeting Question ID',
            'choice' => 'Choice',
        ];
    }

    /**
     * Gets query for [[MeetingQuestion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeetingQuestion()
    {
        return $this->hasOne(MeetingQuestion::className(), ['id' => 'meeting_question_id']);
    }

    /**
     * Gets query for [[MeetingVoter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeetingVoter()
    {
        return $this->hasOne(MeetingVoter::className(), ['id' => 'meeting_voter_id']);
    }
}
