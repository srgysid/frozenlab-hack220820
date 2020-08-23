<?php
namespace common\helpers;

use Yii;

class MathHelper
{
    public static function getFloatValue($strValue)
    {
        $retVal = 0;
        if ($strValue) {
            $pos_d = strpos($strValue, '/');
            if (!$pos_d === false) {
                $numerator = (int)trim(substr($strValue, 0,$pos_d));
                $denominator = (int)trim(substr($strValue, $pos_d + 1));
                if (is_int($denominator) && ($denominator!=0)){
                    $retVal = round($numerator/$denominator, 4);
                    return $retVal;
                }
            }
            $pos_p = strpos($strValue, '%');
            if (!$pos_p === false) {
                $numerator = (float)trim(substr($strValue, 0,$pos_p));
                $retVal = round($numerator/100, 4);
                return $retVal;
            }
            if (($pos_d === false) && ($pos_p === false)) {
                return (float)$strValue;
            }
        }
        return $retVal;
    }

}