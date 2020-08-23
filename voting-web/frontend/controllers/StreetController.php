<?php
namespace frontend\controllers;

use frontend\models\search\StreetSearch;
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

class StreetController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'clear-filter', 'city-by-id'],
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
        $searchModel = new StreetSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cities' => $cities,
        ]);
    }

    public function actionCreate()
    {
        $model = new Street();
        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
//        $model->city_id = 2;
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
                'cities' => $cities,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'cities' => $cities,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
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
                'cities' => $cities,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'cities' => $cities,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = Street::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Улица не найдена'));
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
        if ($session->has('StreetSearch')) {
            $session->remove('StreetSearch');
        }
        if ($session->has('StreetSearchSort')) {
            $session->remove('StreetSearchSort');
        }
        return $this->redirect('index');
    }

    public function actionCityById()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $fias_guid = null;
        $kladr_guid = null;
        if (isset($_POST['city_id'])) {
            $city = City::findOne($_POST['city_id']);
            if ($city) {
                $fias_guid = $city->fias_guid;
                $kladr_guid = $city->kladr_guid;
                return ['fias_guid' => $fias_guid, 'kladr_guid' => $kladr_guid];
            }
        }
        return false;
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('StreetReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['StreetReferrer'])
            return $session['StreetReferrer'];
        return 'index';
    }

}