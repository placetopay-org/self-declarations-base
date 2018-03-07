<?php

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
        if (env('APP_DEBUG')) {
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