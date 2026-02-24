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

        if ($slug_disabled) {
            // Use a hidden slug that won't conflict, with rules placed at bottom priority
            $cpt_args['rewrite'] = ['slug' => 'leadership-profile', 'with_front' => false];
            register_post_type('leadership', $cpt_args);

            // Override the permalink to remove the slug prefix
            add_filter('post_type_link', [__CLASS__, 'remove_slug_from_permalink'], 10, 2);
            // Intercept pagename requests and resolve to leadership if matching
            add_filter('request', [__CLASS__, 'resolve_root_leadership_request'], 10, 1);
            // Prevent canonical redirect from /slug/ to /leadership-profile/slug/
            add_filter('redirect_canonical', [__CLASS__, 'prevent_leadership_canonical_redirect'], 10, 2);
        } else {
            $cpt_args['rewrite'] = ['slug' => 'leadership', 'with_front' => false];
            register_post_type('leadership', $cpt_args);
        }
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

        // Visual settings — per breakpoint (desktop / tablet / mobile)
        $breakpoints = ['', '_tablet', '_mobile'];
        $ratio_defaults  = ['2/1', '3/2', '1/1'];
        $height_defaults = [250, 200, 180];

        foreach ($breakpoints as $i => $suffix) {
            register_setting('bp_leadership_settings', 'bp_leadership_story_ratio' . $suffix, [
                'type'              => 'string',
                'default'           => $ratio_defaults[$i],
                'sanitize_callback' => 'sanitize_text_field',
            ]);
            register_setting('bp_leadership_settings', 'bp_leadership_featured_ratio' . $suffix, [
                'type'              => 'string',
                'default'           => $ratio_defaults[$i],
                'sanitize_callback' => 'sanitize_text_field',
            ]);
            register_setting('bp_leadership_settings', 'bp_leadership_story_height' . $suffix, [
                'type'              => 'integer',
                'default'           => $height_defaults[$i],
                'sanitize_callback' => 'absint',
            ]);
            register_setting('bp_leadership_settings', 'bp_leadership_featured_height' . $suffix, [
                'type'              => 'integer',
                'default'           => $height_defaults[$i],
                'sanitize_callback' => 'absint',
            ]);
            register_setting('bp_leadership_settings', 'bp_leadership_image_ratio' . $suffix, [
                'type'              => 'string',
                'default'           => $ratio_defaults[$i],
                'sanitize_callback' => 'sanitize_text_field',
            ]);
            register_setting('bp_leadership_settings', 'bp_leadership_image_height' . $suffix, [
                'type'              => 'integer',
                'default'           => $height_defaults[$i],
                'sanitize_callback' => 'absint',
            ]);
        }

        // Toggles (shared across breakpoints)
        register_setting('bp_leadership_settings', 'bp_leadership_use_height', [
            'type'              => 'string',
            'default'           => 'no',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        register_setting('bp_leadership_settings', 'bp_leadership_image_use_height', [
            'type'              => 'string',
            'default'           => 'no',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        // Flush rewrite rules when the option changes
        if (isset($_GET['settings-updated']) && $_GET['page'] === 'leadership-settings') {
            flush_rewrite_rules();
        }
    }

    public static function render_settings_page() {
        $use_height       = get_option('bp_leadership_use_height', 'no');
        $image_use_height = get_option('bp_leadership_image_use_height', 'no');

        $breakpoints = [
            'desktop' => ['suffix' => '',        'label' => __('Desktop', 'bp-leadership'), 'icon' => 'dashicons-desktop',    'ratio_default' => '2/1', 'height_default' => 250],
            'tablet'  => ['suffix' => '_tablet',  'label' => __('Tablet', 'bp-leadership'),  'icon' => 'dashicons-tablet',     'ratio_default' => '3/2', 'height_default' => 200],
            'mobile'  => ['suffix' => '_mobile',  'label' => __('Mobile', 'bp-leadership'),  'icon' => 'dashicons-smartphone', 'ratio_default' => '1/1', 'height_default' => 180],
        ];
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

                <h2 class="title"><?php _e('Visual', 'bp-leadership'); ?></h2>

                <!-- Responsive tabs -->
                <div class="bp-leadership-tabs">
                    <nav class="bp-leadership-tabs__nav">
                        <?php foreach ($breakpoints as $key => $bp) : ?>
                            <button type="button" class="bp-leadership-tabs__tab <?php echo $key === 'desktop' ? 'is-active' : ''; ?>" data-tab="<?php echo esc_attr($key); ?>">
                                <span class="dashicons <?php echo esc_attr($bp['icon']); ?>"></span>
                                <?php echo esc_html($bp['label']); ?>
                            </button>
                        <?php endforeach; ?>
                    </nav>

                    <?php foreach ($breakpoints as $key => $bp) :
                        $s = $bp['suffix'];
                        $rd = $bp['ratio_default'];
                        $hd = $bp['height_default'];

                        $story_ratio     = get_option('bp_leadership_story_ratio' . $s, $rd);
                        $featured_ratio  = get_option('bp_leadership_featured_ratio' . $s, $rd);
                        $story_height    = get_option('bp_leadership_story_height' . $s, $hd);
                        $featured_height = get_option('bp_leadership_featured_height' . $s, $hd);
                        $image_ratio     = get_option('bp_leadership_image_ratio' . $s, $rd);
                        $image_height    = get_option('bp_leadership_image_height' . $s, $hd);

                        $var_suffix = $s ? str_replace('_', '-', $s) : '';
                    ?>
                    <div class="bp-leadership-tabs__panel <?php echo $key === 'desktop' ? 'is-active' : ''; ?>" data-panel="<?php echo esc_attr($key); ?>">

                        <!-- Stories -->
                        <h3><?php _e('Stories', 'bp-leadership'); ?></h3>

                        <?php if ($key === 'desktop') : ?>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Use Height Instead of Aspect Ratio', 'bp-leadership'); ?></th>
                                    <td>
                                        <label class="bp-leadership-toggle">
                                            <input type="hidden" name="bp_leadership_use_height" value="no">
                                            <input type="checkbox" name="bp_leadership_use_height" value="yes" data-toggle-target="stories" <?php checked($use_height, 'yes'); ?>>
                                            <span class="bp-leadership-toggle__slider"></span>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        <?php endif; ?>

                        <table class="form-table bp-leadership-ratio-fields" data-toggle-group="stories" style="<?php echo $use_height === 'yes' ? 'display:none' : ''; ?>">
                            <tr>
                                <th scope="row">
                                    <label for="bp_leadership_story_ratio<?php echo esc_attr($s); ?>"><?php _e('Aspect Ratio — Every Story', 'bp-leadership'); ?></label>
                                    <code class="bp-leadership-var-name">--bp-leadership-story-ratio<?php echo esc_html($var_suffix); ?></code>
                                </th>
                                <td><input type="text" id="bp_leadership_story_ratio<?php echo esc_attr($s); ?>" name="bp_leadership_story_ratio<?php echo esc_attr($s); ?>" value="<?php echo esc_attr($story_ratio); ?>" placeholder="<?php echo esc_attr($rd); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="bp_leadership_featured_ratio<?php echo esc_attr($s); ?>"><?php _e('Aspect Ratio — Featured Story', 'bp-leadership'); ?></label>
                                    <code class="bp-leadership-var-name">--bp-leadership-featured-story-ratio<?php echo esc_html($var_suffix); ?></code>
                                </th>
                                <td><input type="text" id="bp_leadership_featured_ratio<?php echo esc_attr($s); ?>" name="bp_leadership_featured_ratio<?php echo esc_attr($s); ?>" value="<?php echo esc_attr($featured_ratio); ?>" placeholder="<?php echo esc_attr($rd); ?>" class="regular-text"></td>
                            </tr>
                        </table>

                        <table class="form-table bp-leadership-height-fields" data-toggle-group="stories" style="<?php echo $use_height !== 'yes' ? 'display:none' : ''; ?>">
                            <tr>
                                <th scope="row">
                                    <label for="bp_leadership_story_height<?php echo esc_attr($s); ?>"><?php _e('Height — Every Story', 'bp-leadership'); ?></label>
                                    <code class="bp-leadership-var-name">--bp-leadership-story-height<?php echo esc_html($var_suffix); ?></code>
                                </th>
                                <td>
                                    <div class="bp-leadership-range">
                                        <input type="range" id="bp_leadership_story_height<?php echo esc_attr($s); ?>" name="bp_leadership_story_height<?php echo esc_attr($s); ?>" min="100" max="600" step="10" value="<?php echo esc_attr($story_height); ?>">
                                        <span class="bp-leadership-range__value"><?php echo esc_html($story_height); ?>px</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="bp_leadership_featured_height<?php echo esc_attr($s); ?>"><?php _e('Height — Featured Story', 'bp-leadership'); ?></label>
                                    <code class="bp-leadership-var-name">--bp-leadership-featured-story-height<?php echo esc_html($var_suffix); ?></code>
                                </th>
                                <td>
                                    <div class="bp-leadership-range">
                                        <input type="range" id="bp_leadership_featured_height<?php echo esc_attr($s); ?>" name="bp_leadership_featured_height<?php echo esc_attr($s); ?>" min="100" max="600" step="10" value="<?php echo esc_attr($featured_height); ?>">
                                        <span class="bp-leadership-range__value"><?php echo esc_html($featured_height); ?>px</span>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- Featured Image -->
                        <h3><?php _e('Featured Image', 'bp-leadership'); ?></h3>

                        <?php if ($key === 'desktop') : ?>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Use Height Instead of Aspect Ratio', 'bp-leadership'); ?></th>
                                    <td>
                                        <label class="bp-leadership-toggle">
                                            <input type="hidden" name="bp_leadership_image_use_height" value="no">
                                            <input type="checkbox" name="bp_leadership_image_use_height" value="yes" data-toggle-target="image" <?php checked($image_use_height, 'yes'); ?>>
                                            <span class="bp-leadership-toggle__slider"></span>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        <?php endif; ?>

                        <table class="form-table bp-leadership-ratio-fields" data-toggle-group="image" style="<?php echo $image_use_height === 'yes' ? 'display:none' : ''; ?>">
                            <tr>
                                <th scope="row">
                                    <label for="bp_leadership_image_ratio<?php echo esc_attr($s); ?>"><?php _e('Featured Image Aspect Ratio', 'bp-leadership'); ?></label>
                                    <code class="bp-leadership-var-name">--bp-leadership-image-ratio<?php echo esc_html($var_suffix); ?></code>
                                </th>
                                <td><input type="text" id="bp_leadership_image_ratio<?php echo esc_attr($s); ?>" name="bp_leadership_image_ratio<?php echo esc_attr($s); ?>" value="<?php echo esc_attr($image_ratio); ?>" placeholder="<?php echo esc_attr($rd); ?>" class="regular-text"></td>
                            </tr>
                        </table>

                        <table class="form-table bp-leadership-height-fields" data-toggle-group="image" style="<?php echo $image_use_height !== 'yes' ? 'display:none' : ''; ?>">
                            <tr>
                                <th scope="row">
                                    <label for="bp_leadership_image_height<?php echo esc_attr($s); ?>"><?php _e('Featured Image Height', 'bp-leadership'); ?></label>
                                    <code class="bp-leadership-var-name">--bp-leadership-image-height<?php echo esc_html($var_suffix); ?></code>
                                </th>
                                <td>
                                    <div class="bp-leadership-range">
                                        <input type="range" id="bp_leadership_image_height<?php echo esc_attr($s); ?>" name="bp_leadership_image_height<?php echo esc_attr($s); ?>" min="100" max="600" step="10" value="<?php echo esc_attr($image_height); ?>">
                                        <span class="bp-leadership-range__value"><?php echo esc_html($image_height); ?>px</span>
                                    </div>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <?php endforeach; ?>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <style>
            /* Tabs */
            .bp-leadership-tabs {
                margin-top: 12px;
                max-width: 800px;
            }
            .bp-leadership-tabs__nav {
                display: flex;
                gap: 0;
                border-bottom: 1px solid #c3c4c7;
                margin-bottom: 0;
            }
            .bp-leadership-tabs__tab {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 10px 18px;
                border: 1px solid transparent;
                border-bottom: none;
                background: none;
                cursor: pointer;
                font-size: 13px;
                font-weight: 500;
                color: #50575e;
                margin-bottom: -1px;
                border-radius: 4px 4px 0 0;
                transition: color 0.15s, background 0.15s;
            }
            .bp-leadership-tabs__tab:hover {
                color: #1d2327;
                background: #f0f0f1;
            }
            .bp-leadership-tabs__tab.is-active {
                background: #fff;
                border-color: #c3c4c7;
                color: #1d2327;
                font-weight: 600;
            }
            .bp-leadership-tabs__tab .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
                line-height: 16px;
            }
            .bp-leadership-tabs__panel {
                display: none;
                background: #fff;
                border: 1px solid #c3c4c7;
                border-top: none;
                padding: 0 20px 10px;
                border-radius: 0 0 4px 4px;
            }
            .bp-leadership-tabs__panel.is-active {
                display: block;
            }
            .bp-leadership-tabs__panel h3 {
                margin: 20px 0 4px;
                padding-bottom: 6px;
                border-bottom: 1px solid #f0f0f1;
                font-size: 14px;
            }
            .bp-leadership-tabs__panel .form-table {
                margin-top: 0;
            }
            .bp-leadership-tabs__panel .form-table th {
                padding-top: 12px;
                padding-bottom: 12px;
                width: 280px;
            }

            /* CSS variable name badge */
            .bp-leadership-var-name {
                display: block;
                margin-top: 4px;
                font-size: 11px;
                color: #8c8f94;
                background: #f6f7f7;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: Consolas, Monaco, monospace;
                font-weight: 400;
            }

            /* Toggle */
            .bp-leadership-toggle {
                display: inline-flex;
                align-items: center;
                cursor: pointer;
            }
            .bp-leadership-toggle input[type="checkbox"] {
                display: none;
            }
            .bp-leadership-toggle__slider {
                position: relative;
                width: 40px;
                height: 22px;
                background: #ccd0d4;
                border-radius: 11px;
                transition: background 0.2s;
            }
            .bp-leadership-toggle__slider::after {
                content: '';
                position: absolute;
                top: 3px;
                left: 3px;
                width: 16px;
                height: 16px;
                background: #fff;
                border-radius: 50%;
                transition: transform 0.2s;
                box-shadow: 0 1px 3px rgba(0,0,0,0.15);
            }
            .bp-leadership-toggle input:checked + .bp-leadership-toggle__slider {
                background: #2271b1;
            }
            .bp-leadership-toggle input:checked + .bp-leadership-toggle__slider::after {
                transform: translateX(18px);
            }

            /* Range slider */
            .bp-leadership-range {
                display: flex;
                align-items: center;
                gap: 12px;
                max-width: 400px;
            }
            .bp-leadership-range input[type="range"] {
                flex: 1;
            }
            .bp-leadership-range__value {
                min-width: 50px;
                font-weight: 600;
                font-variant-numeric: tabular-nums;
            }
        </style>

        <script>
        (function() {
            // Tab switching
            document.querySelectorAll('.bp-leadership-tabs__tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var target = this.getAttribute('data-tab');
                    this.closest('.bp-leadership-tabs__nav').querySelectorAll('.bp-leadership-tabs__tab').forEach(function(t) { t.classList.remove('is-active'); });
                    this.classList.add('is-active');
                    this.closest('.bp-leadership-tabs').querySelectorAll('.bp-leadership-tabs__panel').forEach(function(p) {
                        p.classList.toggle('is-active', p.getAttribute('data-panel') === target);
                    });
                });
            });

            // Toggle ratio/height across ALL panels
            document.querySelectorAll('[data-toggle-target]').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    var group = this.getAttribute('data-toggle-target');
                    document.querySelectorAll('.bp-leadership-ratio-fields[data-toggle-group="' + group + '"]').forEach(function(el) {
                        el.style.display = toggle.checked ? 'none' : '';
                    });
                    document.querySelectorAll('.bp-leadership-height-fields[data-toggle-group="' + group + '"]').forEach(function(el) {
                        el.style.display = toggle.checked ? '' : 'none';
                    });
                });
            });

            // Range value display
            document.querySelectorAll('.bp-leadership-range input[type="range"]').forEach(function(range) {
                var val = range.parentElement.querySelector('.bp-leadership-range__value');
                range.addEventListener('input', function() {
                    val.textContent = this.value + 'px';
                });
            });
        })();
        </script>
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

    /**
     * Remove slug prefix from leadership permalinks when slug is disabled.
     * Turns /leadership/john-doe/ into /john-doe/
     */
    public static function remove_slug_from_permalink($post_link, $post) {
        if ($post->post_type === 'leadership' && $post->post_status === 'publish') {
            return home_url('/' . $post->post_name . '/');
        }
        return $post_link;
    }

    /**
     * Intercept request query vars before WP_Query runs.
     * If a root-level pagename doesn't match an existing page but matches
     * a leadership post, rewrite the query vars to load that leadership post.
     * This fires before canonical redirect, preventing false 301s.
     */
    public static function resolve_root_leadership_request($query_vars) {
        if (is_admin()) {
            return $query_vars;
        }

        // Try to get slug from pagename query var first, fall back to REQUEST_URI
        $slug = '';
        if (!empty($query_vars['pagename'])) {
            $slug = $query_vars['pagename'];
        } elseif (!empty($query_vars['error'])) {
            // WordPress couldn't match any rewrite rule - extract slug from URI
            $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
            // Get path relative to home (handles subdirectory installs)
            $home_path = trim(parse_url(home_url(), PHP_URL_PATH) ?: '', '/');
            if ($home_path && strpos($path, $home_path) === 0) {
                $path = trim(substr($path, strlen($home_path)), '/');
            }
            $slug = $path;
        }

        if (empty($slug)) {
            return $query_vars;
        }

        // Don't interfere with nested slugs (subpages like parent/child)
        if (strpos($slug, '/') !== false) {
            return $query_vars;
        }

        // Check if a page with this slug exists - if so, let WordPress handle it
        $page = get_page_by_path($slug);
        if ($page) {
            return $query_vars;
        }

        // Check if a leadership post with this slug exists
        global $wpdb;
        $leadership_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'leadership' AND post_status = 'publish' LIMIT 1",
            $slug
        ));

        if ($leadership_id) {
            $query_vars = [
                'post_type'  => 'leadership',
                'leadership' => $slug,
                'name'       => $slug,
            ];
        }

        return $query_vars;
    }

    /**
     * Prevent canonical redirect from /slug/ to /leadership-profile/slug/
     */
    public static function prevent_leadership_canonical_redirect($redirect_url, $requested_url) {
        if (is_singular('leadership')) {
            return false;
        }
        return $redirect_url;
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
