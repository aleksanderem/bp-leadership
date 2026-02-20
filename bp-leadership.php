<?php
/**
 * Plugin Name: BP Leadership
 * Description: Featured Stories and Leadership custom post types with ACF fields
 * Version: 1.0.9
 * Author: Alex M.
 * Text Domain: bp-leadership
 */

defined('ABSPATH') || exit;

define('BP_LEADERSHIP_VERSION', '1.0.9');
define('BP_LEADERSHIP_PATH', plugin_dir_path(__FILE__));
define('BP_LEADERSHIP_URL', plugin_dir_url(__FILE__));

require_once BP_LEADERSHIP_PATH . 'includes/class-cpt-featured-stories.php';
require_once BP_LEADERSHIP_PATH . 'includes/class-cpt-leadership.php';
require_once BP_LEADERSHIP_PATH . 'includes/class-acf-fields.php';
require_once BP_LEADERSHIP_PATH . 'includes/class-starter-addon.php';
require_once BP_LEADERSHIP_PATH . 'includes/class-elementor-queries.php';

// Register CPTs
add_action('init', ['BP_Leadership_CPT_Featured_Stories', 'register']);
add_action('init', ['BP_Leadership_CPT_Leadership', 'register']);

// Register ACF fields
add_action('acf/init', ['BP_Leadership_ACF_Fields', 'register']);

// Register Elementor custom queries
BP_Leadership_Elementor_Queries::init();

// Register as Starter Dashboard addon (priority 5, before addon loader)
add_filter('starter_register_external_addons', ['BP_Leadership_Starter_Addon', 'register'], 5);

// GitHub update checker (uses PUC library loaded by Starter Dashboard)
add_action('plugins_loaded', function() {
    if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
        $bp_leadership_updater = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/aleksanderem/bp-leadership/',
            __FILE__,
            'bp-leadership'
        );
        $bp_leadership_updater->setBranch('main');
    }
}, 20);
