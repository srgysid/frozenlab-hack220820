<?php

namespace frontend\models\search;

use common\models\MeetingQuestion;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class MeetingQuestionSearch extends MeetingQuestion
{
    public $question_ids;
    public $title_ids;

    public function rules()
    {
        return [
            [['question_ids', 'title_ids', 'topic', 'proposal'], 'safe'],
        ];
    }

    public function search($params, $meeting_id)
    {
        $session = Yii::$app->session;

        if (!isset($params['MeetingQuestionSearch'])) {
            if ($session->has('MeetingQuestionSearch')){
                $params['MeetingQuestionSearch'] = $session['MeetingQuestionSearch'];
            }
        }
        else{
            $session->set('MeetingQuestionSearch', $params['MeetingQuestionSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('MeetingQuestionSearchSort')){
                $params['sort'] = $session['MeetingQuestionSearchSort'];
            }
        }
        else{
            $session->set('MeetingQuestionSearchSort', $params['sort']);
        }

        if (isset($params["sort"])) {
            $pos = stripos($params["sort"], '-');
            if ($pos !== false) {
                $typeSort = SORT_DESC;
                $fieldSort = substr($params["sort"], 1);
            } else {
                $typeSort = SORT_ASC;
                $fieldSort = $params["sort"];
            }
        }
        else {
            $typeSort = SORT_ASC;
            $fieldSort = 'order_num';
        }

        $query = new Query();
        $query->addSelect([
            'meeting_question.id as id',
            'meeting_question.order_num as order_num',
            'meeting_question.topic as topic',
            'meeting_question.proposal as proposal',
            'question.short_name as question_short_name',
            'title.short_name as title_short_name',
        ])->from('meeting_question')
            ->leftJoin('question', 'question.id = meeting_question.question_id')
            ->leftJoin('title', 'title.id = meeting_question.title_id')
            ->where(['meeting_question.meeting_id'=>$meeting_id])
        ;

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProviderVotes([
            'query' => $query,
        ]);

        $dataProvider->key = 'id';

        $dataProvider->setSort([
            'defaultOrder' => [$fieldSort => $typeSort],
            'attributes' => [
                'id',
                'order_num',
                'topic',
                'question_short_name',
                'title_short_name',
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['IN', 'meeting_question.question_id', $this->question_ids]);
        $query->andFilterWhere(['IN', 'meeting_question.title_id', $this->title_ids]);
        $query->andFilterWhere(['ilike', 'meeting_question.topic', $this->topic]);
        $query->andFilterWhere(['ilike', 'meeting_question.proposal', $this->proposal]);

        return $dataProvider;
    }

}