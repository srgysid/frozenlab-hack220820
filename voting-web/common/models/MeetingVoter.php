<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "meeting_voter".
 *
 * @property int $id
 * @property int $meeting_id
 * @property string $num
 * @property float|null $area
 * @property string|null $type_real_estate
 * @property int|null $type_owner_id
 * @property string|null $name
 * @property string|null $part
 * @property string|null $ownership
 * @property string|null $email
 * @property int|null $phone
 * @property int|null $vote_source
 *
 * @property Meeting $meeting
 * @property TypeOwner $typeOwner
 * @property VoterMeetingQuestion[] $voterMeetingQuestions
 */
class MeetingVoter extends \yii\db\ActiveRecord
{

    const SOURCE_MOBILE = 1;
    const SOURCE_OPERATOR = 2;

    const CHOICE_YES = 10;
    const CHOICE_NO = 20;
    const CHOICE_ABSTAINED = 30;

    public $arrValue;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meeting_voter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meeting_id', 'num'], 'required'],
            [['meeting_id', 'type_owner_id', 'phone', 'vote_source'], 'default', 'value' => null],
            [['meeting_id', 'type_owner_id', 'phone', 'vote_source'], 'integer'],
            [['area'], 'number'],
            [['num'], 'string', 'max' => 32],
            [['type_real_estate'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['part'], 'string', 'max' => 24],
            [['arrValue'], 'safe'],
            [['ownership', 'email'], 'string', 'max' => 127],
            [['meeting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meeting::className(), 'targetAttribute' => ['meeting_id' => 'id']],
            [['type_owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => TypeOwner::className(), 'targetAttribute' => ['type_owner_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'meeting_id' => 'Meeting ID',
            'num' => 'Номер помещения',
            'area' => 'Площадь помещения',
            'type_real_estate' => 'Тип помещения',
            'type_owner_id' => 'Тип собственника',
            'name' => 'Наименование собственника',
            'part' => 'Доля',
            'ownership' => 'Право собственности',
            'email' => 'Email',
            'phone' => 'Телефон',
            'vote_source' => 'Тип голосования',
        ];
    }

    public static function getSourceList()
    {
        return [
            '1' => Yii::t('app', 'Мобильное приложение'),
            '2' => Yii::t('app', 'Оператор'),
        ];
    }

    public static function getChoiceList()
    {
        return [
            '10' => Yii::t('app', 'За'),
            '20' => Yii::t('app', 'Против'),
            '30' => Yii::t('app', 'Воздержался'),
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
     * Gets query for [[TypeOwner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTypeOwner()
    {
        return $this->hasOne(TypeOwner::className(), ['id' => 'type_owner_id']);
    }

    /**
     * Gets query for [[VoterMeetingQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVoterMeetingQuestions()
    {
        return $this->hasMany(VoterMeetingQuestion::className(), ['meeting_voter_id' => 'id']);
    }

    public function saveMeetingVoter()
    {
        if ($this->save()) {
            $modelsMeetingQuestion = MeetingQuestion::find()->where(['meeting_id' => $this->meeting_id])->all();
            foreach ($modelsMeetingQuestion as $meetingQuestion){
                $modelVoterMeetingQuestion = new VoterMeetingQuestion();

                $modelVoterMeetingQuestion->meeting_voter_id = $this->id;
                $modelVoterMeetingQuestion->meeting_question_id = $meetingQuestion->id;
                $modelVoterMeetingQuestion->choice = $this->arrValue[$meetingQuestion->order_num]['choice'];

                if (!$modelVoterMeetingQuestion->save()) {
                    $this->addErrors($modelVoterMeetingQuestion->errors);
                }
            }
        }
        return (count($this->errors) == 0);
    }
}