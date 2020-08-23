<?php
namespace api\modules\v1\controllers;

use common\models\User;
use Yii;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class LoginController extends \yii\rest\Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors = ArrayHelper::merge([
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'login' => ['POST'],
                ],
            ]
        ], $behaviors);

        return $behaviors;
    }

    /**
     * Response with error status code 422
     * @link https://github.com/yiisoft/yii2/blob/master/docs/guide-ru/rest-error-handling.md
     * @param $errors array errors array
     * @return array array to be published
     */
    protected function responseErrors($errors)
    {
        Yii::$app->response->setStatusCode(422);
        return [
            'errors' => $errors
        ];
    }

    public function actionIndex()
    {
        $post = Yii::$app->request->getBodyParams();
        if (!isset($post['phone'])) {
            throw new BadRequestHttpException(Yii::t('app', 'Телефон не указан'));
        }

        if (!isset($post['password'])) {
            throw new BadRequestHttpException(Yii::t('app', 'Пароль не указан'));
        }

        $user = User::findByPhone($post['phone']);
        if (!$user) {
            throw new BadRequestHttpException(Yii::t('app', 'Пользователь или пароль указаны не верно'));
        }

        if (mb_strlen($post['password']) == 0) {
            throw new BadRequestHttpException(Yii::t('app', 'Пароль не может быть пустым'));
        }

        if (!$user->validatePassword($post['password'])) {
            throw new BadRequestHttpException(Yii::t('app', 'Пользователь или пароль указаны не верно'));
        }

        return [
            'access_token' => $user->access_token
        ];
    }

}