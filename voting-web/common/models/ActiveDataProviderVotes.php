<?php

namespace common\models;

use yii\data\ActiveDataProvider;


class ActiveDataProviderVotes extends ActiveDataProvider
{
    public function init(){
        $this->pagination->pageSizeLimit = 500;
        parent::init();
    }
}
