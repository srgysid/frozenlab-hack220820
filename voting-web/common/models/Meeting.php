<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "meeting".
 *
 * @property int $id
 * @property string|null $title
 * @property int $house_id
 * @property int|null $area
 * @property int $type_voting_id
 * @property int $form_voting_id
 * @property string|null $reg_num
 * @property string|null $started_at
 * @property string|null $finished_at
 * @property string|null $meeting_place
 * @property string|null $receiving_place
 * @property string|null $familiarization_place
 * @property string|null $familiarization_date_from
 * @property string|null $familiarization_date_to
 * @property string|null $familiarization_time_from
 * @property string|null $familiarization_time_to
 * @property int|null $type_initiator
 * @property int|null $initiator_company_id
 * @property int|null $type_administrator
 * @property int|null $administrator_company_id
 * @property int|null $administrator_owner_id
 * @property string $created_at
 * @property string|null $description
 *
 * @property InitiatorOwner[] $initiatorOwners
 * @property Company $initiatorCompany
 * @property Company $administratorCompany
 * @property FormVoting $formVoting
 * @property House $house
 * @property Owner $administratorOwner
 * @property TypeVoting $typeVoting
 */
class Meeting extends \yii\db\ActiveRecord
{
    public $city_id;
    public $street_id;
    public $owner_ids;

    const INITIATOR_COMPANY = 1;
    const INITIATOR_OWNER = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meeting';
    }


    public function init()
    {
        parent::init();
        // заполнить рег номер после создания заявки
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'fillRegNum']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'fillReestrMeeting']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['house_id', 'type_voting_id', 'form_voting_id', 'type_initiator', 'type_administrator'], 'required'],
            [['house_id', 'area', 'type_voting_id', 'form_voting_id', 'type_initiator', 'initiator_company_id', 'type_administrator', 'administrator_company_id', 'administrator_owner_id', 'city_id', 'street_id'], 'default', 'value' => null],
            [['house_id', 'area', 'type_voting_id', 'form_voting_id', 'type_initiator', 'initiator_company_id', 'type_administrator', 'administrator_company_id', 'administrator_owner_id', 'city_id', 'street_id'], 'integer'],
            [['started_at', 'distant_started_at', 'finished_at', 'familiarization_date_from', 'familiarization_date_to', 'created_at', 'owner_ids', 'protocol_date'], 'safe'],
            [['description'], 'string'],
            [['meeting_place', 'receiving_place', 'familiarization_place'], 'string', 'max' => 255],
            [['reg_num'], 'string', 'max' => 24],

            [['familiarization_time_from', 'familiarization_time_to'], 'string', 'max' => 5],
            [['familiarization_time_from', 'familiarization_time_to'], 'default', 'value' => null],
            [['familiarization_time_from', 'familiarization_time_to'], 'validateTime'],

            ['familiarization_time_to', 'compare', 'compareAttribute'=>'familiarization_time_from', 'operator'=>'>'],
            ['familiarization_date_to', 'compare', 'compareAttribute'=>'familiarization_date_from', 'operator'=>'>'],
            ['finished_at', 'compare', 'compareAttribute'=>'distant_started_at', 'operator'=>'>'],

            [['owner_ids'], 'each', 'rule' => ['exist', 'skipOnError' => true, 'targetClass' => Owner::className(), 'targetAttribute' => ['owner_ids' => 'id']]],
            [['owner_ids'], 'each', 'rule' => ['filter', 'filter' => 'intval']],

            [['initiator_company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['initiator_company_id' => 'id']],
            [['administrator_company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['administrator_company_id' => 'id']],
            [['form_voting_id'], 'exist', 'skipOnError' => true, 'targetClass' => FormVoting::className(), 'targetAttribute' => ['form_voting_id' => 'id']],
            [['house_id'], 'exist', 'skipOnError' => true, 'targetClass' => House::className(), 'targetAttribute' => ['house_id' => 'id']],
            [['administrator_owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => Owner::className(), 'targetAttribute' => ['administrator_owner_id' => 'id']],
            [['type_voting_id'], 'exist', 'skipOnError' => true, 'targetClass' => TypeVoting::className(), 'targetAttribute' => ['type_voting_id' => 'id']],

            [['started_at'], 'validateStartDate'],
        ];
    }

    public function validateStartDate($attribute, $params)
    {
        $value = Yii::$app->formatter->asDate($this->$attribute, 'php:Y-m-d');
        $create = $this->created_at;
        if ($value) {
            if ($create) {
                $tmpDate = new \DateTime($create);
            }
            else {
                $tmpDate = new \DateTime();
            }
            $tmpDate->modify('+10 day');
            $strTmpDate = $tmpDate->format('Y-m-d');

            if ($value < $strTmpDate) {
                $this->addError($attribute, Yii::t('app', 'Минимальная дата проведения собрания '.$strTmpDate));
            }
        }
    }

    public function  validateTime($attribute, $params)
    {
        $value = $this->$attribute;
        if (empty($value)) {
            $this->$attribute = null;
            return;
        } else {
            $time_parts = explode(':', $value);
            if (count($time_parts) != 2) {
                $this->addError($attribute, 'Формат времени указан не верно');
                return;
            }
            if (!(is_numeric($time_parts[0]) && ($time_parts[0] >= 0) && ($time_parts[0] <= 23))) {
                $this->addError($attribute, 'Часы указаны не верно');
                return;
            }
            if (!(is_numeric($time_parts[0]) && ($time_parts[1] >= 0) && ($time_parts[1] <= 59))) {
                $this->addError($attribute, 'Минуты указаны не верно');
                return;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'house_id' => 'Дом',
            'street_id' => 'Улица',
            'city_id' => 'Город',
            'area' => 'Общая площадь',
            'type_voting_id' => 'Вид собрания',
            'form_voting_id' => 'Форма собрания',
            'reg_num' => 'Номер собрания',
            'started_at' => 'Дата и время собрания',
            'distant_started_at' => 'Дата начала заочного этапа',
            'finished_at' => 'Дата окончания заочного этапа',
            'meeting_place' => 'Место проведения собрания',
            'receiving_place' => 'Место приёма бюллетеней',
            'familiarization_place' => 'Место для ознакомления',
            'familiarization_date_from' => 'Дата начала ознакомления',
            'familiarization_date_to' => 'Дата окончания ознакомления',
            'familiarization_time_from' => 'Время с',
            'familiarization_time_to' => 'Время по',
            'type_initiator' => 'Инициатор  собрания',
            'initiator_company_id' => 'Управляющая компания',
            'type_administrator' => 'Администратор собрания',
            'administrator_company_id' => 'Управляющая компания',
            'administrator_owner_id' => 'Собственник',
            'owner_ids' => 'Собственники',
            'created_at' => 'Дата регистрации',
            'description' => 'Description',
            'protocol_date' => 'Protocol Date',
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge($behaviors, [
            [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()'),
                'updatedAtAttribute' => false,
            ],
        ]);
        return $behaviors;
    }

    public static function getInitiatorList()
    {
        return [
            '1' => Yii::t('app', 'Управляющая компания'),
            '2' => Yii::t('app', 'Собственники'),
        ];
    }

    /**
     * Gets query for [[InitiatorOwners]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInitiatorOwners()
    {
        return $this->hasMany(InitiatorOwner::className(), ['meeting_id' => 'id']);
    }

    /**
     * Gets query for [[InitiatorCompany]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInitiatorCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'initiator_company_id']);
    }

    /**
     * Gets query for [[AdministratorCompany]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdministratorCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'administrator_company_id']);
    }

    /**
     * Gets query for [[FormVoting]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFormVoting()
    {
        return $this->hasOne(FormVoting::className(), ['id' => 'form_voting_id']);
    }

    /**
     * Gets query for [[House]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHouse()
    {
        return $this->hasOne(House::className(), ['id' => 'house_id']);
    }

    /**
     * Gets query for [[AdministratorOwner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdministratorOwner()
    {
        return $this->hasOne(Owner::className(), ['id' => 'administrator_owner_id']);
    }

    /**
     * Gets query for [[TypeVoting]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTypeVoting()
    {
        return $this->hasOne(TypeVoting::className(), ['id' => 'type_voting_id']);
    }

    /**
     * Gets query for [[MeetingQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeetingQuestions()
    {
        return $this->hasMany(MeetingQuestion::className(), ['meeting_id' => 'id']);
    }

    /**
     * Gets query for [[MeetingVoters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeetingVoters()
    {
        return $this->hasMany(MeetingVoter::className(), ['meeting_id' => 'id']);
    }

    public function getReestrMeetings()
    {
        return $this->hasMany(ReestrMeeting::className(), ['meeting_id' => 'id']);
    }

    public function fillRegNum()
    {
        $seq_name = 'meeting_reg_num_'.date('Y').'_'.str_pad($this->house_id, 5, "0", STR_PAD_LEFT);
        $prevStr = date('Y');
        self::getDb()->createCommand('CREATE SEQUENCE IF NOT EXISTS '.$seq_name)->execute();
        self::getDb()->createCommand("update meeting set reg_num = concat($prevStr,'/',nextval('$seq_name')) where id = :id", ['id' => $this->id])->execute();
    }

    public function fillReestrMeeting()
    {
        $reestr_id = Reestr::maxReestrHouse($this->house_id);
        if ($reestr_id) {
            $modelReestrMeeting = new ReestrMeeting();
            $modelReestrMeeting->meeting_id = $this->id;
            $modelReestrMeeting->reestr_id = $reestr_id;
            $modelReestrMeeting->save();

            $modelsReestrDetail = ReestrDetail::find()->where(['reestr_id'=>$reestr_id])->all();
            if ($modelsReestrDetail) {
                foreach ($modelsReestrDetail as $reestrDetail){
                    $modelMeetingVoter = new MeetingVoter();
                    $modelMeetingVoter->meeting_id = $this->id;
                    $modelMeetingVoter->num = $reestrDetail->num;
                    $modelMeetingVoter->name = $reestrDetail->name;
                    $modelMeetingVoter->area = $reestrDetail->area;
                    $modelMeetingVoter->type_real_estate = $reestrDetail->type_real_estate;
                    $modelMeetingVoter->type_owner_id = $reestrDetail->type_owner_id;
                    $modelMeetingVoter->part = $reestrDetail->part;
                    $modelMeetingVoter->ownership = $reestrDetail->ownership;
                    $modelMeetingVoter->email = $reestrDetail->email;
                    $modelMeetingVoter->phone = $reestrDetail->phone;

                    $modelMeetingVoter->save();
                }
            }
        }
    }

    public function saveMeeting()
    {
        if ($this->save()){
            InitiatorOwner::deleteAll(['meeting_id' => $this->id]);

            if ($this->owner_ids) {
                foreach ($this->owner_ids as $ownerId) {
                    $modelInitiatorOwner = new InitiatorOwner();
                    $modelInitiatorOwner->meeting_id = $this->id;
                    $modelInitiatorOwner->owner_id = $ownerId;
                    $modelInitiatorOwner->save(false);
                }
            }
            return true;
        }
        return false;
    }


}
