<?php

namespace frontend\models\search;

use common\models\TitleQuestion;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class TitleQuestionSearch extends TitleQuestion
{
    public $title_ids;
    public $question_ids;

    public function rules()
    {
        return [
            [['title_ids', 'question_ids'], 'safe'],
        ];
    }

    public function search($params)
    {
        $session = Yii::$app->session;

        if (!isset($params['TitleQuestionSearch'])) {
            if ($session->has('TitleQuestionSearch')){
                $params['TitleQuestionSearch'] = $session['TitleQuestionSearch'];
            }
        }
        else{
            $session->set('TitleQuestionSearch', $params['TitleQuestionSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('TitleQuestionSearchSort')){
                $params['sort'] = $session['TitleQuestionSearchSort'];
            }
        }
        else{
            $session->set('TitleQuestionSearchSort', $params['sort']);
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
            $fieldSort = 'title_short_name';
        }

        $query = new Query();
        $query->addSelect([
            'title_question.id as id',
            'title.short_name as title_short_name',
            'question.short_name as question_short_name',
        ])->from('title_question')
            ->leftJoin('title', 'title.id = title_question.title_id')
            ->leftJoin('question', 'question.id = title_question.question_id')
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
                'title_short_name',
                'question_short_name',
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

        $query->andFilterWhere(['IN', 'title_question.title_id', $this->title_ids]);
        $query->andFilterWhere(['IN', 'title_question.question_id', $this->question_ids]);

        return $dataProvider;
    }

}