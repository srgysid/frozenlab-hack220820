<?php
namespace frontend\controllers;

use common\models\RealEstate;
use common\models\RealEstateType;
use common\models\Street;
use common\models\House;
use common\models\City;
use frontend\models\search\RealEstateSearch;
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

class RealEstateController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'clear-filter',
                        'house-list', 'street-list'],
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

    public function actionIndex()
    {
        $searchModel = new RealEstateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streets = ArrayHelper::map(Street::find()->orderBy('name')->all(), 'id', 'name');
        $streetsGrid = ArrayHelper::map(Street::getStreetWithCity(), 'id', 'name');
        $prefShortName = ArrayHelper::map(RealEstateType::getRealEstateTypeList(), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'streets' => $streets,
            'streetsGrid' => $streetsGrid,
            'prefShortName' => $prefShortName,
            'cities' => $cities,
        ]);
    }

    public function actionCreate()
    {
        $model = new RealEstate();

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streets = ArrayHelper::map(Street::getStreetByCity(null), 'id', 'name');
        $houses = ArrayHelper::map(House::getFullHouseByStreet(null), 'id', 'name');
        $prefShortName = ArrayHelper::map(RealEstateType::getRealEstateTypeList(), 'id', 'name');

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
                'houses' => $houses,
                'prefShortName' => $prefShortName,
                'cities' => $cities,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'streets' => $streets,
                'houses' => $houses,
                'prefShortName' => $prefShortName,
                'cities' => $cities,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->city_id = $model->house->street->city_id;
        $model->street_id = $model->house->street_id;

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streets = ArrayHelper::map(Street::getStreetByCity($model->city_id), 'id', 'name');
        $houses = ArrayHelper::map(House::getFullHouseByStreet($model->street_id), 'id', 'name');
        $prefShortName = ArrayHelper::map(RealEstateType::getRealEstateTypeList(), 'id', 'name');

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
                'houses' => $houses,
                'prefShortName' => $prefShortName,
                'cities' => $cities,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'streets' => $streets,
                'houses' => $houses,
                'prefShortName' => $prefShortName,
                'cities' => $cities,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = RealEstate::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Счет не найден'));
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


    public function actionHouseList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $street_id = $parents[0];
                if ($street_id) {
                    $out = House::getFullHouseByStreet($street_id);
                }
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionClearFilter()
    {
        $session = Yii::$app->session;
        if ($session->has('RealEstateSearch')) {
            $session->remove('RealEstateSearch');
        }
        if ($session->has('RealEstateSearchSort')) {
            $session->remove('RealEstateSearchSort');
        }

        return $this->redirect('index');
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('RealEstateReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['RealEstateReferrer'])
            return $session['RealEstateReferrer'];
        return 'index';
    }

}