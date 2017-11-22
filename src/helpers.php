<?php

if (!function_exists('isDevelopment')) {
    /**
     * @return bool
     */
    function isDevelopment()
    {
        return in_array(config('app.env'), ['local', 'testing']);
    }
}

if (!function_exists('isTesting')) {
    /**
     * @return bool
     */
    function isTesting()
    {
        return config('app.env') === 'testing';
    }
}

if (!function_exists('hashing')) {
    /**
     * @param $string
     * @return string
     */
    function hashing($string)
    {
        return app('hash')->make($string);
    }
}

if (!function_exists('randomHashing')) {
    /**
     * @param int $length
     * @return string
     */
    function randomHashing($length = 64)
    {
        return hashing(str_random($length));
    }
}

if (!function_exists('shaN')) {
    /**
     * @param $string
     * @return string
     */
    function shaN($string)
    {
        return hash('sha256', $string);
    }
}

if (!function_exists('pretty')) {
    /**
     * @param mixed $var
     * @return string
     */
    function pretty($var)
    {
        return print_r($var, 1);
    }
}

if (!function_exists('now')) {
    /**
     * @return int
     */
    function now()
    {
        return (new \Carbon\Carbon())->toDateTimeString();
    }
}

if (!function_exists('method')) {
    /**
     * @param string $property
     * @return string
     */
    function method($property)
    {
        return (strpos($property, '_') !== false) ? camel_case($property) : $property;
    }
}

if (!function_exists('setter')) {
    /**
     * @param string $property
     * @return string
     */
    function setter($property)
    {
        return method($property);
    }
}

if (!function_exists('getter')) {
    /**
     * @param string $property
     * @return string
     */
    function getter($property)
    {
        return method($property);
    }
}

if (!function_exists('ff')) {
    /**
     * @param array ...$attributes
     * @return mixed
     */
    function ff(...$attributes)
    {
        if (isDevelopment()) {
            $log = '';
            foreach ($attributes as $i => $attribute) {
                $log .= print_r($attribute, true) . "\n";
            }
            $log .= (
            env('APP_DEBUG_TRACE')
                ? print_r(customizeTrace((new Exception())->getTrace(), env('APP_DEBUG_TRACE_DEEP', 1)), true)
                : '');
            Illuminate\Support\Facades\Log::info($log . "\n");
        }

        return $attributes[0];
    }
}

if (!function_exists('passwordIsValid')) {
    /**
     * @param $actual
     * @param $expected
     * @return bool
     */
    function passwordIsValid($actual, $expected)
    {
        return Illuminate\Support\Facades\Hash::check($actual, $expected);
    }
}

if (!function_exists('apiTokenIsValid')) {
    /**
     * @param $apiToken
     * @return bool
     */
    function apiTokenIsValid($apiToken)
    {
        return base64_decode($apiToken, true);
    }
}

if (!function_exists('filterArray')) {
    /**
     * @param $array
     * @return array
     */
    function filterArray($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = filterArray($value);
            }
        }

        return array_filter($array, function ($item) {
            return !empty($item) || $item === false || $item === 0;
        });
    }
}

if (!function_exists('resource')) {
    /**
     * @param mixed $app
     * @param string $route
     * @param string $controller
     * @param string $alias
     */
    function resource($app, $route, $controller, $alias = null)
    {
        $alias = $alias ?: $route;

        $app->get($route, ['as' => "api.{$alias}.show", 'uses' => "{$controller}@index"]);
        $app->post($route, ['as' => "api.{$alias}.create", 'uses' => "{$controller}@store"]);
        $app->get("{$route}/{id}", ['as' => "api.{$alias}.read", 'uses' => "{$controller}@show"]);
        $app->put("{$route}/{id}", ['as' => "api.{$alias}.update", 'uses' => "{$controller}@update"]);
        $app->patch("{$route}/{id}", ['as' => "api.{$alias}.patch", 'uses' => "{$controller}@update"]);
        $app->delete("{$route}/{id}", ['as' => "api.{$alias}.delete", 'uses' => "{$controller}@destroy"]);
    }
}

if (!function_exists('responseJson')) {
    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return string
     */
    function responseJson($content = '', $status = 200, array $headers = [])
    {
        $status = is_null($content) ? \Illuminate\Http\Response::HTTP_NO_CONTENT : $status;
        $options = env('APP_JSON_PRETTY_PRINT') === true ? JSON_PRETTY_PRINT : 0;

        return response()->json($content, $status, $headers, $options);
    }
}

if (!function_exists('responseJsonApi')) {
    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    function responseJsonApi($content = '', $status = 200, array $headers = [])
    {
        $status = is_null($content) ? \Illuminate\Http\Response::HTTP_NO_CONTENT : $status;
        $headers = array_merge([
            'Content-Type' => \FreddieGar\Base\Middleware\SupportedMediaTypeMiddleware::MEDIA_TYPE_SUPPORTED
        ], $headers);

        return response($content, $status, $headers);
    }
}

if (!function_exists('responseJsonApiError')) {
    /**
     * @param array $response
     * @param array $headers
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    function responseJsonApiError(array $response, array $headers = [])
    {
        $errors = new Neomerx\JsonApi\Exceptions\ErrorCollection();

        $response['meta'] = isset($response['meta']) ? $response['meta'] : null;

        if (is_array($response['detail'])) {
            foreach ($response['detail'] as $idx => $detail) {
                $errors->addDataError($response['title'], $detail[0], $response['status'], $idx, null, null, $response['meta']);
            }
        } else {
            $errors->addDataError($response['title'], $response['detail'], $response['status'], null, null, null, $response['meta']);
        }

        $encoder = Neomerx\JsonApi\Encoder\Encoder::instance([], encoderOptions());

        return responseJsonApi($encoder->encodeErrors($errors), $response['status'], $headers);
    }
}

if (!function_exists('encoderOptions')) {
    /**
     * @return \Neomerx\JsonApi\Encoder\EncoderOptions
     */
    function encoderOptions()
    {
        $options = env('APP_JSON_PRETTY_PRINT') === true ? JSON_PRETTY_PRINT : 0;

        return new Neomerx\JsonApi\Encoder\EncoderOptions($options, route('/'));
    }
}

if (!function_exists('customizeTrace')) {
    /**
     * @param array $exceptions
     * @param null $deep
     * @return array
     */
    function customizeTrace(array $exceptions, $deep = null)
    {
        $i = 1;
        $trace = [];
        $function = '';

        foreach ($exceptions as $index => $exception) {
            if (!isset($exception['file']) || strpos($exception['file'], '/vendor/') !== false) {
                continue;
            }
            if ($deep && $i > $deep) {
                break;
            }
            if (isset($exceptions[$index + 1])) {
                $function = $exceptions[$index + 1]['function'];
            }
            $trace[] = sprintf('%s:%d %s', $exception['file'], $exception['line'], $function);
            ++$i;
        }

        return $trace;
    }
}

if (!function_exists('makeTagNameCache')) {
    /**
     * @param array $filters
     * @return string
     */
    function makeTagNameCache(array $filters)
    {
        return shaN(json_encode($filters, 0));
    }
}