<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('root_url')) {
    /**
     * Return base URL of the root monorepo (landing).
     */
    function root_url(string $path = ''): string
    {
        $root = preg_replace('#/(campaign|datahub)/?$#', '', base_url());
        return rtrim($root, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '/');
    }
}

if (!function_exists('get_option')) {
    /**
     * Get system option / configuration flag with fallback.
     */
    function get_option(string $key, $default = null)
    {
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('options')) {
                $row = $db->table('options')->where('option_name', $key)->get()->getRowArray();
                if ($row && isset($row['option_value'])) {
                    return $row['option_value'];
                }
            }
        } catch (\Throwable $e) {
            // Fall back to default
        }
        return $default;
    }
}
