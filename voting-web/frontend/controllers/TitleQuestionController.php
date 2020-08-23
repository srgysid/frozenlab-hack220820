<?php
namespace frontend\controllers;

use common\models\Question;
use common\models\Title;
use common\models\TitleQuestion;
use frontend\models\search\TitleQuestionSearch;
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

class TitleQuestionController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'clear-filter'],
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

    public function actionIndex()
    {
        $searchModel = new TitleQuestionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $titles = ArrayHelper::map(Title::find()->orderBy('id')->all(), 'id', 'short_name');
        $questions = ArrayHelper::map(Question::find()->orderBy('id')->all(), 'id', 'short_name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'titles' => $titles,
            'questions' => $questions,
        ]);
    }

    public function actionCreate()
    {
        $model = new TitleQuestion();

        $titles = ArrayHelper::map(Title::find()->orderBy('id')->all(), 'id', 'short_name');
        $questions = ArrayHelper::map(Question::find()->orderBy('id')->all(), 'id', 'short_name');

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
                'titles' => $titles,
                'questions' => $questions,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'titles' => $titles,
                'questions' => $questions,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $titles = ArrayHelper::map(Title::find()->orderBy('id')->all(), 'id', 'short_name');
        $questions = ArrayHelper::map(Question::find()->orderBy('id')->all(), 'id', 'short_name');

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
                'titles' => $titles,
                'questions' => $questions,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'titles' => $titles,
                'questions' => $questions,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = TitleQuestion::findOne($id);
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

    public function actionClearFilter()
    {
        $session = Yii::$app->session;
        if ($session->has('TitleQuestionSearch')) {
            $session->remove('TitleQuestionSearch');
        }
        if ($session->has('TitleQuestionSearchSort')) {
            $session->remove('TitleQuestionSearchSort');
        }

        return $this->redirect('index');
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('TitleQuestionReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['TitleQuestionReferrer'])
            return $session['TitleQuestionReferrer'];
        return 'index';
    }

}