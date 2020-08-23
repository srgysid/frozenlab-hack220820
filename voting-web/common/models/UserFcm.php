<?php

namespace common\models;

use paragraph1\phpFCM\Recipient\Device;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "user_fcm".
 *
 * @property int $id
 * @property int $user_id
 * @property string $fcm_token
 * @property string $token_hash
 * @property int $created_at
 * @property int $updated_at
 * @property int $app_id
 *
 * @property User $user
 */
class UserFcm extends \yii\db\ActiveRecord
{
    const APP_ID_USER = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_fcm';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'app_id'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['user_id', 'created_at', 'updated_at', 'app_id'], 'integer'],
            [['fcm_token'], 'string', 'max' => 1024],
            [['token_hash'], 'string', 'max' => 32],
            [['token_hash'], 'required'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['app_id'], 'in', 'range' => [self::APP_ID_USER]]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge($behaviors, [
            [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()'),
                'skipUpdateOnClean' => false,   // это для обновления поля updated_at в любом случае, даже если оно не поменялось
            ],
        ]);
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'fcm_token' => Yii::t('app', 'Fcm Token'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'app_id' => Yii::t('app', 'App id'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Replace old fcm token to new. If old token is not exists, just create new one
     * @param $user_id int
     * @param $app_id int
     * @param $old_token string
     * @param $new_token string
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function replaceFcm($user_id, $app_id,  $old_token, $new_token)
    {
        if ($old_token) {
            $old_token_hash = md5($old_token);
            $oldFcm = self::findOne([
                'token_hash' => $old_token_hash,
                'app_id' => $app_id
            ]);

//            $oldFcm = self::findOne([
//                'user_id' => $user_id,
//                'fcm_token' => $old_token,
//                'app_id' => $app_id
//            ]);
            // update old_fcm if found and $new_token is set
            if ($oldFcm && $new_token) {
                $oldFcm->user_id = $user_id;
                $oldFcm->fcm_token = $new_token;
                $oldFcm->token_hash = md5($old_token);
                $oldFcm->save(false);
                return true;
            }

            // delete old token if new token is null
            if ($oldFcm && ($new_token == null)) {
                $oldFcm->delete();
                return true;
            }
        }

        // check if new fcm already there
        if ($new_token) {
            $new_token_hash = md5($new_token);
            $newFcm = self::findOne([
                'token_hash' => $new_token_hash,
                'app_id' => $app_id
            ]);

            if ($newFcm) {
                // we already have new fcm there, update user_id if need
                if ($newFcm->user_id != $user_id) {
                    $newFcm->user_id = $user_id;
                    $newFcm->save(false);
                }
                return true;
            }
        }

//        $newFcm = self::findOne([
//            'user_id' => $user_id,
//            'fcm_token' => $new_token,
//            'app_id' => $app_id,
//        ]);

        // указан новый fcm, и его еще нет в базе
        $newFcm = new UserFcm([
            'user_id' => $user_id,
            'fcm_token' => $new_token,
            'token_hash' => md5($new_token),
            'app_id' => $app_id,
        ]);
        $newFcm->save(false);
        return true;
    }

    /**
     * Отправляет уведомление пользователю или списку пользователей
     * @param $user_id int|array id пользователя или массив id пользователей
     * @param $app_id int|array id приложения (UserFcm::APP_ID_USER|UserFcm::APP_ID_PERFORMER)
     * @param $title string Заголовок сообщения
     * @param $message string Текст сообщения
     * @param null $data payload-данные
     * @return bool Возвращае true в случае успеха
     */
    public static function sendNotification($user_id, $app_id, $title, $message, $data = null)
    {
        if (!$user_id) return true;

        $fcm_tokens = UserFcm::findAll(['user_id' => $user_id, 'app_id' => $app_id]);
        if (!$fcm_tokens) {
            return true;
        }

        $note = Yii::$app->fcm->createNotification($title, $message);
        $note->setSound('default');
        $message = Yii::$app->fcm->createMessage();

        foreach ($fcm_tokens as $fcm_token) {
            $message->addRecipient(new Device($fcm_token['fcm_token']));
        }

        $message->setNotification($note)
            ->setData($data);

        $response = Yii::$app->fcm->send($message);

        return ($response->getStatusCode() == 200);
    }


}
