<?php

namespace frontend\models\search;

use common\models\Company;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CompanySearch extends Company
{
    public function rules()
    {
        return [
            [['name', 'inn'], 'safe'],
        ];
    }

    public function search($params)
    {
        $session = Yii::$app->session;

        if (!isset($params['CompanySearch'])) {
            if ($session->has('CompanySearch')){
                $params['CompanySearch'] = $session['CompanySearch'];
            }
        }
        else{
            $session->set('CompanySearch', $params['CompanySearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('CompanySearchSort')){
                $params['sort'] = $session['CompanySearchSort'];
            }
        }
        else{
            $session->set('CompanySearchSort', $params['sort']);
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
            'company.id as id',
            'company.name as name',
            'company.inn as inn',
        ])->from('company')
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
                'inn'
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

        $query->andFilterWhere(['ilike', 'company.name', $this->name]);
        $query->andFilterWhere(['=', 'company.inn', $this->inn]);

        return $dataProvider;
    }

//    public function attributeLabels()
//    {
//        $labels = parent::attributeLabels();
//        $labels = ArrayHelper::merge($labels, [
//            'company_name' => Yii::t('app', 'Наименование компании'),
//        ]);
//
//        return $labels;
//    }

}