<?php

namespace frontend\models\search;

use common\models\MeetingVoter;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class MeetingVoterSearch extends MeetingVoter
{

    public $type_owner_ids;
    public $vote_source_ids;

    public function rules()
    {
        return [
            [['name', 'num', 'type_real_estate', 'ownership'], 'string'],
            [['phone'], 'integer'],
            [['type_owner_ids', 'vote_source_ids'], 'safe'],
        ];
    }

    public function search($params, $meeting_id)
    {
        $session = Yii::$app->session;

        if (!isset($params['MeetingVoterSearch'])) {
            if ($session->has('MeetingVoterSearch')){
                $params['MeetingVoterSearch'] = $session['MeetingVoterSearch'];
            }
        }
        else{
            $session->set('MeetingVoterSearch', $params['MeetingVoterSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('MeetingVoterSearchSort')){
                $params['sort'] = $session['MeetingVoterSearchSort'];
            }
        }
        else{
            $session->set('MeetingVoterSearchSort', $params['sort']);
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
            $fieldSort = 'num';
        }

        $query = new Query();
        $query->addSelect([
            'meeting_voter.id as id',
            'meeting_voter.num as num',
            'meeting_voter.name as name',
            'meeting_voter.area as area',
            'meeting_voter.part as part',
            'meeting_voter.ownership as ownership',
            'meeting_voter.type_real_estate as type_real_estate',
            'meeting_voter.phone as phone',
            'meeting_voter.vote_source as vote_source',
            'type_owner.name as type_owner_name',
            'house.area as house_area'
        ])->from('meeting_voter')
            ->leftJoin('type_owner', 'type_owner.id = meeting_voter.type_owner_id')
            ->leftJoin('meeting', 'meeting.id = meeting_voter.meeting_id')
            ->leftJoin('house', 'house.id = meeting.house_id')
            ->where(['meeting_voter.meeting_id' => $meeting_id])
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
                'num',
                'name',
                'phone',
                'area',
                'type_real_estate',

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

        $query->andFilterWhere(['ilike', 'meeting_voter.num', $this->num]);
        $query->andFilterWhere(['ilike', 'meeting_voter.name', $this->name]);
        $query->andFilterWhere(['ilike', 'meeting_voter.ownership', $this->ownership]);
        $query->andFilterWhere(['in', 'meeting_voter.type_owner_id', $this->type_owner_ids]);
        $query->andFilterWhere(['in', 'meeting_voter.vote_source', $this->vote_source_ids]);
        $query->andFilterWhere(['=', 'meeting_voter.phone', $this->phone]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge($labels, [
            'type_owner_name' => Yii::t('app', 'Тип собственника'),
            'house_area' => Yii::t('app', 'Общая площадь дома'),
            'cntPercent' => Yii::t('app', 'Количество голосов'),
        ]);

        return $labels;
    }

}