<?php
namespace api\modules\v1\controllers;

use api\controllers\BaseApiController;
use api\modules\v1\models\search\MeetingSearch;
use common\models\Meeting;
use Yii;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class MeetingController extends BaseApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors = ArrayHelper::merge($behaviors, [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['GET'],
                    'view' => ['GET'],
                ],
            ],
        ]);

        return $behaviors;
    }

    public function actionIndex()
    {
        $post = Yii::$app->request->getBodyParams();
        $searchModel = new MeetingSearch();
        $dataProvider = $searchModel->searchByHouse(Yii::$app->request->queryParams, $post['house_id']);

        return $dataProvider;
    }

    public function actionView($house_id)
    {
        $searchModel = new MeetingSearch();
        $dataProvider = $searchModel->searchByHouse(Yii::$app->request->queryParams, $house_id);

        return $dataProvider;
    }

}