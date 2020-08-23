<?php

namespace frontend\models\search;

use common\models\Title;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class TitleSearch extends Title
{
    public function rules()
    {
        return [
            [['name','short_name'], 'safe'],
        ];
    }

    public function search($params)
    {
        $session = Yii::$app->session;

        if (!isset($params['TitleSearch'])) {
            if ($session->has('TitleSearch')){
                $params['TitleSearch'] = $session['TitleSearch'];
            }
        }
        else{
            $session->set('TitleSearch', $params['TitleSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('TitleSearchSort')){
                $params['sort'] = $session['TitleSearchSort'];
            }
        }
        else{
            $session->set('TitleSearchSort', $params['sort']);
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
            $fieldSort = 'short_name';
        }

        $query = new Query();
        $query->addSelect([
            'title.id as id',
            'title.name as name',
            'title.short_name as short_name',
        ])->from('title')
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
                'short_name',
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

        $query->andFilterWhere(['ilike', 'title.name', $this->name]);
        $query->andFilterWhere(['ilike', 'title.short_name', $this->short_name]);

        return $dataProvider;
    }

}