<?php
/**
 * Featured Stories CPT
 */

defined('ABSPATH') || exit;

class BP_Leadership_CPT_Featured_Stories {

    public static function register() {
        register_post_type('featured_story', [
            'labels' => [
                'name'               => __('Featured Stories', 'bp-leadership'),
                'singular_name'      => __('Featured Story', 'bp-leadership'),
                'add_new'            => __('Add New', 'bp-leadership'),
                'add_new_item'       => __('Add New Featured Story', 'bp-leadership'),
                'edit_item'          => __('Edit Featured Story', 'bp-leadership'),
                'new_item'           => __('New Featured Story', 'bp-leadership'),
                'view_item'          => __('View Featured Story', 'bp-leadership'),
                'search_items'       => __('Search Featured Stories', 'bp-leadership'),
                'not_found'          => __('No featured stories found', 'bp-leadership'),
                'not_found_in_trash' => __('No featured stories found in Trash', 'bp-leadership'),
                'all_items'          => __('All Featured Stories', 'bp-leadership'),
                'menu_name'          => __('Featured Stories', 'bp-leadership'),
            ],
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'has_archive'  => false,
            'supports'     => ['title', 'excerpt', 'thumbnail'],
            'menu_icon'    => 'dashicons-star-filled',
            'rewrite'      => false,
        ]);

        add_filter('manage_featured_story_posts_columns', [__CLASS__, 'admin_columns']);
        add_action('manage_featured_story_posts_custom_column', [__CLASS__, 'admin_column_content'], 10, 2);
        add_action('admin_head', [__CLASS__, 'admin_column_styles']);

        add_action('admin_menu', [__CLASS__, 'add_settings_page']);
        add_action('admin_init', [__CLASS__, 'handle_actions']);
    }

    public static function admin_column_styles() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'edit-featured_story') {
            echo '<style>.column-thumb{width:60px;}</style>';
        }
    }

    public static function admin_columns($columns) {
        $new = [];
        foreach ($columns as $key => $label) {
            if ($key === 'title') {
                $new['thumb'] = __('Image', 'bp-leadership');
            }
            $new[$key] = $label;
        }
        return $new;
    }

    public static function admin_column_content($column, $post_id) {
        if ($column === 'thumb') {
            $img = get_the_post_thumbnail($post_id, [50, 50], ['style' => 'border-radius:4px;object-fit:cover;']);
            echo $img ?: '<span class="dashicons dashicons-format-image" style="font-size:36px;color:#ccc;"></span>';
        }
    }

    public static function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=featured_story',
            __('Settings', 'bp-leadership'),
            __('Settings', 'bp-leadership'),
            'manage_options',
            'featured-stories-settings',
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function handle_actions() {
        if (!isset($_POST['featured_stories_action']) || !current_user_can('manage_options')) {
            return;
        }

        if ($_POST['featured_stories_action'] === 'generate') {
            check_admin_referer('featured_stories_generate');
            $count = absint($_POST['story_count'] ?? 10);
            $count = max(1, min($count, 100));
            $created = self::generate_dummy_data($count);
            set_transient('featured_stories_notice', sprintf(
                __('Created %d dummy featured stories.', 'bp-leadership'),
                $created
            ), 30);
            wp_redirect(admin_url('edit.php?post_type=featured_story&page=featured-stories-settings'));
            exit;
        }

        if ($_POST['featured_stories_action'] === 'delete_all') {
            check_admin_referer('featured_stories_delete_all');
            $deleted = self::delete_all_stories();
            set_transient('featured_stories_notice', sprintf(
                __('Deleted %d featured stories.', 'bp-leadership'),
                $deleted
            ), 30);
            wp_redirect(admin_url('edit.php?post_type=featured_story&page=featured-stories-settings'));
            exit;
        }
    }

    public static function render_settings_page() {
        $notice = get_transient('featured_stories_notice');
        if ($notice) {
            delete_transient('featured_stories_notice');
        }

        $total = wp_count_posts('featured_story');
        $count = ($total->publish ?? 0) + ($total->draft ?? 0);
        ?>
        <div class="wrap">
            <h1><?php _e('Featured Stories Settings', 'bp-leadership'); ?></h1>

            <?php if ($notice): ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html($notice); ?></p></div>
            <?php endif; ?>

            <div style="display:flex;gap:24px;margin-top:20px;">

                <div class="card" style="max-width:400px;">
                    <h2><?php _e('Generate Dummy Data', 'bp-leadership'); ?></h2>
                    <p><?php _e('Create sample featured stories with random titles, excerpts and URLs.', 'bp-leadership'); ?></p>
                    <form method="post">
                        <?php wp_nonce_field('featured_stories_generate'); ?>
                        <input type="hidden" name="featured_stories_action" value="generate">
                        <p>
                            <label for="story_count"><?php _e('Number of stories:', 'bp-leadership'); ?></label><br>
                            <input type="number" id="story_count" name="story_count" value="10" min="1" max="100" style="width:80px;">
                        </p>
                        <?php submit_button(__('Generate Stories', 'bp-leadership'), 'primary', 'submit', false); ?>
                    </form>
                </div>

                <div class="card" style="max-width:400px;">
                    <h2><?php _e('Delete All Stories', 'bp-leadership'); ?></h2>
                    <p><?php printf(__('Currently %d featured stories exist.', 'bp-leadership'), $count); ?></p>
                    <form method="post" onsubmit="return confirm('<?php esc_attr_e('Are you sure? This will permanently delete all featured stories.', 'bp-leadership'); ?>');">
                        <?php wp_nonce_field('featured_stories_delete_all'); ?>
                        <input type="hidden" name="featured_stories_action" value="delete_all">
                        <?php submit_button(__('Delete All', 'bp-leadership'), 'delete', 'submit', false); ?>
                    </form>
                </div>

            </div>
        </div>
        <?php
    }

    private static function generate_dummy_data($count) {
        $topics = [
            'Innovation in Healthcare',
            'Sustainable Energy Solutions',
            'Digital Transformation Strategy',
            'Global Supply Chain Resilience',
            'AI-Powered Customer Experience',
            'Future of Remote Work',
            'Cybersecurity Best Practices',
            'Green Building Technologies',
            'Financial Inclusion Initiatives',
            'Smart City Infrastructure',
            'Biotech Breakthroughs',
            'Ocean Conservation Efforts',
            'EdTech Revolution',
            'Space Exploration Milestones',
            'Renewable Energy Investments',
            'Mental Health in the Workplace',
            'Autonomous Vehicle Progress',
            'Blockchain Supply Chain',
            'Climate Action Leadership',
            'Diversity and Inclusion Programs',
        ];

        $domains = [
            'https://example.com/story/',
            'https://news.example.org/article/',
            'https://blog.example.net/post/',
            'https://medium.com/example/',
        ];

        // Google Favicon API - real company icons for dummy logos
        $logo_domains = [
            'google.com',
            'apple.com',
            'microsoft.com',
            'amazon.com',
            'netflix.com',
            'spotify.com',
            'slack.com',
            'dropbox.com',
            'airbnb.com',
            'uber.com',
            'stripe.com',
            'shopify.com',
            'zoom.us',
            'salesforce.com',
            'adobe.com',
            'github.com',
            'twitter.com',
            'linkedin.com',
            'medium.com',
            'reddit.com',
        ];

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $created = 0;
        for ($i = 0; $i < $count; $i++) {
            $title = $topics[array_rand($topics)] . ' ' . wp_rand(100, 999);
            $post_id = wp_insert_post([
                'post_type'    => 'featured_story',
                'post_title'   => $title,
                'post_excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'post_status'  => 'publish',
            ]);

            if (!is_wp_error($post_id)) {
                $url = $domains[array_rand($domains)] . sanitize_title($title);
                update_field('story_url', $url, $post_id);

                // Sideload placeholder image as featured image
                $image_url = 'https://picsum.photos/800/450?random=' . $post_id . '_' . time();
                $tmp_file = download_url($image_url, 30);

                if (!is_wp_error($tmp_file)) {
                    $file_array = [
                        'name'     => sanitize_title($title) . '.jpg',
                        'tmp_name' => $tmp_file,
                    ];

                    $attach_id = media_handle_sideload($file_array, $post_id, $title);

                    if (!is_wp_error($attach_id)) {
                        set_post_thumbnail($post_id, $attach_id);
                    } else {
                        @unlink($tmp_file);
                    }
                }

                // Sideload logo from Google Favicon API
                $logo_domain = $logo_domains[array_rand($logo_domains)];
                $logo_url = 'https://www.google.com/s2/favicons?sz=128&domain=' . $logo_domain;
                $logo_tmp = download_url($logo_url, 15);

                if (!is_wp_error($logo_tmp)) {
                    $logo_file = [
                        'name'     => sanitize_title($logo_domain) . '-logo.png',
                        'tmp_name' => $logo_tmp,
                    ];

                    $logo_attach_id = media_handle_sideload($logo_file, $post_id, $logo_domain . ' logo');

                    if (!is_wp_error($logo_attach_id)) {
                        update_field('story_logo', $logo_attach_id, $post_id);
                    } else {
                        @unlink($logo_tmp);
                    }
                }

                $created++;
            }
        }

        return $created;
    }

    private static function delete_all_stories() {
        $posts = get_posts([
            'post_type'      => 'featured_story',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);

        $deleted = 0;
        foreach ($posts as $post_id) {
            if (wp_delete_post($post_id, true)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
