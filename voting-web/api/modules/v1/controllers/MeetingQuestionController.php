<?php
namespace api\modules\v1\controllers;

use api\controllers\BaseApiController;
use api\modules\v1\models\search\MeetingQuestionSearch;
use common\models\MeetingQuestion;
use Yii;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class MeetingQuestionController extends BaseApiController
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

    public function actionIndex($meeting_id)
    {
        $searchModel = new MeetingQuestionSearch();
        $dataProvider = $searchModel->searchByMeeting(Yii::$app->request->queryParams, $meeting_id);

        return $dataProvider;
    }

}