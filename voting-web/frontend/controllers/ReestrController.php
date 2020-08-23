<?php
namespace frontend\controllers;

use common\models\Reestr;
use common\models\House;
use common\models\ReestrDetail;
use common\models\ReestrMeeting;
use frontend\models\search\ReestrSearch;
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
use yii\web\UploadedFile;


class ReestrController extends Controller
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
                        'actions' => ['index', 'create', 'delete', 'clear-filter'],
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

    public function actionIndex($house_id)
    {
        $searchModel = new ReestrSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$house_id);

        $modelHouse = House::findOne($house_id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelHouse' => $modelHouse,
        ]);
    }

    public function actionCreate($house_id)
    {
        $model = new Reestr();
        $model->house_id = $house_id;

        if ($model->saveData()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Реестр создан'));
        }
        else Yii::$app->session->addFlash('error', Yii::t('app', 'Ошибка создания реестра'));

        return $this->redirect(['index','house_id' => $house_id ]);
    }

    private function findModel($id)
    {
        $model = Reestr::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Реестр не найден'));
        }

        return $model;
    }

    public function actionDelete($id)
    {
        $cntReestrMeeting = ReestrMeeting::checkCountReestrMeeting($id);

        $this->setCurrentUrl();

        if (!$cntReestrMeeting){
            ReestrDetail::deleteAll(['reestr_id' =>$id]);
            $this->findModel($id)->delete();
            $url = $this->getCurrentUrl();
            return $this->redirect($url);
        }
        else {
            $url = $this->getCurrentUrl();
            Yii::$app->session->addFlash('error',Yii::t('app', 'Удаление невозможно! Реестр используется.'));
            return $this->redirect($url);
        }
    }
    public function actionClearFilter($house_id)
    {
        $session = Yii::$app->session;
        if ($session->has('ReestrSearch')) {
            $session->remove('ReestrSearch');
        }
        if ($session->has('ReestrSearchSort')) {
            $session->remove('ReestrSearchSort');
        }

        return $this->redirect(['index','house_id' => $house_id ]);
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('ReestrReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['ReestrReferrer'])
            return $session['ReestrReferrer'];
        return 'index';
    }

}