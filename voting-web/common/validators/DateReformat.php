<?php
namespace common\validators;


use DateTime;
use IntlDateFormatter;
use kartik\datecontrol\DateControl;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FormatConverter;
use yii\validators\Validator;

class DateReformat extends Validator
{
    const TYPE_DATE = 'date';

    public $type = self::TYPE_DATE;

    public $fromFormat;

    public $toFormat;

    public $targetField;

    /**
     * @var bool set this parameter to true if you need strict date format validation (e.g. only such dates pass
     * validation for the following format 'yyyy-MM-dd': '0011-03-25', '2019-04-30' etc. and not '18-05-15',
     * '2017-Mar-14' etc. which pass validation if this parameter is set to false)
     * @since 2.0.22
     */
    public $strictDateFormat = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'The format of {attribute} is invalid.');
        }

        if ($this->fromFormat === null) {
            if ($this->type === self::TYPE_DATE) {
                $this->fromFormat = Yii::$app->modules['datecontrol']['displaySettings'][\kartik\datecontrol\Module::FORMAT_DATE];
            } else {
                throw new InvalidConfigException('Unknown validation type set for DateReformat::$type: ' . $this->type);
            }
        }

        if ($this->toFormat === null) {
            if ($this->type === self::TYPE_DATE) {
                $this->toFormat = Yii::$app->modules['datecontrol']['saveSettings'][\kartik\datecontrol\Module::FORMAT_DATE];
            } else {
                throw new InvalidConfigException('Unknown validation type set for DateValidator::$type: ' . $this->type);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if ($this->isEmpty($value)) {
            if ($this->targetField !== null) {
                $model->{$this->targetField} = null;
            }
            return;
        }

        $timestamp = $this->parseDateValueFormat($value, $this->fromFormat);
        if ($timestamp === false) {
            $this->addError($model, $attribute, $this->message, []);
        } /*elseif ($this->min !== null && $timestamp < $this->min) {
            $this->addError($model, $attribute, $this->tooSmall, ['min' => $this->minString]);
        } elseif ($this->max !== null && $timestamp > $this->max) {
            $this->addError($model, $attribute, $this->tooBig, ['max' => $this->maxString]);
        }*/ elseif ($this->targetField !== null) {
//            if ($this->timestampAttributeFormat === null) {
//                $model->{$this->timestampAttribute} = $timestamp;
//            } else {
//                $model->{$this->timestampAttribute} = $this->formatTimestamp($timestamp, $this->timestampAttributeFormat);
//            }
            $model->{$this->targetField} = $this->formatTimestamp($timestamp, $this->toFormat);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $timestamp = $this->parseDateValue($value);
        if ($timestamp === false) {
            return [$this->message, []];
        } elseif ($this->min !== null && $timestamp < $this->min) {
            return [$this->tooSmall, ['min' => $this->minString]];
        } elseif ($this->max !== null && $timestamp > $this->max) {
            return [$this->tooBig, ['max' => $this->maxString]];
        }

        return null;
    }

    /**
     * Parses date string into UNIX timestamp.
     *
     * @param string $value string representing date
     * @return int|false a UNIX timestamp or `false` on failure.
     */
    protected function parseDateValue($value)
    {
        // TODO consider merging these methods into single one at 2.1
        return $this->parseDateValueFormat($value, $this->format);
    }

    /**
     * Parses date string into UNIX timestamp.
     *
     * @param string $value string representing date
     * @param string $format expected date format
     * @return int|false a UNIX timestamp or `false` on failure.
     * @throws InvalidConfigException
     */
    private function parseDateValueFormat($value, $format)
    {
        if (is_array($value)) {
            return false;
        }
//        if (strncmp($format, 'php:', 4) === 0) {
//            $format = substr($format, 4);
//        } else {
//            if (extension_loaded('intl')) {
//                return $this->parseDateValueIntl($value, $format);
//            }

            //// fallback to PHP if intl is not installed
        $format = FormatConverter::convertDateIcuToPhp($format, 'date');
//        }

        return $this->parseDateValuePHP($value, $format);
    }

    /**
     * Parses a date value using the IntlDateFormatter::parse().
     * @param string $value string representing date
     * @param string $format the expected date format
     * @return int|bool a UNIX timestamp or `false` on failure.
     * @throws InvalidConfigException
     */
    private function parseDateValueIntl($value, $format)
    {
        $formatter = $this->getIntlDateFormatter($format);
        // enable strict parsing to avoid getting invalid date values
        $formatter->setLenient(false);

        // There should not be a warning thrown by parse() but this seems to be the case on windows so we suppress it here
        // See https://github.com/yiisoft/yii2/issues/5962 and https://bugs.php.net/bug.php?id=68528
        $parsePos = 0;
        $parsedDate = @$formatter->parse($value, $parsePos);
        $valueLength = mb_strlen($value, Yii::$app ? Yii::$app->charset : 'UTF-8');
        if ($parsedDate === false || $parsePos !== $valueLength || ($this->strictDateFormat && $formatter->format($parsedDate) !== $value)) {
            return false;
        }

        return $parsedDate;
    }

    /**
     * Creates IntlDateFormatter
     *
     * @param $format string date format
     * @return IntlDateFormatter
     * @throws InvalidConfigException
     */
    private function getIntlDateFormatter($format)
    {
        if (!isset($this->_dateFormats[$format])) {
            // if no time was provided in the format string set time to 0 to get a simple date timestamp
            $hasTimeInfo = (strpbrk($format, 'ahHkKmsSA') !== false);
            $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $hasTimeInfo ? $this->timeZone : 'UTC', null, $format);

            return $formatter;
        }

        if ($this->type === self::TYPE_DATE) {
            $dateType = $this->_dateFormats[$format];
            $timeType = IntlDateFormatter::NONE;
            $timeZone = 'UTC';
        } /*elseif ($this->type === self::TYPE_DATETIME) {
            $dateType = $this->_dateFormats[$format];
            $timeType = $this->_dateFormats[$format];
            $timeZone = $this->timeZone;
        } elseif ($this->type === self::TYPE_TIME) {
            $dateType = IntlDateFormatter::NONE;
            $timeType = $this->_dateFormats[$format];
            $timeZone = $this->timeZone;
        } */else {
            throw new InvalidConfigException('Unknown validation type set for DateValidator::$type: ' . $this->type);
        }

        $formatter = new IntlDateFormatter($this->locale, $dateType, $timeType, $timeZone);

        return $formatter;
    }

    /**
     * Parses a date value using the DateTime::createFromFormat().
     * @param string $value string representing date
     * @param string $format the expected date format
     * @return int|bool a UNIX timestamp or `false` on failure.
     */
    private function parseDateValuePHP($value, $format)
    {
        // if no time was provided in the format string set time to 0 to get a simple date timestamp
        $hasTimeInfo = (strpbrk($format, 'HhGgisU') !== false);

        $date = DateTime::createFromFormat($format, $value, new \DateTimeZone($hasTimeInfo ? $this->timeZone : 'UTC'));
        $errors = DateTime::getLastErrors();
        if ($date === false || $errors['error_count'] || $errors['warning_count'] || ($this->strictDateFormat && $date->format($format) !== $value)) {
            return false;
        }

        if (!$hasTimeInfo) {
            $date->setTime(0, 0, 0);
        }

        return $date->getTimestamp();
    }

    /**
     * Formats a timestamp using the specified format.
     * @param int $timestamp
     * @param string $format
     * @return string
     * @throws Exception
     */
    private function formatTimestamp($timestamp, $format)
    {
        if (strncmp($format, 'php:', 4) === 0) {
            $format = substr($format, 4);
        } else {
            $format = FormatConverter::convertDateIcuToPhp($format, 'date');
        }

        $date = new DateTime();
        $date->setTimestamp($timestamp);
//        $date->setTimezone(new \DateTimeZone($this->timestampAttributeTimeZone));
        return $date->format($format);
    }
}