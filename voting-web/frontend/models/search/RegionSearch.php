<?php

namespace frontend\models\search;

use common\models\Region;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class RegionSearch extends Region
{
    public function rules()
    {
        return [
            [['name','pref_name','kladr_guid'], 'safe'],
        ];
    }

    public function search($params)
    {
        $session = Yii::$app->session;

        if (!isset($params['RegionSearch'])) {
            if ($session->has('RegionSearch')){
                $params['RegionSearch'] = $session['RegionSearch'];
            }
        }
        else{
            $session->set('RegionSearch', $params['RegionSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('RegionSearchSort')){
                $params['sort'] = $session['RegionSearchSort'];
            }
        }
        else{
            $session->set('RegionSearchSort', $params['sort']);
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
            'region.id as id',
            'region.name as name',
            'region.fias_guid as fias_guid',
            'region.kladr_guid as kladr_guid',
            'region.pref_name as pref_name',
        ])->from('region')
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
                'pref_name',
                'kladr_guid'
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

        $query->andFilterWhere(['ilike', 'region.name', $this->name]);
        $query->andFilterWhere(['ilike', 'region.pref_name', $this->pref_name]);
        $query->andFilterWhere(['ilike', 'region.kladr_guid', $this->kladr_guid]);

        return $dataProvider;
    }

}