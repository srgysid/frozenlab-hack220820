<?php
namespace frontend\controllers;

use common\models\Owner;
use common\models\City;
use common\models\Street;
use common\models\House;
use common\models\RealEstate;
use common\models\TypeOwner;
use frontend\models\search\OwnerSearch;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\bootstrap4\ActiveForm;

class OwnerController extends Controller
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
                        'actions' => ['index','view','create','update','delete','real-estate-list','house-list','street-list','clear-filter','validate-form'],
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
        $searchModel = new OwnerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streetsGrid = ArrayHelper::map(Street::getStreetWithCity(), 'id', 'name');
        $type_owner = ArrayHelper::map(TypeOwner::find()->orderBy('name')->all(), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cities' => $cities,
            'streetsGrid' => $streetsGrid,
            'type_owner' => $type_owner,
        ]);
    }

    public function actionCreate()
    {
        $model = new Owner();
        $model->load(Yii::$app->request->post());

        $cities = ArrayHelper::map(City::find()->orderBy('name')->all(), 'id', 'name');
        $streets = ArrayHelper::map(Street::getStreetByCity($model->city_id), 'id', 'name');
        $houses = ArrayHelper::map(House::getFullHouseByStreet($model->street_id), 'id', 'name');
        $real_estate = ArrayHelper::map(RealEstate::getRealEstateByHouse($model->house_id), 'id', 'name');
        $type_owner = ArrayHelper::map(TypeOwner::find()->orderBy('name')->all(), 'id', 'name');

        if (Yii::$app->request->isAjax) {
            $model->load(Yii::$app->request->post());
        }
        else {
            if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
                $url = $this->getCurrentUrl();
                return $this->redirect($url);
            }
        }

        $this->setCurrentUrl();

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
                'cities' => $cities,
                'streets' => $streets,
                'houses' => $houses,
                'real_estate' => $real_estate,
                'type_owner' => $type_owner,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'cities' => $cities,
                'streets' => $streets,
                'houses' => $houses,
                'real_estate' => $real_estate,
                'type_owner' => $type_owner,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $modelRealEstate = RealEstate::findOne($model->real_estate_id);
        $model->house_id = $modelRealEstate->house_id;
        $model->street_id = $modelRealEstate->house->street_id;
        $model->city_id = $modelRealEstate->house->street->city_id;

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streets = ArrayHelper::map(Street::getStreetByCity($model->city_id), 'id', 'name');
        $houses = ArrayHelper::map(House::getFullHouseByStreet($model->street_id), 'id', 'name');
        $real_estate = ArrayHelper::map(RealEstate::getRealEstateByHouse($model->house_id), 'id', 'name');
        $type_owner = ArrayHelper::map(TypeOwner::find()->orderBy('name')->all(), 'id', 'name');

        if (Yii::$app->request->isAjax) {
            $model->load(Yii::$app->request->post());
        }
        else {
            if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
                $url = $this->getCurrentUrl();
                return $this->redirect($url);
            }
        }
        $this->setCurrentUrl();

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
                'cities' => $cities,
                'streets' => $streets,
                'houses' => $houses,
                'real_estate' => $real_estate,
                'type_owner' => $type_owner,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'cities' => $cities,
                'streets' => $streets,
                'houses' => $houses,
                'real_estate' => $real_estate,
                'type_owner' => $type_owner,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = Owner::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Собственник не найден'));
        }

        return $model;
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

    public function actionRealEstateList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $house_id = $parents[0];
                if ($house_id) {
                    $out = RealEstate::getRealEstateByHouse($house_id);
                }
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionClearFilter()
    {
        $session = Yii::$app->session;
        if ($session->has('OwnerSearch')) {
            $session->remove('OwnerSearch');
        }
        if ($session->has('OwnerSearchSort')) {
            $session->remove('OwnerSearchSort');
        }

        return $this->redirect('index');
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('OwnerReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['OwnerReferrer'])
            return $session['OwnerReferrer'];
        return 'index';
    }

    public function actionValidateForm()
    {
        $model = new Owner();
        $model->load(Yii::$app->request->post());

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        throw new BadRequestHttpException(Yii::t('app', 'Ошибка запроса'));
    }

}