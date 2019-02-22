<?php

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
