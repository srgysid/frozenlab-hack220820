<?php

namespace api\modules\v1\models\search;

use common\models\MeetingQuestion;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\Expression;

class MeetingQuestionSearch extends MeetingQuestion
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

    public function searchByMeeting($params, $meeting_id)
    {
        $query = new Query();
        $query->addSelect([
            'meeting_question.id as id',
            'meeting_question.order_num as order_num',
            'title.short_name as title_short_name',
            'question.short_name as question_short_name',
            'meeting_question.topic as topic',
            'meeting_question.proposal as proposal',
        ])->from('meeting_question')
            ->leftJoin('question', 'question.id = meeting_question.question_id')
            ->leftJoin('title', 'title.id = meeting_question.title_id')
            ->where(['meeting_question.meeting_id' => $meeting_id])
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->key = 'id';

        $dataProvider->setSort([
            'defaultOrder' => ['order_num' => SORT_ASC],
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