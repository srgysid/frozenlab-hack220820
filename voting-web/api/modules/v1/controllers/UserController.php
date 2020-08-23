<?php
namespace api\modules\v1\controllers;


use api\controllers\BaseApiController;
use api\modules\v1\models\ChangePasswordModel;
use common\helpers\AuthHelper;
use common\models\UserFcm;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class UserController extends BaseApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors = ArrayHelper::merge([
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'profile' => ['GET'],
                    'fcm'  => ['POST'],
                ],
            ]
        ], $behaviors);

        return $behaviors;
    }

    public function actionProfile()
    {
        $user = Yii::$app->user->identity;
        $profile = $user->userProfile;

        return [
            'email' => $user['email'],
            'first_name' => $profile['first_name'] ?? null,
            'second_name' => $profile['second_name'] ?? null,
            'third_name' => $profile['third_name'] ?? null,
            'phone' => $profile['phone'] ?? null,
        ];
    }

    /**
     * Register FCM token
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionFcm()
    {
        $user = Yii::$app->user;
        $request = Yii::$app->request;

        $old_token = $request->post('old_token', null);
        $new_token = $request->post('new_token', null);
        $app_id = $request->post('app_id', null);

        if (!$app_id) {
            throw new BadRequestHttpException('app_id не указан');
        }

        if (!in_array($app_id, [UserFcm::APP_ID_USER])) {
            throw new BadRequestHttpException('Указанного app_id не существует');
        }

        return UserFcm::replaceFcm($user->id, $app_id, $old_token, $new_token);
    }

    /**
     * Удаление авторизованного пользователя
     * @return bool
     */
    public function actionDelete()
    {
        $user = Yii::$app->user;
        if ($user->identity->delete()) {
            return true;
        }
        return false;
    }

    /**
     * Изменение пароля пользователю
     * @return array|bool
     */
    public function actionChangePassword()
    {
        $model = new ChangePasswordModel();

        if ($model->load(Yii::$app->request->post(), '')) {

            if ($model->validate() && $model->changePassword()) {
                return ['access_token' => Yii::$app->user->identity->access_token];
            } else {
                return $this->responseErrors($model->errors);
            }
        }

        return false;
    }
}