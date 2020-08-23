<?php
namespace frontend\controllers;

use common\models\MeetingQuestion;
use common\models\VoterMeetingQuestion;
use common\models\Meeting;
use common\models\MeetingVoter;
use common\models\TypeOwner;
use frontend\models\search\MeetingVoterSearch;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Controller;
use yii\bootstrap4\ActiveForm;

class MeetingVoterController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'clear-filter'],
                        'allow' => true,
                        'roles' => ['rl_admin', 'rl_key_user', 'rl_user'],
                    ],
                    [
                        'actions' => ['vote'],
                        'allow' => true,
                        'roles' => ['rl_admin', 'rl_key_user'],
                    ]

                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex($meeting_id)
    {
        $searchModel = new MeetingVoterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$meeting_id);
        $modelMeeting = Meeting::findOne($meeting_id);

        $type_owner = ArrayHelper::map(TypeOwner::find()->orderBy('name')->all(), 'id', 'name');
        $source = MeetingVoter::getSourceList();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelMeeting' => $modelMeeting,
            'type_owner' => $type_owner,
            'source' => $source,
        ]);
    }

    public function actionVote($id)
    {
        $model = $this->findModel($id);

        if ($model->vote_source == null) {
            $model->vote_source = MeetingVoter::SOURCE_OPERATOR;

            $modelsMeetingQuestion = MeetingQuestion::find()->where(['meeting_id' => $model->meeting_id])->all();

            if ($modelsMeetingQuestion) {

                $choiceList = MeetingVoter::getChoiceList();
                if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($model);
                }

                if ($model->load(Yii::$app->request->post())) {
                    if ($model->saveMeetingVoter()){
                        $url = $this->getCurrentUrl();
                        Yii::$app->session->addFlash('success', Yii::t('app', 'Мнение собственника: '.$model->name.' зарегистрированно.'));
                        return $this->redirect($url);
                    }
                    else {
                        Yii::$app->session->addFlash('error', Yii::t('app', 'Ошибка при сохранении голосования'));
                    }
                }

                $this->setCurrentUrl();

                if (!$modelsMeetingQuestion){
                    $url = $this->getCurrentUrl();
                    Yii::$app->session->addFlash('error',Yii::t('app', 'Вопросы для собрания не найдены.'));
                    return $this->redirect($url);
                }

                foreach ($modelsMeetingQuestion as $meetingQuestion) {
                    $model->arrValue[$meetingQuestion->order_num]['order_num'] = $meetingQuestion->order_num;
                    $model->arrValue[$meetingQuestion->order_num]['topic'] = $meetingQuestion->topic;
                    $model->arrValue[$meetingQuestion->order_num]['proposal'] = $meetingQuestion->proposal;
                    $model->arrValue[$meetingQuestion->order_num]['choice'] = MeetingVoter::CHOICE_ABSTAINED;
                }

                if (Yii::$app->request->isAjax){
                    return $this->renderAjax('_form', [
                        'model' => $model,
                        'modelsMeetingQuestion' =>$modelsMeetingQuestion,
                        'choiceList' => $choiceList
                    ]);
                }
                else{
                    return $this->render('_form', [
                        'model' => $model,
                        'modelsMeetingQuestion' =>$modelsMeetingQuestion,
                        'choiceList' => $choiceList
                    ]);
                }
            }
            else {
                $this->setCurrentUrl();

                $url = $this->getCurrentUrl();
                Yii::$app->session->addFlash('error',Yii::t('app', 'Не определены вопросы для голосования'));
                return $this->redirect($url);
            }
        }
        else {
            $this->setCurrentUrl();

            $url = $this->getCurrentUrl();
            Yii::$app->session->addFlash('error',Yii::t('app', 'Собственник '.$model->name.' уже проголосовал'));
            return $this->redirect($url);
        }
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        $modelsMeetingQuestion = MeetingQuestion::find()->where(['meeting_id' => $model->meeting_id])->all();
        $choiceList = MeetingVoter::getChoiceList();

        foreach ($modelsMeetingQuestion as $meetingQuestion) {
            $modelVoterMeetingQuestion = VoterMeetingQuestion::find()->where(['meeting_voter_id' => $model->id, 'meeting_question_id'=> $meetingQuestion->id])->one();

            $model->arrValue[$meetingQuestion->order_num]['order_num'] = $meetingQuestion->order_num;
            $model->arrValue[$meetingQuestion->order_num]['topic'] = $meetingQuestion->topic;
            $model->arrValue[$meetingQuestion->order_num]['proposal'] = $meetingQuestion->proposal;
            $model->arrValue[$meetingQuestion->order_num]['choice'] = $modelVoterMeetingQuestion->choice;
        }

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('view', [
                'model' => $model,
                'modelsMeetingQuestion' =>$modelsMeetingQuestion,
                'choiceList' => $choiceList
            ]);
        }
        else{
            return $this->render('view', [
                'model' => $model,
                'modelsMeetingQuestion' =>$modelsMeetingQuestion,
                'choiceList' => $choiceList
            ]);
        }
    }

    private function findModel($id)
    {
        $model = MeetingVoter::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Реестр не найден'));
        }

        return $model;
    }

    public function actionClearFilter($meeting_id)
    {
        $session = Yii::$app->session;
        if ($session->has('MeetingVoterSearch')) {
            $session->remove('MeetingVoterSearch');
        }
        if ($session->has('MeetingVoterSearchSort')) {
            $session->remove('MeetingVoterSearchSort');
        }

        return $this->redirect(['index','meeting_id' => $meeting_id ]);
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('MeetingVoterReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['MeetingVoterReferrer'])
            return $session['MeetingVoterReferrer'];
        return 'index';
    }

}