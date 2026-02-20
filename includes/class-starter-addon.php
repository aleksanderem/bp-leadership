<?php
/**
 * Starter Dashboard Addon Integration
 */

defined('ABSPATH') || exit;

class BP_Leadership_Starter_Addon {

    public static function register($addons) {
        $addons['bp-leadership'] = [
            'name'        => __('BP Leadership', 'bp-leadership'),
            'description' => __('Featured Stories and Leadership custom post types', 'bp-leadership'),
            'icon'        => 'user-multiple-02',
            'category'    => 'integration',
            'file'        => __FILE__,
            'has_settings' => false,
            'version'     => BP_LEADERSHIP_VERSION,
            'plugin_file' => BP_LEADERSHIP_PATH . 'bp-leadership.php',
        ];

        return $addons;
    }
}
