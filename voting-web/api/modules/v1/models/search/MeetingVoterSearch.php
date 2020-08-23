<?php

namespace api\modules\v1\models\search;

use common\models\MeetingVoter;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\Expression;

class MeetingVoterSearch extends MeetingVoter
{
    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['order_num'], 'safe'],
        ];
    }

    public function searchByMeetingVoter($params, $meeting_id, $phone)
    {
        $subQuery = new Query();
        $subQuery->addSelect([
            'ownership'
        ])->from('owner')
            ->where(['=','owner.phone', $phone])
        ;

        $query = new Query();
        $query->addSelect([
            'voter_meeting_question.id as id',
            'meeting_question.order_num as order_num',
            'meeting_question.topic as topic',
            'meeting_question.proposal as proposal',
            'voter_meeting_question.choice as choice',
        ])->from('voter_meeting_question')
            ->leftJoin('meeting_question', 'meeting_question.id = voter_meeting_question.meeting_question_id')
            ->leftJoin('meeting_voter', 'meeting_voter.id = voter_meeting_question.meeting_voter_id')
            ->where(['meeting_voter.meeting_id' => $meeting_id, 'meeting_voter.vote_source' => MeetingVoter::SOURCE_MOBILE])
            ->andWhere(['IN', 'meeting_voter.ownership', $subQuery])
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->key = 'id';

        $dataProvider->setSort([
            'defaultOrder' => ['id' => SORT_ASC],
            'attributes' => [
                'id',
                'order_num',
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->andWhere('1=0');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        return $dataProvider;
    }

}