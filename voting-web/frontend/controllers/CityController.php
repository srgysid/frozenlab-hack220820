<?php
namespace frontend\controllers;

use frontend\models\search\CitySearch;
use common\models\City;
use common\models\Region;
use common\models\District;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\bootstrap4\ActiveForm;

class CityController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'clear-filter', 'region-by-id', 'district-list'],
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
        $searchModel = new CitySearch();
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
        $model = new City();
        $regions = ArrayHelper::map(Region::getFullRegion(), 'id', 'name');
        $districts = ArrayHelper::map(District::getDistrictByRegion($model->region_id), 'id', 'name');

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
                'districts' => $districts,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'regions' => $regions,
                'districts' => $districts,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $regions = ArrayHelper::map(Region::getFullRegion(), 'id', 'name');
        $districts = ArrayHelper::map(District::getDistrictByRegion($model->region_id), 'id', 'name');

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
                'districts' => $districts,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'regions' => $regions,
                'districts' => $districts,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = City::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Город не найден'));
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
        if ($session->has('CitySearch')) {
            $session->remove('CitySearch');
        }
        if ($session->has('CitySearchSort')) {
            $session->remove('CitySearchSort');
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

    public function actionDistrictList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $region_id = $parents[0];
                if ($region_id) {
                    $out = District::getDistrictByRegion($region_id);
                }
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('CityReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['CityReferrer'])
            return $session['CityReferrer'];
        return 'index';
    }

}