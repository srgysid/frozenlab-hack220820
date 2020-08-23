<?php
namespace frontend\controllers;

use frontend\models\search\HouseSearch;
use common\models\House;
use common\models\Street;
use common\models\City;
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

class HouseController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'clear-filter', 'street-list', 'street-by-id'],
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
        $searchModel = new HouseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streets = ArrayHelper::map(Street::find()->orderBy('name')->all(), 'id', 'name');
        $streetsGrid = ArrayHelper::map(Street::getStreetWithCity(), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'streets' => $streets,
            'streetsGrid' => $streetsGrid,
            'cities' => $cities,
        ]);
    }

    public function actionCreate()
    {
        $model = new House();

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streets = ArrayHelper::map(Street::getStreetByCity(null), 'id', 'name');

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
                'streets' => $streets,
                'cities' => $cities,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'streets' => $streets,
                'cities' => $cities,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->city_id = $model->street->city_id;
        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streets = ArrayHelper::map(Street::getStreetByCity($model->city_id), 'id', 'name');

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
                'streets' => $streets,
                'cities' => $cities,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'streets' => $streets,
                'cities' => $cities,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = House::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Дом не найден'));
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

    public function actionStreetList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $city_id = $parents[0];
                if ($city_id) {
                    $out = Street::getStreetByCity($city_id);
                }
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionClearFilter()
    {
        $session = Yii::$app->session;
        if ($session->has('HouseSearch')) {
            $session->remove('HouseSearch');
        }
        if ($session->has('HouseSearchSort')) {
            $session->remove('HouseSearchSort');
        }
        return $this->redirect('index');
    }

    public function actionStreetById()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $fias_guid = null;
        $kladr_guid = null;
        if (isset($_POST['street_id'])) {
            $street = Street::findOne($_POST['street_id']);
            if ($street) {
                $fias_guid = $street->fias_guid;
                $kladr_guid = $street->kladr_guid;
                return ['fias_guid' => $fias_guid, 'kladr_guid' => $kladr_guid];
            }
        }
        return false;
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('HouseReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['HouseReferrer'])
            return $session['HouseReferrer'];
        return 'index';
    }

}