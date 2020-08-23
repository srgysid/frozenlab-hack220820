<?php

namespace frontend\models\search;

use common\models\RealEstate;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class RealEstateSearch extends RealEstate
{
    public $city_ids;
    public $street_ids;
    public $house_ids;
    public $real_estate_type_ids;

    public $street_name;
    public $house_num;
    public $int_real_estate_num;
    public $real_estate_type_short_name;

    public function rules()
    {
        return [
            [['city_ids', 'street_ids', 'house_ids', 'real_estate_type_ids'], 'safe'],
            [['street_name', 'real_estate_type_short_name', 'num', 'house_num'], 'string'],
            [['int_real_estate_num'], 'integer'],
        ];
    }

    public function search($params)
    {
        $session = Yii::$app->session;

        if (!isset($params['RealEstateSearch'])) {
            if ($session->has('RealEstateSearch')){
                $params['RealEstateSearch'] = $session['RealEstateSearch'];
            }
        }
        else{
            $session->set('RealEstateSearch', $params['RealEstateSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('RealEstateSearchSort')){
                $params['sort'] = $session['RealEstateSearchSort'];
            }
        }
        else{
            $session->set('RealEstateSearchSort', $params['sort']);
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
            $fieldSort = 'street_name';
        }

        $query = new Query();
        $query->addSelect([
            'real_estate.id',
            'real_estate.num',
            new Expression("NULLIF(regexp_replace(real_estate.num, '\D','','g'), '')::int AS int_real_estate_num"),
            'real_estate_type.name as real_estate_type_name',
            'real_estate_type.short_name as real_estate_type_short_name',
            'house.num as house_num',
            'street.name as street_name',
            new Expression("concat(street.pref_short, ' ', street.name) as street_full"),
            new Expression("concat(city.pref_short, ' ', city.name) as city_name"),
        ])->from('real_estate')
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
                'num',
                'house_num',
                'street_name',
                'int_real_estate_num',
                'real_estate_type_short_name',
                'city_name',
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

        $query->andFilterWhere(['ilike', 'real_estate.num', $this->num]);
        $query->andFilterWhere(['ilike', 'house.num', $this->house_num]);
        $query->andFilterWhere(['IN', 'house.street_id', $this->street_ids]);
        $query->andFilterWhere(['IN', 'street.city_id', $this->city_ids]);
        $query->andFilterWhere(['IN', 'real_estate.real_estate_type_id', $this->real_estate_type_ids]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge($labels, [
            'house_num' => Yii::t('app', 'Дом'),
            'int_real_estate_num' => Yii::t('app', 'Номер'),
            'street_name' => Yii::t('app', 'Название улицы'),
            'real_estate_type_short_name' => Yii::t('app', 'Тип помещения'),
            'city_name' => Yii::t('app', 'Город'),
        ]);

        return $labels;
    }

}