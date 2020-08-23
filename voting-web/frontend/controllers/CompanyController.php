<?php
namespace frontend\controllers;

use frontend\models\search\CompanySearch;
use common\models\City;
use common\models\Company;
use common\models\CompanyPhone;
use common\models\House;
use common\models\Street;
use Yii;
use yii\base\Exception;
use yii\base\InvalidCallException;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\bootstrap4\ActiveForm;

class CompanyController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'clear-filter', 'street-list',
                            'house-list'],
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
        $searchModel = new CompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string|Response
     * @throws \yii\base\Exception
     */
    public function actionCreate()
    {
        $model = new Company();
        $companyPhones = $model->getCompanyPhones();

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streets = ArrayHelper::map(Street::getStreetByCity($model->city_id), 'id', 'name');
        $houses = ArrayHelper::map(House::getFullHouseByStreet($model->street_id), 'id', 'name');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (CompanyPhone::loadMultiple($companyPhones, Yii::$app->request->post()) && CompanyPhone::validateMultiple($companyPhones)) {
                $model->setCompanyPhones($companyPhones);
            }
            if ($model->save()) {
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
                'companyPhones' => $companyPhones,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'cities' => $cities,
                'streets' => $streets,
                'houses' => $houses,
                'companyPhones' => $companyPhones,
            ]);
        }
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $companyPhones = $model->getCompanyPhones();
        $this->setCurrentUrl();

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streets = ArrayHelper::map(Street::getStreetByCity($model->city_id), 'id', 'name');
        $houses = ArrayHelper::map(House::getFullHouseByStreet($model->street_id), 'id', 'name');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (CompanyPhone::loadMultiple($companyPhones, Yii::$app->request->post()) && CompanyPhone::validateMultiple($companyPhones)) {
                $model->setCompanyPhones($companyPhones);
                if ($model->save()) {
                    $url = $this->getCurrentUrl();
                    return $this->redirect($url);
                }
            }
        }

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
                'cities' => $cities,
                'streets' => $streets,
                'houses' => $houses,
                'companyPhones' => $companyPhones,
            ]);
        }
        else {
            return $this->render('_form', [
                'model' => $model,
                'cities' => $cities,
                'streets' => $streets,
                'houses' => $houses,
                'companyPhones' => $companyPhones,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = Company::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Компания не найдена'));
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
        if ($session->has('CompanySearch')) {
            $session->remove('CompanySearch');
        }
        if ($session->has('CompanySearchSort')) {
            $session->remove('CompanySearchSort');
        }

        return $this->redirect('index');
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('CompanyReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['CompanyReferrer'])
            return $session['CompanyReferrer'];
        return 'index';
    }

    public function actionHouseList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = [];
        $selected_id = '';
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $street_id = $parents[0];
                if (is_numeric($street_id)){
                    $out = House::getFullHouseByStreet($street_id);
                    return ['output' => $out, 'selected' => $selected_id];
                }
            }
        }
        return ['output' => $out, 'selected' => ''];
    }

    public function actionStreetList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = [];
        $selected_id = '';
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $city_id = $parents[0];
                $out = Street::getStreetByCity($city_id);
                return ['output' => $out, 'selected' => $selected_id];
            }
        }
        return ['output' => $out, 'selected' => ''];
    }

}