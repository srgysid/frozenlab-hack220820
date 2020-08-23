<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "initiator_owner".
 *
 * @property int $id
 * @property int|null $meeting_id
 * @property int|null $owner_id
 *
 * @property Meeting $meeting
 * @property Owner $owner
 */
class InitiatorOwner extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'initiator_owner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meeting_id', 'owner_id'], 'default', 'value' => null],
            [['meeting_id', 'owner_id'], 'integer'],
            [['meeting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meeting::className(), 'targetAttribute' => ['meeting_id' => 'id']],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => Owner::className(), 'targetAttribute' => ['owner_id' => 'id']],
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
            'owner_id' => 'Owner ID',
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
     * Gets query for [[Owner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(Owner::className(), ['id' => 'owner_id']);
    }

    public static function getInitiatorOwnerByMeetingId($meeting_id) {
        $initiator_owner_ids = InitiatorOwner::find()->andWhere(['meeting_id' => $meeting_id])->all();
        $initiator_owner_ids = ArrayHelper::getColumn($initiator_owner_ids, 'owner_id');
        return $initiator_owner_ids;
    }

}
