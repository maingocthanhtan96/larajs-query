<?php

if (!function_exists('isNullString')) {
    function isNullString($value): bool
    {
        return $value === 'null';
    }
}

if (!function_exists('isBooleanString')) {
    function isBooleanString($value): bool
    {
        return in_array($value, ['true', 'false']);
    }
}

if (!function_exists('isNumberString')) {
    function isNumberString($value): bool
    {
        return is_string($value) && strlen(trim($value)) > 0 && is_numeric($value);
    }
}

if (!function_exists('isDateString')) {
    function isDateString($value): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }
}

if (!function_exists('wildCardString')) {
    function wildCardString($value, $operator = null): string
    {
        return match ($operator) {
            'contains' => '%' . $value . '%',
            'startsWith' => $value . '%',
            'endsWith' => '%' . $value,
            default => $value,
        };
    }
}

if (!function_exists('removeHashFromString')) {
    function removeHashFromString($str): string
    {
        return str_replace('#', '', $str);
    }
}

if (!function_exists('convertToOrFormat')) {
    function convertToOrFormat($str): string
    {
        $capStr = ucfirst($str);

        return "or$capStr";
    }
}

/**
 * @param  string  $direction
 * @return string
 */
if (!function_exists('convert_direction')) {
    function convert_direction(string $direction = 'asc'): string
    {
        return in_array($direction, ['ascending', 'asc']) ? 'asc' : 'desc';
    }
}
