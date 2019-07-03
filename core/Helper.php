<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-30
 * Time: 16:53
 */

if (!function_exists('app_path')) {
    /**
     * Get the application path.
     *
     * @param  string $path
     * @return string
     */
    function app_path($path = '')
    {
        return BASE_PATH . 'app' . ($path ? "/{$path}" : '');
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the storage  path.
     *
     * @param  string $path
     * @return string
     */
    function storage_path($path = '')
    {
        return BASE_PATH . 'storage' . ($path ? "/{$path}" : '');
    }
}

if (!function_exists('cache_path')) {
    /**
     * Get the cache path.
     *
     * @param  string $path
     * @return string
     */
    function cache_path($path = '')
    {
        return storage_path('cache') . ($path ? "/{$path}" : '');
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return BASE_PATH . 'config' . ($path ? "/{$path}" : '');
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return value($default);
        }
        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'empty':
                return '';
            case 'null':
                return null;
        }
        return $value;
    }
}

/**
 * json output
 * @param int $code 0正常，其他不正常
 * @param null $msg
 * @param null $data
 * @param null $extra
 */
function output($code, $msg = null, $data = null, $extra = null)
{
    @header('Content-Type:application/json;charset=UTF-8');
    $output = array(
        'code' => $code,
        'msg'  => $msg,
        'data' => $data
    );
    if (is_array($extra)) {
        foreach ($extra as $key => $val) {
            $output[$key] = $val;
        }
    }
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    die;
}