<?php

namespace api\modules\v1\models\search;

use common\models\Meeting;
use common\models\FormVoting;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\Expression;

class MeetingSearch extends Meeting
{
    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['reg_num'], 'string'],
        ];
    }

    public function search($params)
    {
        $query = new Query();
        $query->addSelect([
            'meeting.id',
            'meeting.reg_num',
            'meeting.created_at',
            'type_voting.name as type_voting_name',
            'form_voting.name as form_voting_name',
            'meeting.distant_started_at',
            'meeting.finished_at',
            'meeting.house_id',
            'house.num as house_num',
            'street.name as street_name',
            new Expression("concat(street.pref_short, ' ', street.name) as street_full"),
            new Expression("concat(city.pref_short, ' ', city.name) as city_name"),
        ])->from('meeting')
            ->leftJoin('type_voting', 'type_voting.id = meeting.type_voting_id')
            ->leftJoin('form_voting', 'form_voting.id = meeting.form_voting_id')
            ->leftJoin('house', 'house.id = meeting.house_id')
            ->leftJoin('street', 'street.id = house.street_id')
            ->leftJoin('city', 'city.id = street.city_id')
            ->where(['>', 'meeting.form_voting_id', FormVoting::INTRAMURAL])
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->key = 'id';

        $dataProvider->setSort([
            'defaultOrder' => ['reg_num' => SORT_ASC],
            'attributes' => [
                'id',
                'reg_num',
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

    public function searchByHouse($params, $house_id)
    {
        $query = new Query();
        $query->addSelect([
            'meeting.id',
            'meeting.reg_num',
            'meeting.created_at',
            'type_voting.name as type_voting_name',
            'form_voting.name as form_voting_name',
            'meeting.distant_started_at',
            'meeting.finished_at',
            'meeting.house_id',
            'house.num as house_num',
            'street.name as street_name',
            new Expression("concat(street.pref_short, ' ', street.name) as street_full"),
            new Expression("concat(city.pref_short, ' ', city.name) as city_name"),
        ])->from('meeting')
            ->leftJoin('type_voting', 'type_voting.id = meeting.type_voting_id')
            ->leftJoin('form_voting', 'form_voting.id = meeting.form_voting_id')
            ->leftJoin('house', 'house.id = meeting.house_id')
            ->leftJoin('street', 'street.id = house.street_id')
            ->leftJoin('city', 'city.id = street.city_id')
            ->where(['meeting.house_id' =>$house_id])
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->key = 'id';

        $dataProvider->setSort([
            'defaultOrder' => ['reg_num' => SORT_ASC],
            'attributes' => [
                'id',
                'reg_num',
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