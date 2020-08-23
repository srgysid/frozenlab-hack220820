<?php

namespace frontend\models\search;

use common\models\House;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class HouseSearch extends House
{
    public $street_ids;
    public $street_full;
    public $city_ids;
    public $int_house_num;

    public function rules()
    {
        return [
            [['street_ids', 'city_ids','num','kladr_guid'], 'safe'],
            [['street_full'], 'string'],
            [['int_house_num', 'area'], 'integer'],
        ];
    }

    public function search($params)
    {
        $session = Yii::$app->session;

        if (!isset($params['HouseSearch'])) {
            if ($session->has('HouseSearch')){
                $params['HouseSearch'] = $session['HouseSearch'];
            }
        }
        else{
            $session->set('HouseSearch', $params['HouseSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('HouseSearchSort')){
                $params['sort'] = $session['HouseSearchSort'];
            }
        }
        else{
            $session->set('HouseSearchSort', $params['sort']);
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
            'house.id',
            'house.num',
            'house.fias_guid',
            'house.kladr_guid',
            'house.area',
            new Expression("NULLIF(regexp_replace(house.num, '\D','','g'), '')::int AS int_house_num"),
            'street.name as street_name',
            'street.pref_short as street_pref_short',
            new Expression("concat(street.pref_short, ' ', street.name) as street_full"),
            new Expression("concat(city.pref_short, ' ', city.name) as city_name"),
        ])->from('house')
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
                'kladr_guid',
                'num',
                'int_house_num',
                'street_name',
                'area',
                'city_name'
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
        $query->andFilterWhere(['ilike', 'house.num', $this->num]);
        $query->andFilterWhere(['ilike', 'house.kladr_guid', $this->kladr_guid]);
//        $query->andFilterWhere(['=', new Expression("NULLIF(regexp_replace(house.num, '\D','','g'), '')::int"), $this->int_house_num]);
        $query->andFilterWhere(['=', 'house.area', $this->area]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge($labels, [
            'int_house_num' => Yii::t('app', 'Дом'),
            'street_name' => Yii::t('app', 'Название улицы'),
            'city_name' => Yii::t('app', 'Город'),
        ]);

        return $labels;
    }

}