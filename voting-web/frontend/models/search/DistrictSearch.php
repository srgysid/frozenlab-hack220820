<?php

namespace frontend\models\search;

use common\models\District;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DistrictSearch extends District
{

    public $region_ids;

    public function rules()
    {
        return [
            [['name', 'region_ids','kladr_guid','pref_name'], 'safe'],
        ];
    }

    public function search($params)
    {

        $session = Yii::$app->session;

        if (!isset($params['DistrictSearch'])) {
            if ($session->has('DistrictSearch')){
                $params['DistrictSearch'] = $session['DistrictSearch'];
            }
        }
        else{
            $session->set('DistrictSearch', $params['DistrictSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('DistrictSearchSort')){
                $params['sort'] = $session['DistrictSearchSort'];
            }
        }
        else{
            $session->set('DistrictSearchSort', $params['sort']);
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
            'district.id',
            'district.name',
            'district.pref_name',
            'district.pref_short',
            'district.region_id',
            'district.fias_guid',
            'district.kladr_guid',
            'region.name as region_name',
            'region.pref_name as region_pref_name'
        ])->from('district')
            ->leftJoin('region', 'region.id = district.region_id')
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

        $query->andFilterWhere(['ilike', 'district.name', $this->name]);
        $query->andFilterWhere(['ilike', 'district.pref_name', $this->pref_name]);
        $query->andFilterWhere(['ilike', 'district.kladr_guid', $this->kladr_guid]);
        $query->andFilterWhere(['IN', 'district.region_id', $this->region_ids]);

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