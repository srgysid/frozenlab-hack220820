<?php

namespace frontend\models\search;

use common\models\Reestr;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ReestrSearch extends Reestr
{

    public function rules()
    {
        return [];
    }

    public function search($params, $house_id)
    {
        $session = Yii::$app->session;

        if (!isset($params['ReestrSearch'])) {
            if ($session->has('ReestrSearch')){
                $params['ReestrSearch'] = $session['ReestrSearch'];
            }
        }
        else{
            $session->set('ReestrSearch', $params['ReestrSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('ReestrSearchSort')){
                $params['sort'] = $session['ReestrSearchSort'];
            }
        }
        else{
            $session->set('ReestrSearchSort', $params['sort']);
        }

        if (isset($params["sort"])) {
            $pos = stripos($params["sort"], '-');
            if ($pos !== false) {
                $typeSort = SORT_ASC;
                $fieldSort = substr($params["sort"], 1);
            } else {
                $typeSort = SORT_DESC;
                $fieldSort = $params["sort"];
            }
        }
        else {
            $typeSort = SORT_DESC;
            $fieldSort = 'created_at';
        }

        $query = new Query();
        $query->addSelect([
            'reestr.id as id',
            'reestr.reg_num as reg_num',
            'reestr.created_at as created_at',
            'reestr.house_id as house_id',
        ])->from('reestr')
            ->where(['reestr.house_id' => $house_id])
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
                'reg_num',
                'created_at',
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

        return $dataProvider;
    }

}