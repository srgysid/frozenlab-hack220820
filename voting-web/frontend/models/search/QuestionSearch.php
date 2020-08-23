<?php

namespace frontend\models\search;

use common\models\Question;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class QuestionSearch extends Question
{
    public function rules()
    {
        return [
            [['short_name', 'topic', 'proposal'], 'safe'],
        ];
    }

    public function search($params)
    {
        $session = Yii::$app->session;

        if (!isset($params['QuestionSearch'])) {
            if ($session->has('QuestionSearch')){
                $params['QuestionSearch'] = $session['QuestionSearch'];
            }
        }
        else{
            $session->set('QuestionSearch', $params['QuestionSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('QuestionSearchSort')){
                $params['sort'] = $session['QuestionSearchSort'];
            }
        }
        else{
            $session->set('QuestionSearchSort', $params['sort']);
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
            $fieldSort = 'short_name';
        }

        $query = new Query();
        $query->addSelect([
            'question.id as id',
            'question.short_name as short_name',
            'question.topic as topic',
            'question.proposal as proposal',
        ])->from('question')
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
                'short_name',
                'topic',
                'proposal',
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

        $query->andFilterWhere(['ilike', 'question.short_name', $this->short_name]);
        $query->andFilterWhere(['ilike', 'question.topic', $this->topic]);
        $query->andFilterWhere(['ilike', 'question.proposal', $this->proposal]);

        return $dataProvider;
    }

}