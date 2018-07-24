<?php

namespace Application\Utils;

/**
 * Datetime utility
 * User: rizts
 * Date: 01/04/17
 * Time: 09:20
 */

class DatetimeUtil
{

    /**
     * Convert delay value from specific unit to seconds
     *
     * @param $unit = delay unit
     * @param $value = delay value
     * @return time in seconds unit
     */
    public static function convertTo($unit, $value)
    {
        return (in_array($unit,['day', 'hour', 'minute'])?($unit == 'day'?(86400 * $value):($unit == 'hour'?(3600 * $value):(60 * $value))):0);
    }

    /**
     * Convert delay value from seconds to specific unit
     *
     * @param $unit = delay unit
     * @param $value = delay value
     * @return time in seconds unit
     */
    public static function convertFrom($unit, $value)
    {
        return (in_array($unit,['day', 'hour', 'minute'])?($unit == 'day'?($value/86400):($unit == 'hour'?($value/3600):($value/60))):0);
    }
}
