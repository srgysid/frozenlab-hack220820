<?php
namespace frontend\controllers;

use common\models\MeetingQuestion;
use common\models\Meeting;
use common\models\Question;
use common\models\Title;
use frontend\models\search\MeetingQuestionSearch;
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

class MeetingQuestionController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'clear-filter','question-data', 'question-list'],
                        'allow' => true,
                        'roles' => ['rl_admin', 'rl_key_user', 'rl_user'],
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
        $searchModel = new MeetingQuestionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$meeting_id);

        $modelMeeting = Meeting::findOne($meeting_id);
        $titles = ArrayHelper::map(Title::find()->orderBy('short_name')->all(), 'id', 'short_name');
        $questions = ArrayHelper::map(Question::find()->all(), 'id', 'short_name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'questions' => $questions,
            'modelMeeting' => $modelMeeting,
            'titles' => $titles,
        ]);
    }

    public function actionCreate($meeting_id)
    {
        $model = new MeetingQuestion();
        $model->meeting_id = $meeting_id;

        $titles = ArrayHelper::map(Title::find()->orderBy('short_name')->all(), 'id', 'short_name');
        $questions = ArrayHelper::map(Question::getQuestionByTitle($model->title_id), 'id', 'short_name');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $url = $this->getCurrentUrl();
            return $this->redirect($url);
        }
        $this->setCurrentUrl();

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
                'questions' => $questions,
                'titles' => $titles,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'questions' => $questions,
                'titles' => $titles,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $titles = ArrayHelper::map(Title::find()->orderBy('short_name')->all(), 'id', 'short_name');
        $questions = ArrayHelper::map(Question::getQuestionByTitle($model->title_id), 'id', 'short_name');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $url = $this->getCurrentUrl();
            return $this->redirect($url);
        }
        $this->setCurrentUrl();

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
                'questions' => $questions,
                'titles' => $titles,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'questions' => $questions,
                'titles' => $titles,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = MeetingQuestion::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Вопрос по Теме собрания не найден'));
        }

        return $model;
    }

    public function actionDelete($id)
    {
        $this->setCurrentUrl();
        $this->findModel($id)->delete();

        $url = $this->getCurrentUrl();
        return $this->redirect($url);
    }

    public function actionQuestionData()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $topic = null;
        $proposal = null;
        if (isset($_POST['question_id'])) {
            $question_id = $_POST['question_id'];
            if ($question_id != null) {
                $modelQuestion =  Question::findOne($question_id);
                if ($modelQuestion){
                    $topic = $modelQuestion->topic;
                    $proposal = $modelQuestion->proposal;
                }
            }
        }
        return ['topic' => $topic, 'proposal' => $proposal];
    }

    public function actionQuestionList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $title_id = $parents[0];
                if ($title_id) {
                    $out = Question::getQuestionByTitle($title_id);
                }
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionClearFilter($meeting_id)
    {
        $session = Yii::$app->session;
        if ($session->has('MeetingQuestionSearch')) {
            $session->remove('MeetingQuestionSearch');
        }
        if ($session->has('MeetingQuestionSearchSort')) {
            $session->remove('MeetingQuestionSearchSort');
        }

        return $this->redirect(['index','meeting_id'=>$meeting_id ]);
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('MeetingQuestionReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['MeetingQuestionReferrer'])
            return $session['MeetingQuestionReferrer'];
        return 'index';
    }

}