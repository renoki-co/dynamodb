<?php

namespace Rennokki\DynamoDb;

class Helper
{
    /**
     * Get the first element of the array.
     *
     * @param  array  $array
     * @param  \Closure|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public static function array_first($array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return static::value($default);
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return static::value($default);
    }

    /**
     * Get the value of the closure or the value itself.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}
