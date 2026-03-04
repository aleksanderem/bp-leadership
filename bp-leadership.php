<?php
/**
 * Plugin Name: BP Leadership
 * Description: Featured Stories and Leadership custom post types with ACF fields
 * Version: 1.3.0
 * Author: Alex M.
 * Text Domain: bp-leadership
 */

defined('ABSPATH') || exit;

define('BP_LEADERSHIP_VERSION', '1.3.0');
define('BP_LEADERSHIP_PATH', plugin_dir_path(__FILE__));
define('BP_LEADERSHIP_URL', plugin_dir_url(__FILE__));

require_once BP_LEADERSHIP_PATH . 'includes/class-cpt-featured-stories.php';
require_once BP_LEADERSHIP_PATH . 'includes/class-cpt-leadership.php';
require_once BP_LEADERSHIP_PATH . 'includes/class-acf-fields.php';
require_once BP_LEADERSHIP_PATH . 'includes/class-starter-addon.php';
require_once BP_LEADERSHIP_PATH . 'includes/class-elementor-queries.php';

// Load textdomain early to avoid WP 6.7+ notice
add_action('init', function() {
    load_plugin_textdomain('bp-leadership', false, dirname(plugin_basename(__FILE__)) . '/languages');
}, 0);

// Register CPTs
add_action('init', ['BP_Leadership_CPT_Featured_Stories', 'register']);
add_action('init', ['BP_Leadership_CPT_Leadership', 'register']);

// Register ACF fields
add_action('acf/init', ['BP_Leadership_ACF_Fields', 'register']);

// Register Elementor custom queries
BP_Leadership_Elementor_Queries::init();

// Register as Starter Dashboard addon (priority 5, before addon loader)
add_filter('starter_register_external_addons', ['BP_Leadership_Starter_Addon', 'register'], 5);

// Initialize addon settings hooks (save handler + CSS variables)
BP_Leadership_Starter_Addon::init();

// Tag the assigned_stories loop grid widget with a custom CSS class
add_action('elementor/frontend/widget/before_render', function($widget) {
    if ($widget->get_name() !== 'loop-grid') return;
    $settings = $widget->get_settings_for_display();
    if (($settings['post_query_query_id'] ?? '') === 'assigned_stories') {
        $widget->add_render_attribute('_wrapper', 'class', 'bp-stories-grid');
    }
});

// Per-leader stories grid columns override (ACF → Elementor Loop Grid)
add_action('wp_head', function() {
    if (!is_singular('leadership')) return;

    $post_id = get_the_ID();
    $desktop = get_field('grid_cols_desktop', $post_id) ?: '3';
    $tablet  = get_field('grid_cols_tablet', $post_id)  ?: 'inherit';
    $mobile  = get_field('grid_cols_mobile', $post_id)  ?: 'inherit';

    if ($tablet === 'inherit') $tablet = $desktop;
    if ($mobile === 'inherit') $mobile = $tablet;

    $scope = 'body.postid-' . $post_id;
    $grid  = '.bp-stories-grid .elementor-loop-container.elementor-grid';

    $css = "$scope $grid { grid-template-columns: repeat($desktop, 1fr) !important; }";

    if ($tablet !== $desktop) {
        $css .= "\n@media (max-width: 1024px) { $scope $grid { grid-template-columns: repeat($tablet, 1fr) !important; } }";
    }
    if ($mobile !== $tablet) {
        $css .= "\n@media (max-width: 767px) { $scope $grid { grid-template-columns: repeat($mobile, 1fr) !important; } }";
    }

    echo '<style id="bp-leadership-grid-cols">' . $css . '</style>' . "\n";
}, 99);

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
