<?php
namespace api\modules\v1\controllers;

use api\controllers\BaseApiController;
use common\models\MeetingQuestion;
use common\models\MeetingVoter;
use common\models\Owner;
use common\models\VoterMeetingQuestion;
use api\modules\v1\models\search\MeetingVoterSearch;
use Yii;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;


class MeetingVoterController extends BaseApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors = ArrayHelper::merge($behaviors, [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['POST'],
                    'view-vote' => ['GET'],
                ],
            ],
        ]);

        return $behaviors;
    }

    public function actionIndex($meeting_id)
    {
        $user = Yii::$app->user;
        $profile = $user->identity->userProfile;
        $request = Yii::$app->request;
        $countErrors = 0;
        $descError = '';

        $modelOwners = Owner::find()->where(['phone' => $profile->phone])->all();

        if ($modelOwners) {
            foreach ($modelOwners as $owner){
                $modelsMeetingVoter = MeetingVoter::find()->where(['meeting_id'=>$meeting_id, 'ownership'=> $owner->ownership])->all();
                if ($modelsMeetingVoter) {
                    foreach ($modelsMeetingVoter as $meetingVoter){
                        $model = MeetingVoter::findOne($meetingVoter->id);
                        if ($model){
                            if ($model->vote_source == null){
                                $answers_array = $request->post();
                                if ($answers_array) {
                                    foreach ($answers_array as $answerRow){
                                        $modelVoterMeetingQuestion = new VoterMeetingQuestion();

                                        $modelVoterMeetingQuestion->meeting_voter_id = $model->id;
                                        $modelVoterMeetingQuestion->meeting_question_id = $answerRow['question_id'];
                                        $modelVoterMeetingQuestion->choice = $answerRow['choice'];

                                        if (!$modelVoterMeetingQuestion->save()) {
                                            $model->addErrors($modelVoterMeetingQuestion->errors);
                                        }
                                    }
                                }
                                if (count($model->errors) == 0){
                                    $model->vote_source = MeetingVoter::SOURCE_MOBILE;
                                    $model->save();
                                }else $countErrors++;
                            }
                            else $countErrors++;
                        }
                    }
                }
            }
        }

        return $countErrors;
    }

    public function actionViewVote($meeting_id)
    {
        $user = Yii::$app->user;
        $profile = $user->identity->userProfile;

        $searchModel = new MeetingVoterSearch();
        $dataProvider = $searchModel->searchByMeetingVoter(Yii::$app->request->queryParams, $meeting_id, $profile->phone);

        return $dataProvider;
    }

}