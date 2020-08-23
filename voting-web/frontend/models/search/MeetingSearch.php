<?php

namespace frontend\models\search;

use common\models\Meeting;
use common\models\ActiveDataProviderVotes;
use common\validators\DateReformat;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class MeetingSearch extends Meeting
{
    public $city_ids;
    public $street_ids;
    public $house_ids;

    public $street_name;
    public $house_num;

    public $created_at_from;
    public $created_at_to;
    public $created_at_from_formatted;
    public $created_at_to_formatted;

    public function rules()
    {
        return [
            [['city_ids', 'street_ids', 'house_ids'], 'safe'],
            [['street_name', 'house_num', 'reg_num'], 'string'],
            [['created_at_from', 'created_at_to', 'created_at'], 'date'],
            [['created_at_from'], DateReformat::className(), 'targetField' => 'created_at_from_formatted'],
            [['created_at_to'], DateReformat::className(), 'targetField' => 'created_at_to_formatted'],
        ];
    }

    public function search($params)
    {
        $session = Yii::$app->session;

        if (!isset($params['MeetingSearch'])) {
            if ($session->has('MeetingSearch')){
                $params['MeetingSearch'] = $session['MeetingSearch'];
            }
        }
        else{
            $session->set('MeetingSearch', $params['MeetingSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('MeetingSearchSort')){
                $params['sort'] = $session['MeetingSearchSort'];
            }
        }
        else{
            $session->set('MeetingSearchSort', $params['sort']);
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
            $fieldSort = 'created_at_from';
        }

        $query = new Query();
        $query->addSelect([
            'meeting.id',
            'meeting.reg_num',
            'meeting.area',
            'meeting.type_voting_id',
            'meeting.form_voting_id',
            'meeting.created_at',
            'type_voting.name as type_voting_name',
            'form_voting.name as form_voting_name',
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
                'reg_num',
                'house_num',
                'street_name',
                'city_name',
                'created_at_from' => [
                    'asc' => ['meeting.created_at' => SORT_ASC],
                    'desc' => ['meeting.created_at' => SORT_DESC],
                ],

            ]
        ]);

        $this->load($params);

        if (!$this->created_at_from) {
            $this->created_at_from_formatted = null;
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['ilike', 'meeting.reg_num', $this->reg_num]);
        $query->andFilterWhere(['ilike', 'house.num', $this->house_num]);
        $query->andFilterWhere(['IN', 'house.street_id', $this->street_ids]);
        $query->andFilterWhere(['IN', 'street.city_id', $this->city_ids]);
        $query->andFilterWhere(['>=', 'meeting.created_at', $this->created_at_from_formatted]);
        $query->andFilterWhere(['<=', new Expression("CAST(meeting.created_at as date)"), $this->created_at_to_formatted]);


        return $dataProvider;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge($labels, [
            'house_num' => Yii::t('app', 'Дом'),
            'street_name' => Yii::t('app', 'Название улицы'),
            'city_name' => Yii::t('app', 'Город'),
        ]);
        return $labels;
    }

}