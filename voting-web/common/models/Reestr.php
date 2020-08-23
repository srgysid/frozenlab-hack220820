<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use moonland\phpexcel\Excel;

/**
 * This is the model class for table "reestr".
 *
 * @property int $id
 * @property int $house_id
 * @property string|null $reg_num
 * @property string $created_at
 *
 * @property House $house
 * @property ReestrDetail[] $reestrDetails
 * @property ReestrMeeting[] $reestrMeetings
 */
class Reestr extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

    public $excelFile;

    public static function tableName()
    {
        return 'reestr';
    }

    public function init()
    {
        parent::init();
        // заполнить рег номер после создания заявки
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'fillRegNum']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['house_id'], 'required'],
            [['house_id'], 'default', 'value' => null],
            [['house_id'], 'integer'],
            [['created_at'], 'safe'],
            [['reg_num'], 'string', 'max' => 24],
            [['house_id'], 'exist', 'skipOnError' => true, 'targetClass' => House::className(), 'targetAttribute' => ['house_id' => 'id']],
            [['excelFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'xls, xlsx', 'maxFiles' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'house_id' => 'Дом',
            'reg_num' => 'Номер реестра',
            'created_at' => 'Дата реестра',
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
     * Gets query for [[ReestrDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReestrDetails()
    {
        return $this->hasMany(ReestrDetail::className(), ['reestr_id' => 'id']);
    }

    /**
     * Gets query for [[ReestrMeetings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReestrMeetings()
    {
        return $this->hasMany(ReestrMeeting::className(), ['reestr_id' => 'id']);
    }

    public function fillRegNum()
    {
        $seq_name = 'reestr_reg_num_'.str_pad($this->house_id, 5, "0", STR_PAD_LEFT);
        self::getDb()->createCommand('CREATE SEQUENCE IF NOT EXISTS '.$seq_name)->execute();
        self::getDb()->createCommand("update reestr set reg_num = nextval('$seq_name') where id = :id", ['id' => $this->id])->execute();
    }

    public function saveData()
    {
        $house_id = $this->house_id;
        $this->save();
        if ($house_id) {
            $arrOwners = Owner::getOwnerByHouse($house_id);
            if ($arrOwners) {
                foreach ($arrOwners as $ownerRow){
                    $modelOwner = Owner::findOne($ownerRow['id']);
                    if ($modelOwner){
                        $modelReestrDetail = new ReestrDetail();

                        $modelReestrDetail->reestr_id = $this->id;
                        $modelReestrDetail->num = $modelOwner->realEstate->num;
                        $modelReestrDetail->name = $modelOwner->name;
                        $modelReestrDetail->area = $modelOwner->realEstate->area;
                        $modelReestrDetail->type_real_estate = $modelOwner->realEstate->realEstateType->name;
                        $modelReestrDetail->type_owner_id = $modelOwner->type_owner_id;
                        $modelReestrDetail->part = (string)$modelOwner->percent_own.'%';
                        $modelReestrDetail->ownership = $modelOwner->ownership;
                        $modelReestrDetail->email = $modelOwner->email;
                        $modelReestrDetail->phone = $modelOwner->phone;

                        $modelReestrDetail->save();
                    }
                }
            }
        }

        return true;
    }

    public static function maxReestrHouse($house_id)
    {
        $maxId = 0;
        $sql = "
            select max(reestr.id)
            from reestr
            where reestr.house_id = :house_id
        ";

        $maxId = Yii::$app->db->createCommand($sql, ['house_id' => $house_id])->queryScalar();
        return $maxId;
    }


    public function importToTable()
    {
        if ($this->save()){
        }
        return true;
    }

}
