<?php
namespace frontend\controllers;

use frontend\models\search\DistrictSearch;
use common\models\District;
use common\models\Region;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\bootstrap4\ActiveForm;

class DistrictController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'clear-filter', 'region-by-id'],
                        'allow' => true,
                        'roles' => ['rl_admin','rl_key_user'],
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
        $searchModel = new DistrictSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $regions = ArrayHelper::map(Region::getFullRegion(), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'regions' => $regions,
        ]);
    }

    public function actionCreate()
    {
        $model = new District();
        $regions = ArrayHelper::map(Region::getFullRegion(), 'id', 'name');

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
                'regions' => $regions,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'regions' => $regions,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $regions = ArrayHelper::map(Region::getFullRegion(), 'id', 'name');
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
                'regions' => $regions,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'regions' => $regions,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = District::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Район не найден'));
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
        if ($session->has('DistrictSearch')) {
            $session->remove('DistrictSearch');
        }
        if ($session->has('DistrictSearchSort')) {
            $session->remove('DistrictSearchSort');
        }

        return $this->redirect('index');
    }

    public function actionRegionById()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $fias_guid = null;
        $kladr_guid = null;
        if (isset($_POST['region_id'])) {
            $region = Region::findOne($_POST['region_id']);
            if ($region) {
                $fias_guid = $region->fias_guid;
                $kladr_guid = $region->kladr_guid;
                return ['fias_guid' => $fias_guid, 'kladr_guid' => $kladr_guid];
            }
        }
        return false;
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('DistrictReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['DistrictReferrer'])
            return $session['DistrictReferrer'];
        return 'index';
    }

}