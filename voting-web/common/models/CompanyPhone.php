<?php

namespace common\models;

use yii\base\Model;

/**
 * This is the model class json attribute "company.phone".
 *
 * @property int $phone
 * @property string $description
 */
class CompanyPhone extends Model
{
    public $phone;
    public $description;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone'], 'filter', 'filter' => function($value) {
                return ($value == '0' ? null : $value);
            }],
//            [['phone'], 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],
            [['phone', 'description'], 'default', 'value' => null],
            [['phone'], 'match', 'pattern' => '/^\d{10}$/', 'enableClientValidation'=> false, 'skipOnEmpty' => true],
            [['description'], 'string', 'max' => 255],
            [['description'], 'filter', 'filter' => 'trim'],
            [['description'], 'filter', 'filter' => function($value) {
                return (mb_strlen($value) == 0 ? null : $value);
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'Телефон',
            'description' => 'Описание',
        ];
    }

}
