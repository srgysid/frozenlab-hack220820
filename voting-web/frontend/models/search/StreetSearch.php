<?php

namespace frontend\models\search;

use common\models\Street;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class StreetSearch extends Street
{
    public $street_pref_ids;
    public $city_ids;

    public function rules()
    {
        return [
            [['street_pref_ids', 'city_ids','name', 'city_name', 'kladr_guid'], 'safe'],
        ];
    }

    public function search($params)
    {
        $session = Yii::$app->session;

        if (!isset($params['StreetSearch'])) {
            if ($session->has('StreetSearch')){
                $params['StreetSearch'] = $session['StreetSearch'];
            }
        }
        else{
            $session->set('StreetSearch', $params['StreetSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('StreetSearchSort')){
                $params['sort'] = $session['StreetSearchSort'];
            }
        }
        else{
            $session->set('StreetSearchSort', $params['sort']);
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
            'street.id',
            'street.name',
            'street.fias_guid',
            'street.kladr_guid',
            'street.pref_name',
            'street.pref_short',
//            'city.name as city_name',
            new Expression("concat(city.pref_short, ' ', city.name) as city_name"),
        ])->from('street')
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
                'pref_short',
                'name',
                'kladr_guid',
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

        $query->andFilterWhere(['IN', 'street.city_id', $this->city_ids]);
        $query->andFilterWhere(['ilike', 'street.name', $this->name]);
        $query->andFilterWhere(['ilike', 'street.pref_short', $this->pref_short]);
        $query->andFilterWhere(['ilike', 'street.kladr_guid', $this->kladr_guid]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge($labels, [
            'city_name' => Yii::t('app', 'Город'),
        ]);

        return $labels;
    }

}