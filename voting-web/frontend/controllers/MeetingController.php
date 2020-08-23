<?php
namespace frontend\controllers;

use common\models\Meeting;
use common\models\MeetingQuestion;
use common\models\InitiatorOwner;
use common\models\RealEstate;
use common\models\Reestr;
use common\models\ReestrDetail;
use common\models\ReestrMeeting;
use common\models\Street;
use common\models\House;
use common\models\City;
use common\models\Company;
use common\models\Owner;
use common\models\Title;
use common\models\TypeVoting;
use common\models\FormVoting;
use frontend\models\search\MeetingSearch;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseFileHelper;
use yii\helpers\BaseInflector;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Controller;
use yii\bootstrap4\ActiveForm;
use yii\web\BadRequestHttpException;
use Mpdf\Mpdf;
use ZipArchive;


class MeetingController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'clear-filter', 'house-list', 'street-list', 'owner-list',
                            'validate-form', 'area-by-house', 'current-address'],
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
        $default_from_date = new \DateTime();
        $default_from_date->modify('-90 days');
        $searchModel = new MeetingSearch([
            'created_at_from' => trim(Yii::$app->formatter->asDate($default_from_date)),
            'created_at_from_formatted' => $default_from_date->format('Y-m-d'),
        ]);
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
        $model = new Meeting();
        $model->load(Yii::$app->request->post());

        $cities = ArrayHelper::map(City::getCityFull(), 'id', 'name');
        $streets = ArrayHelper::map(Street::getStreetByCity($model->city_id), 'id', 'name');
        $houses = ArrayHelper::map(House::getFullHouseByStreet($model->street_id), 'id', 'name');
        $company = ArrayHelper::map(Company::find()->orderBy('name')->all(), 'id', 'name');
        $owners = ArrayHelper::map(Owner::getOwnerByHouse($model->house_id), 'id', 'name');

        $type_voting = ArrayHelper::map(TypeVoting::find()->orderBy('id')->all(), 'id', 'name');
        $form_voting = ArrayHelper::map(FormVoting::find()->orderBy('id')->all(), 'id', 'name');

        if (Yii::$app->request->isAjax) {
            $model->load(Yii::$app->request->post());
        }
        else {
            if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->saveMeeting()) {
                $url = $this->getCurrentUrl();
                return $this->redirect($url);
            }
        }
        $this->setCurrentUrl();

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
                'streets' => $streets,
                'houses' => $houses,
                'cities' => $cities,
                'type_voting' => $type_voting,
                'form_voting' => $form_voting,
                'company' => $company,
                'owners' => $owners,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'streets' => $streets,
                'houses' => $houses,
                'cities' => $cities,
                'type_voting' => $type_voting,
                'form_voting' => $form_voting,
                'company' => $company,
                'owners' => $owners,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->city_id = $model->house->street->city_id;
        $model->street_id = $model->house->street_id;
        $model->owner_ids = InitiatorOwner::getInitiatorOwnerByMeetingId($id);

        $company = ArrayHelper::map(Company::find()->orderBy('name')->all(), 'id', 'name');
        $owners = ArrayHelper::map(Owner::getOwnerByHouse($model->house_id), 'id', 'name');

        $type_voting = ArrayHelper::map(TypeVoting::find()->orderBy('id')->all(), 'id', 'name');
        $form_voting = ArrayHelper::map(FormVoting::find()->orderBy('id')->all(), 'id', 'name');

        if (Yii::$app->request->isAjax) {
            $model->load(Yii::$app->request->post());
        }
        else {
            if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->saveMeeting()) {
                $url = $this->getCurrentUrl();
                return $this->redirect($url);
            }
        }
        $this->setCurrentUrl();

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
                'type_voting' => $type_voting,
                'form_voting' => $form_voting,
                'company' => $company,
                'owners' => $owners,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'type_voting' => $type_voting,
                'form_voting' => $form_voting,
                'company' => $company,
                'owners' => $owners,
            ]);
        }
    }

    private function findModel($id)
    {
        $model = Meeting::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Собрание не найдено'));
        }

        return $model;
    }

    public function actionDelete($id)
    {
        $this->setCurrentUrl();
        if (InitiatorOwner::deleteAll(['meeting_id' => $id])) {
            $this->findModel($id)->delete();
        }

        $url = $this->getCurrentUrl();
        return $this->redirect($url);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        $reestr_id = Reestr::maxReestrHouse($model->house_id);

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('view', [
                'model' => $model,
                'reestr_id' => $reestr_id,
            ]);
        }
        else{
            return $this->render('view', [
                'model' => $model,
                'reestr_id' => $reestr_id,
            ]);
        }
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

    public function actionOwnerList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $house_id = $parents[0];
                if ($house_id) {
                    $out = Owner::getOwnerByHouse($house_id);
                }
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionAreaByHouse()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $area = null;
        if (isset($_POST['house_id'])) {
            $house_id = $_POST['house_id'];
            if ($house_id != null) {
                $modelHouse =  House::findOne($house_id);
                if ($modelHouse){
                    $area = $modelHouse->area;
                }
            }
        }
        return ['area' => $area];
    }

    public function actionCurrentAddress()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $address = null;
        if (isset($_POST['house_id'])) {
            $house_id = $_POST['house_id'];
            if ($house_id != null) {
                $modelHouse =  House::findOne($house_id);
                if ($modelHouse){
//                    $address = $modelHouse->street->city->name.', '.$modelHouse->street->name.', д. '.$modelHouse->num;
                    $address = $modelHouse->street->city->name.', '.$modelHouse->street->pref_short.' '.$modelHouse->street->name.', д. '.$modelHouse->num;
                }
            }
        }
        return ['address' => $address];
    }

    public function actionClearFilter()
    {
        $session = Yii::$app->session;
        if ($session->has('MeetingSearch')) {
            $session->remove('MeetingSearch');
        }
        if ($session->has('MeetingSearchSort')) {
            $session->remove('MeetingSearchSort');
        }

        return $this->redirect('index');
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('MeetingReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['MeetingReferrer'])
            return $session['MeetingReferrer'];
        return 'index';
    }

    public function actionValidateForm($id = null)
    {
        if ($id) {
            $model = $this->findModel($id);
        }
        else {
            $model = new Meeting();
        }

        $model->load(Yii::$app->request->post());

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        throw new BadRequestHttpException(Yii::t('app', 'Ошибка запроса'));
    }

}