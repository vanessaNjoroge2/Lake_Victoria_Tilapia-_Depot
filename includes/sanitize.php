<?php

/**
 * Sanitize strings to prevent XSS and strip HTML tags
 *
 * @param string $input
 * @return string
 */
function sanitize_string(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize and clean floats/decimals, rounding to two decimal places
 *
 * @param mixed $input
 * @return float
 */
function sanitize_decimal(mixed $input): float {
    return round((float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION), 2);
}

/**
 * Sanitize and clean integer inputs
 *
 * @param mixed $input
 * @return int
 */
function sanitize_int(mixed $input): int {
    return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}
