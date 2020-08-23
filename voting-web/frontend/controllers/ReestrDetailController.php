<?php
namespace frontend\controllers;

use common\models\Reestr;
use common\models\ReestrMeeting;
use common\models\Meeting;
use common\models\MeetingVoter;
use common\models\TypeOwner;
use common\models\ReestrDetail;
use frontend\models\search\ReestrDetailSearch;
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

class ReestrDetailController extends Controller
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
                        'actions' => ['index', 'view', 'reestr-meeting', 'clear-filter'],
                        'allow' => true,
                        'roles' => ['rl_admin', 'rl_key_user', 'rl_user'],
                    ],
                    [
                        'actions' => ['create', 'delete', 'update','reestr-meeting-update'],
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

    public function actionIndex($reestr_id)
    {
        $searchModel = new ReestrDetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$reestr_id);

        $modelReestr = Reestr::findOne($reestr_id);
        $type_owner = ArrayHelper::map(TypeOwner::find()->orderBy('name')->all(), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelReestr' => $modelReestr,
            'type_owner' => $type_owner,
        ]);
    }

    public function actionReestrMeeting($meeting_id)
    {
        $modelMeeting = Meeting::findOne($meeting_id);
        $modelReestrMeeting = ReestrMeeting::find()->where(['meeting_id'=>$meeting_id])->one();
        if (isset($modelReestrMeeting->reestr_id)) {
            $reestr_id = $modelReestrMeeting->reestr_id;
        }
        else $reestr_id = 0;
        $searchModel = new ReestrDetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $reestr_id);

        $modelReestr = Reestr::findOne($reestr_id);
        $type_owner = ArrayHelper::map(TypeOwner::find()->orderBy('name')->all(), 'id', 'name');

        return $this->render('reestr_meeting_rows', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelReestr' => $modelReestr,
            'modelMeeting' => $modelMeeting,
            'type_owner' => $type_owner,
        ]);
    }

    public function actionReestrMeetingUpdate($meeting_id)
    {
        $modelMeeting = Meeting::findOne($meeting_id);
        $reestr_id = Reestr::maxReestrHouse($modelMeeting->house_id);

        if ($reestr_id) {
            ReestrMeeting::deleteAll(['meeting_id'=>$modelMeeting->id]);
            $modelReestrMeeting = new ReestrMeeting();
            $modelReestrMeeting->meeting_id = $meeting_id;
            $modelReestrMeeting->reestr_id = $reestr_id;
            $modelReestrMeeting->save();

            MeetingVoter::deleteAll(['meeting_id'=>$modelMeeting->id]);
            $modelsReestrDetail = ReestrDetail::find()->where(['reestr_id'=>$reestr_id])->all();
            if ($modelsReestrDetail) {
                foreach ($modelsReestrDetail as $reestrDetail){
                    $modelMeetingVoter = new MeetingVoter();
                    $modelMeetingVoter->meeting_id = $meeting_id;
                    $modelMeetingVoter->num = $reestrDetail->num;
                    $modelMeetingVoter->name = $reestrDetail->name;
                    $modelMeetingVoter->area = $reestrDetail->area;
                    $modelMeetingVoter->type_real_estate = $reestrDetail->type_real_estate;
                    $modelMeetingVoter->type_owner_id = $reestrDetail->type_owner_id;
                    $modelMeetingVoter->part = $reestrDetail->part;
                    $modelMeetingVoter->ownership = $reestrDetail->ownership;
                    $modelMeetingVoter->email = $reestrDetail->email;
                    $modelMeetingVoter->phone = $reestrDetail->phone;

                    $modelMeetingVoter->save();
                }
            }

        }
        return $this->redirect(['reestr-meeting','meeting_id' => $modelMeeting->id]);
    }

    public function actionCreate($reestr_id)
    {
        $model = new ReestrDetail();
        $model->reestr_id = $reestr_id;
        $type_owner = ArrayHelper::map(TypeOwner::find()->orderBy('name')->all(), 'id', 'name');

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
                'type_owner' => $type_owner,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'type_owner' => $type_owner,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $type_owner = ArrayHelper::map(TypeOwner::find()->orderBy('name')->all(), 'id', 'name');

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
                'type_owner' => $type_owner,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'type_owner' => $type_owner,
            ]);
        }
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->isAjax){
            return $this->renderAjax('view', [
                'model' => $model,
            ]);
        }
        else{
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = ReestrDetail::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Реестр не найден'));
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

    public function actionClearFilter($reestr_id)
    {
        $session = Yii::$app->session;
        if ($session->has('ReestrDetailSearch')) {
            $session->remove('ReestrDetailSearch');
        }
        if ($session->has('ReestrDetailSearchSort')) {
            $session->remove('ReestrDetailSearchSort');
        }
        return $this->redirect(['index','reestr_id' => $reestr_id ]);
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('ReestrDetailReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['ReestrDetailReferrer'])
            return $session['ReestrDetailReferrer'];
        return 'index';
    }

}