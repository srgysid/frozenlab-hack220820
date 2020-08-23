<?php

namespace frontend\models\search;

use common\models\ReestrDetail;
use common\models\ActiveDataProviderVotes;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ReestrDetailSearch extends ReestrDetail
{

    public $type_owner_ids;

    public function rules()
    {
        return [
            [['name', 'num', 'type_real_estate', 'ownership'], 'string'],
//            [['area'], 'double'],
            [['phone'], 'integer'],
            [['type_owner_ids'], 'safe'],
        ];
    }

    public function search($params, $reestr_id)
    {
        $session = Yii::$app->session;

        if (!isset($params['ReestrDetailSearch'])) {
            if ($session->has('ReestrDetailSearch')){
                $params['ReestrDetailSearch'] = $session['ReestrDetailSearch'];
            }
        }
        else{
            $session->set('ReestrDetailSearch', $params['ReestrDetailSearch']);
        }

        if (!isset($params['sort'])) {
            if ($session->has('ReestrDetailSearchSort')){
                $params['sort'] = $session['ReestrDetailSearchSort'];
            }
        }
        else{
            $session->set('ReestrDetailSearchSort', $params['sort']);
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
            $fieldSort = 'num';
        }

        $query = new Query();
        $query->addSelect([
            'reestr_detail.id as id',
            'reestr_detail.num as num',
            'reestr_detail.name as name',
            'reestr_detail.area as area',
            'reestr_detail.part as part',
            'reestr_detail.ownership as ownership',
            'reestr_detail.type_real_estate as type_real_estate',
            'reestr_detail.phone as phone',
            'type_owner.name as type_owner_name',
        ])->from('reestr_detail')
            ->leftJoin('type_owner', 'type_owner.id = reestr_detail.type_owner_id')
            ->where(['reestr_detail.reestr_id' => $reestr_id])
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
                'name',
                'phone',
                'area',
                'type_real_estate',

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

        $query->andFilterWhere(['ilike', 'reestr_detail.num', $this->num]);
        $query->andFilterWhere(['ilike', 'reestr_detail.name', $this->name]);
        $query->andFilterWhere(['ilike', 'reestr_detail.ownership', $this->ownership]);
        $query->andFilterWhere(['in', 'reestr_detail.type_owner_id', $this->type_owner_ids]);
        $query->andFilterWhere(['=', 'reestr_detail.phone', $this->phone]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge($labels, [
            'type_owner_name' => Yii::t('app', 'Тип собственника'),
        ]);

        return $labels;
    }

}