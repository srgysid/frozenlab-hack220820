<?php

namespace frontend\models\search;

use common\models\Owner;
use common\models\ActiveDataProviderVotes;
use yii\data\ActiveDataProvider;
use Yii;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class OwnerSearch extends Owner
{
    public $type_owner_name;
    public $type_owner_ids;
    public $street_full;
    public $street_ids;
    public $city_ids;
    public $house_num;
    public $real_estate_num;

    public function rules()
    {
        return [
            [['street_full','name', 'phone', 'type_owner_name'], 'string'],
            [['type_owner_ids', 'street_ids', 'city_ids','real_estate_num', 'house_num'], 'safe'],
        ];
    }

    public function search($params)
    {
        $session = Yii::$app->session;

        if (!isset($params['OwnerSearch'])) {
            if ($session->has('OwnerSearch')){
                $params['OwnerSearch'] = $session['OwnerSearch'];
            }
        }
        else{
            $session->set('OwnerSearch', $params['OwnerSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('OwnerSearchSort')){
                $params['sort'] = $session['OwnerSearchSort'];
            }
        }
        else{
            $session->set('OwnerSearchSort', $params['sort']);
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
            $fieldSort = 'name';
        }

        $query = new Query();
        $query->addSelect([
            'owner.id',
            'owner.name as name',
            'owner.type_owner_id',
            'owner.real_estate_id',
            'owner.phone as phone',
            'owner.ownership',
            'owner.passport',
            'owner.email',
            'owner.percent_own',
            'owner.ogrn',
            'owner.address',
            'owner.legal_form',
            'owner.url',
            'type_owner.name as type_owner_name',
            'real_estate.num as real_estate_num',
            'real_estate_type.short_name as real_estate_type_short_name',
            'house.num as house_num',
            'street.name as street_name',
            new Expression("concat(street.pref_short, ' ', street.name) as street_full"),
            new Expression("concat(city.pref_short, ' ', city.name) as city_name"),
        ])->from('owner')
            ->leftJoin('type_owner', 'type_owner.id = owner.type_owner_id')
            ->leftJoin('real_estate', 'real_estate.id = owner.real_estate_id')
            ->leftJoin('real_estate_type', 'real_estate_type.id = real_estate.real_estate_type_id')
            ->leftJoin('house', 'house.id = real_estate.house_id')
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
                'name',
                'type_owner_name',
                'real_estate_num',
                'house_num',
                'street_full',
                'city_name',
                'phone',
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

        $query->andFilterWhere(['IN', 'house.street_id', $this->street_ids]);
        $query->andFilterWhere(['IN', 'street.city_id', $this->city_ids]);
        $query->andFilterWhere(['=', 'owner.phone', $this->phone]);
        $query->andFilterWhere(['ilike', 'house.num', $this->house_num]);
        $query->andFilterWhere(['ilike', 'real_estate.num', $this->real_estate_num]);
        $query->andFilterWhere(['ilike', 'owner.name', $this->name]);
        $query->andFilterWhere(['in', 'owner.type_owner_id', $this->type_owner_ids]);
//        $dataProvider->pagination->pageSizeLimit = 100;

        return $dataProvider;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge($labels, [
            'street_full' => Yii::t('app', 'Улица'),
            'house_num' => Yii::t('app', 'Дом'),
            'real_estate_num' => Yii::t('app', 'Номер'),
            'city_name' => Yii::t('app', 'Город'),
            'type_owner_name' => Yii::t('app', 'Тип'),
        ]);

        return $labels;
    }
}