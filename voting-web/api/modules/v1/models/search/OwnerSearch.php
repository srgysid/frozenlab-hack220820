<?php

namespace api\modules\v1\models\search;

use common\models\Owner;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\Expression;

class OwnerSearch extends Owner
{
    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['house_id'], 'safe'],
        ];
    }

    public function searchByPhone($params, $phone)
    {
        $query = new Query();
        $query->addSelect([
            'house.id as house_id',
            'owner.id',
            'owner.name',
            'type_owner.name as type_owner_name',
            'owner.address',
            'owner.ownership',
            'owner.percent_own',
            'real_estate.area as real_estate_area',
            'real_estate_type.short_name as real_estate_type_short_name',
            'real_estate.num as real_estate_num',
            'house.num as house_num',
            'street.name as street_name',
            new Expression("concat(street.pref_short, ' ', street.name) as street_full"),
            'city.name as city_name',
            new Expression("concat(city.pref_short, ' ', city.name) as city_full"),
        ])->from('owner')
            ->leftJoin('type_owner', 'type_owner.id = owner.type_owner_id')
            ->leftJoin('real_estate', 'real_estate.id = owner.real_estate_id')
            ->leftJoin('real_estate_type', 'real_estate_type.id = real_estate.real_estate_type_id')
            ->leftJoin('house', 'house.id = real_estate.house_id')
            ->leftJoin('street', 'street.id = house.street_id')
            ->leftJoin('city', 'city.id = street.city_id')
            ->where(['=', 'owner.phone', $phone])
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->key = 'id';

        $dataProvider->setSort([
            'defaultOrder' => ['house_id' => SORT_ASC],
            'attributes' => [
                'house_id',
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