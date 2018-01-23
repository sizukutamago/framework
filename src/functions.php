<?php
/**
 * Created by PhpStorm.
 * User: sizukutamago
 * Date: 2017/07/28
 * Time: 14:55
 */

/**
 * @param string $key
 * @param string $default
 * @return string
 */
if (!function_exists('env')) {

    function env(string $key, $default = ''): string
    {
        if (getenv($key)) {
            return getenv($key);
        }

        return $default;
    }

}
