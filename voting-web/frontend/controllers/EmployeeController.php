<?php
namespace frontend\controllers;

use frontend\models\search\EmployeeSearch;
use common\helpers\AuthHelper;
use common\models\Employee;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

use yii\web\Response;
use yii\bootstrap4\ActiveForm;

class EmployeeController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update-profile', 'clear-filter'],
                        'allow' => true,
                        'roles' => ['rl_admin','rl_key_user'],
                    ],
                    [
                        'actions' => ['change-pass', 'activate', 'deactivate', 'delete-user'],
                        'allow' => true,
                        'roles' => ['rl_admin'],
                    ]
                ]
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
        $searchModel = new EmployeeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new Employee();
        $model->scenario = Employee::SCENARIO_REGISTER;
        $model->load(Yii::$app->request->post());
        $roles = $this->getRoles();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->register()) {
            $url = $this->getCurrentUrl();
            return $this->redirect($url);
        }
        $this->setCurrentUrl();

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
                'roles' => $roles,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'roles' => $roles,
            ]);
        }
    }

    public function actionUpdateProfile($id)
    {
        $model = Employee::findByUserId($id);
        $model->scenario = Employee::SCENARIO_UPDATE;
        $model->load(Yii::$app->request->post());
        $roles = $this->getRoles();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->updateProfile()) {
            $url = $this->getCurrentUrl();
            return $this->redirect($url);

        }
        $this->setCurrentUrl();

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
                'roles' => $roles,
            ]);
        }
        else{
            return $this->render('_form', [
                'model' => $model,
                'roles' => $roles,
            ]);
        }
    }

    public function actionChangePass($id)
    {
        $model = Employee::findByUserId($id);
        $model->scenario = Employee::SCENARIO_CHANGE_PASS;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->changePass()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Пароль для пользователя {0} изменен', [$model->fullName]));
            $url = $this->getCurrentUrl();
            return $this->redirect($url);
        }
        $this->setCurrentUrl();

        if (Yii::$app->request->isAjax){
            return $this->renderAjax('change_pass', [
                'model' => $model,
            ]);
        }
        else{
            return $this->render('change_pass', [
                'model' => $model,
            ]);
        }
    }

    private function findModelByUserId($id)
    {
        $model = Employee::findByUserId($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'Сотрудник не найден'));
        }
        return $model;
    }

    public function actionClearFilter()
    {
        $session = Yii::$app->session;
        if ($session->has('EmployeeSearch')) {
            $session->remove('EmployeeSearch');
        }
        if ($session->has('EmployeeSearchSort')) {
            $session->remove('EmployeeSearchSort');
        }

        return $this->redirect('index');
    }

    public function actionDeleteUser($id)
    {
        $model = $this->findModelByUserId($id);
        $this->setCurrentUrl();

        try {
            $model->deleteEmployee();
            Yii::$app->session->addFlash('success', Yii::t('app', 'Пользователь {0} удален', [$model->fullName]));
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        $url = $this->getCurrentUrl();
        return $this->redirect($url);

    }

    public function actionActivate($id)
    {
        $model = $this->findModelByUserId($id);
        $this->setCurrentUrl();
        if ($model->activateUser()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Пользователь {0} активирован', [$model->fullName]));
        }
        $url = $this->getCurrentUrl();
        return $this->redirect($url);
    }

    public function actionDeactivate($id)
    {
        $model = $this->findModelByUserId($id);
        $this->setCurrentUrl();
        if ($model->deactivateUser()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Пользователь {0} деактивирован', [$model->fullName]));
        }
        $url = $this->getCurrentUrl();
        return $this->redirect($url);
    }

    public function setCurrentUrl()
    {
        $session = Yii::$app->session;
        $session->set('EmployeeReferrer', Yii::$app->request->referrer);
    }

    public function getCurrentUrl()
    {
        $session = Yii::$app->session;
        if ($session['EmployeeReferrer'])
            return $session['EmployeeReferrer'];
        return 'index';
    }

    /**
     * Получается список доступных для редактирования ролей для данного пользователя
     * @return array
     */
    public function getRoles()
    {
        $user = Yii::$app->user;
        $roles = AuthHelper::getRoles();

        if (!$user->can('rl_admin')) {
            unset($roles['rl_admin']);
        }

        return $roles;
    }
}