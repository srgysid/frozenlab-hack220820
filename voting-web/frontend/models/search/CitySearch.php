<?php

namespace frontend\models\search;

use common\models\City;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CitySearch extends City
{

    public $region_ids;

    public function rules()
    {
        return [
            [['name','region_ids','kladr_guid','pref_name'], 'safe'],
        ];
    }

    public function search($params)
    {

        $session = Yii::$app->session;

        if (!isset($params['CitySearch'])) {
            if ($session->has('CitySearch')){
                $params['CitySearch'] = $session['CitySearch'];
            }
        }
        else{
            $session->set('CitySearch', $params['CitySearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('CitySearchSort')){
                $params['sort'] = $session['CitySearchSort'];
            }
        }
        else{
            $session->set('CitySearchSort', $params['sort']);
        }

        if (isset($params['sort'])) {
            $pos = stripos($params['sort'], '-');
            if ($pos !== false) {
                $typeSort = SORT_DESC;
                $fieldSort = substr($params['sort'], 1);
            } else {
                $typeSort = SORT_ASC;
                $fieldSort = $params['sort'];
            }
        }
        else {
            $typeSort = SORT_ASC;
            $fieldSort = 'name';
        }

        $query = new Query();
        $query->addSelect([
            'city.id',
            'city.name',
            'city.pref_name',
            'city.pref_short',
            'city.region_id',
            'city.fias_guid',
            'city.kladr_guid',
            'region.name as region_name',
            'region.pref_name as region_pref_name'
        ])->from('city')
            ->leftJoin('region', 'region.id = city.region_id')
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
                'kladr_guid',
                'region_name',
                'pref_name',
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

        $query->andFilterWhere(['ilike', 'city.name', $this->name]);
        $query->andFilterWhere(['ilike', 'city.pref_name', $this->pref_name]);
        $query->andFilterWhere(['ilike', 'city.kladr_guid', $this->kladr_guid]);
        $query->andFilterWhere(['IN', 'city.region_id', $this->region_ids]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge($labels, [
            'region_name' => Yii::t('app', 'Наименование региона'),
        ]);

        return $labels;
    }

}