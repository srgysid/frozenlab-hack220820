<?php
namespace api\modules\v1\controllers;

use api\controllers\BaseApiController;
use api\modules\v1\models\search\OwnerSearch;
use common\models\Meeting;
use Yii;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class OwnerController extends BaseApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors = ArrayHelper::merge($behaviors, [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['GET'],
                ],
            ],
        ]);

        return $behaviors;
    }

    public function actionIndex()
    {
        $user = Yii::$app->user;
        $profile = $user->identity->userProfile;

        $searchModel = new OwnerSearch();
        $dataProvider = $searchModel->searchByPhone(Yii::$app->request->queryParams, $profile->phone);

        return $dataProvider;
    }

}