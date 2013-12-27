<?php

namespace Util;

class String
{
    public static function convertToCamelCase($string)
    {
        return implode('', array_map('ucfirst', explode('_', $string)));
    }

    public static function pluralize($string)
    {
        $index = strlen($string) - 1;
        $last = $string[$index];
        if ($last == 'e') {
            return $string . 's';
        }
        if ($last == 'y') {
            $string[$index] = 'i';

            return $string . 'es';
        }

        return $string . 's';
    }

    public static function formatBytes($size, $precision = 2)
    {
        $base = log($size) / log(1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }
}