<?php
if (!defined('ABSPATH')) exit;
/**
 * Encrypt a value using AES-256-CBC
 */
function workable_encrypt_key($value) {
    if (empty($value)) return '';

    $key = hash('sha256', wp_salt('auth'));
    // Fixed IV for consistent encryption/decryption
    $iv  = substr(hash('sha256', 'epos_workable_iv'), 0, 16);

    $encrypted = openssl_encrypt($value, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($encrypted);
}

/**
 * Decrypt a value encrypted by workable_encrypt_key()
 */
function workable_decrypt_key($value) {
    if (empty($value)) return '';

    $key = hash('sha256', wp_salt('auth'));
    $iv  = substr(hash('sha256', 'epos_workable_iv'), 0, 16);

    $decoded = base64_decode($value, true);
    if ($decoded === false) return $value;

    $decrypted = openssl_decrypt($decoded, 'AES-256-CBC', $key, 0, $iv);
    return $decrypted !== false ? $decrypted : $value;
}

/**
 * Automatically encrypt value before saving to database
 */
add_filter('pre_update_option_' . WORKABLE_CLIENT_KEY, function ($new_value, $old_value) {
    // Only encrypt if the value has changed
    if ($new_value !== $old_value) {
        return workable_encrypt_key($new_value);
    }
    return $old_value;
}, 10, 2);

/**
 * Automatically decrypt value when retrieved from database
 */
add_filter('option_' . WORKABLE_CLIENT_KEY, function ($value) {
    return workable_decrypt_key($value);
});
