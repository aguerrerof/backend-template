<?php

namespace App\Helpers;

if (! function_exists('splitWords')) {
    function splitWords($text)
    {
        $result = preg_replace('/(?<!^)([A-Z])/', ' $1', $text);
        return trim($result);
    }
}
