<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "reestr_meeting".
 *
 * @property int $id
 * @property int $reestr_id
 * @property int $meeting_id
 *
 * @property Meeting $meeting
 * @property Reestr $reestr
 */
class ReestrMeeting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reestr_meeting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reestr_id', 'meeting_id'], 'required'],
            [['reestr_id', 'meeting_id'], 'default', 'value' => null],
            [['reestr_id', 'meeting_id'], 'integer'],
            [['meeting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meeting::className(), 'targetAttribute' => ['meeting_id' => 'id']],
            [['reestr_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reestr::className(), 'targetAttribute' => ['reestr_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reestr_id' => 'Reestr ID',
            'meeting_id' => 'Meeting ID',
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
     * Gets query for [[Reestr]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReestr()
    {
        return $this->hasOne(Reestr::className(), ['id' => 'reestr_id']);
    }

    public static function checkCountReestrMeeting($reestr_id)
    {
        $sql = "
            select count(*)
            from reestr_meeting
            where reestr_meeting.reestr_id = :reestr_id
        ";

        $cnt = Yii::$app->db->createCommand($sql, ['reestr_id' => $reestr_id])->queryScalar();
        return ($cnt > 0);
    }

}
