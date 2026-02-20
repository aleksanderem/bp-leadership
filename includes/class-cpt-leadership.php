<?php
/**
 * Leadership CPT
 */

defined('ABSPATH') || exit;

class BP_Leadership_CPT_Leadership {

    public static function register() {
        add_action('pre_get_posts', [__CLASS__, 'exclude_from_search']);
        add_filter('single_template', [__CLASS__, 'single_template']);
        add_filter('theme_leadership_templates', [__CLASS__, 'register_templates']);
        add_filter('manage_leadership_posts_columns', [__CLASS__, 'admin_columns']);
        add_action('manage_leadership_posts_custom_column', [__CLASS__, 'admin_column_content'], 10, 2);
        add_action('admin_head', [__CLASS__, 'admin_column_styles']);
        add_action('admin_menu', [__CLASS__, 'add_settings_page']);
        add_action('admin_init', [__CLASS__, 'register_settings']);

        $slug_disabled = get_option('bp_leadership_disable_slug', false);

        $cpt_args = [
            'labels' => [
                'name'               => __('Leadership', 'bp-leadership'),
                'singular_name'      => __('Leader', 'bp-leadership'),
                'add_new'            => __('Add New', 'bp-leadership'),
                'add_new_item'       => __('Add New Leader', 'bp-leadership'),
                'edit_item'          => __('Edit Leader', 'bp-leadership'),
                'new_item'           => __('New Leader', 'bp-leadership'),
                'view_item'          => __('View Leader', 'bp-leadership'),
                'search_items'       => __('Search Leadership', 'bp-leadership'),
                'not_found'          => __('No leaders found', 'bp-leadership'),
                'not_found_in_trash' => __('No leaders found in Trash', 'bp-leadership'),
                'all_items'          => __('All Leaders', 'bp-leadership'),
                'menu_name'          => __('Leadership', 'bp-leadership'),
            ],
            'show_ui'      => true,
            'show_in_rest' => true,
            'supports'     => ['title', 'thumbnail', 'page-attributes'],
            'menu_icon'    => 'dashicons-groups',
        ];

        $cpt_args['public']      = true;
        $cpt_args['has_archive'] = !$slug_disabled;
        $cpt_args['rewrite']     = $slug_disabled
            ? ['slug' => '/', 'with_front' => false]
            : ['slug' => 'leadership', 'with_front' => false];

        register_post_type('leadership', $cpt_args);
    }

    public static function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=leadership',
            __('Leadership Settings', 'bp-leadership'),
            __('Settings', 'bp-leadership'),
            'manage_options',
            'leadership-settings',
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function register_settings() {
        register_setting('bp_leadership_settings', 'bp_leadership_disable_slug', [
            'type'              => 'boolean',
            'default'           => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        // Flush rewrite rules when the option changes
        if (isset($_GET['settings-updated']) && $_GET['page'] === 'leadership-settings') {
            flush_rewrite_rules();
        }
    }

    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Leadership Settings', 'bp-leadership'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('bp_leadership_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Disable Public Slug', 'bp-leadership'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="bp_leadership_disable_slug" value="1" <?php checked(get_option('bp_leadership_disable_slug', false)); ?>>
                                <?php _e('Disable /leadership/ archive page (single leader pages will still be accessible)', 'bp-leadership'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function register_templates($templates) {
        $templates['bp-leadership-profile'] = __('Leadership Profile', 'bp-leadership');
        return $templates;
    }

    public static function single_template($template) {
        if (get_post_type() !== 'leadership') {
            return $template;
        }

        $chosen = get_page_template_slug();
        if ($chosen === 'bp-leadership-profile') {
            $plugin_template = BP_LEADERSHIP_PATH . 'templates/single-leadership.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }

        return $template;
    }

    public static function admin_column_styles() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'edit-leadership') {
            echo '<style>.column-photo{width:60px;}</style>';
        }
    }

    public static function admin_columns($columns) {
        $new = [];
        foreach ($columns as $key => $label) {
            if ($key === 'title') {
                $new['photo'] = __('Photo', 'bp-leadership');
            }
            $new[$key] = $label;
        }
        return $new;
    }

    public static function exclude_from_search($query) {
        if (is_admin() || !$query->is_main_query() || !$query->is_search()) {
            return;
        }

        $excluded = get_posts([
            'post_type'      => 'leadership',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_key'       => 'exclude_from_search',
            'meta_value'     => '1',
        ]);

        if (!empty($excluded)) {
            $existing = $query->get('post__not_in') ?: [];
            $query->set('post__not_in', array_merge($existing, $excluded));
        }
    }

    public static function admin_column_content($column, $post_id) {
        if ($column === 'photo') {
            $thumb = get_the_post_thumbnail($post_id, [50, 50], ['style' => 'border-radius:50%;object-fit:cover;']);
            echo $thumb ?: '<span class="dashicons dashicons-admin-users" style="font-size:36px;color:#ccc;"></span>';
        }
    }
}
