<?php
/**
 * Starter Dashboard Addon Integration
 */

defined('ABSPATH') || exit;

class BP_Leadership_Starter_Addon {

    const OPT_STORY_RATIO       = 'bp_leadership_story_ratio';
    const OPT_FEATURED_RATIO    = 'bp_leadership_featured_ratio';
    const OPT_USE_HEIGHT        = 'bp_leadership_use_height';
    const OPT_STORY_HEIGHT      = 'bp_leadership_story_height';
    const OPT_FEATURED_HEIGHT   = 'bp_leadership_featured_height';
    const OPT_IMAGE_RATIO       = 'bp_leadership_image_ratio';
    const OPT_IMAGE_USE_HEIGHT  = 'bp_leadership_image_use_height';
    const OPT_IMAGE_HEIGHT      = 'bp_leadership_image_height';

    public static function register($addons) {
        $addons['bp-leadership'] = [
            'name'              => __('BP Leadership', 'bp-leadership'),
            'description'       => __('Featured Stories and Leadership custom post types', 'bp-leadership'),
            'icon'              => 'user-multiple-02',
            'category'          => 'integration',
            'file'              => __FILE__,
            'has_settings'      => true,
            'settings_callback' => [__CLASS__, 'render_settings'],
            'version'           => BP_LEADERSHIP_VERSION,
            'plugin_file'       => BP_LEADERSHIP_PATH . 'bp-leadership.php',
        ];

        return $addons;
    }

    public static function init() {
        add_filter('starter_addon_save_settings_bp-leadership', [__CLASS__, 'save_settings'], 10, 2);
        add_action('wp_head', [__CLASS__, 'output_css_variables']);
    }

    public static function render_settings() {
        $story_ratio     = get_option(self::OPT_STORY_RATIO, '2/1');
        $featured_ratio  = get_option(self::OPT_FEATURED_RATIO, '2/1');
        $use_height      = get_option(self::OPT_USE_HEIGHT, 'no');
        $story_height    = get_option(self::OPT_STORY_HEIGHT, 250);
        $featured_height = get_option(self::OPT_FEATURED_HEIGHT, 250);
        $image_ratio     = get_option(self::OPT_IMAGE_RATIO, '2/1');
        $image_use_height = get_option(self::OPT_IMAGE_USE_HEIGHT, 'no');
        $image_height    = get_option(self::OPT_IMAGE_HEIGHT, 250);
        ?>
        <div class="bp-addon-settings bp-leadership-settings" data-addon="bp-leadership">

            <!-- Stories Visual Settings -->
            <div class="bp-addon-settings__section">
                <h4>
                    <easier-icon name="image-02" variant="twotone" size="18" color="var(--bp-primary)"></easier-icon>
                    <?php _e('Visual — Stories', 'bp-leadership'); ?>
                </h4>
                <p class="description"><?php _e('Control the aspect ratio or fixed height for story cards.', 'bp-leadership'); ?></p>

                <div class="bp-leadership-ratio-fields" data-toggle-group="stories" style="<?php echo $use_height === 'yes' ? 'display:none' : ''; ?>">
                    <div class="bp-addon-settings__field">
                        <label for="bp-leadership-story-ratio"><?php _e('Aspect Ratio — Every Story', 'bp-leadership'); ?></label>
                        <input type="text"
                               id="bp-leadership-story-ratio"
                               name="story_ratio"
                               value="<?php echo esc_attr($story_ratio); ?>"
                               placeholder="2/1"
                               class="regular-text">
                    </div>
                    <div class="bp-addon-settings__field">
                        <label for="bp-leadership-featured-ratio"><?php _e('Aspect Ratio — Featured Story', 'bp-leadership'); ?></label>
                        <input type="text"
                               id="bp-leadership-featured-ratio"
                               name="featured_ratio"
                               value="<?php echo esc_attr($featured_ratio); ?>"
                               placeholder="2/1"
                               class="regular-text">
                    </div>
                </div>

                <div class="bp-addon-settings__field bp-leadership-toggle-field">
                    <label class="bp-leadership-switch">
                        <input type="checkbox"
                               name="use_height"
                               value="yes"
                               data-toggle-target="stories"
                               <?php checked($use_height, 'yes'); ?>>
                        <span class="bp-leadership-switch__slider"></span>
                        <span class="bp-leadership-switch__label"><?php _e('Use height instead of aspect ratio', 'bp-leadership'); ?></span>
                    </label>
                </div>

                <div class="bp-leadership-height-fields" data-toggle-group="stories" style="<?php echo $use_height !== 'yes' ? 'display:none' : ''; ?>">
                    <div class="bp-addon-settings__field">
                        <label for="bp-leadership-story-height"><?php _e('Height — Every Story', 'bp-leadership'); ?></label>
                        <div class="bp-leadership-range-wrap">
                            <input type="range"
                                   id="bp-leadership-story-height"
                                   name="story_height"
                                   min="100"
                                   max="600"
                                   step="10"
                                   value="<?php echo esc_attr($story_height); ?>">
                            <span class="bp-leadership-range-value"><?php echo esc_html($story_height); ?>px</span>
                        </div>
                    </div>
                    <div class="bp-addon-settings__field">
                        <label for="bp-leadership-featured-height"><?php _e('Height — Featured Story', 'bp-leadership'); ?></label>
                        <div class="bp-leadership-range-wrap">
                            <input type="range"
                                   id="bp-leadership-featured-height"
                                   name="featured_height"
                                   min="100"
                                   max="600"
                                   step="10"
                                   value="<?php echo esc_attr($featured_height); ?>">
                            <span class="bp-leadership-range-value"><?php echo esc_html($featured_height); ?>px</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Featured Image Visual Settings -->
            <div class="bp-addon-settings__section">
                <h4>
                    <easier-icon name="image-01" variant="twotone" size="18" color="var(--bp-primary)"></easier-icon>
                    <?php _e('Visual — Featured Image', 'bp-leadership'); ?>
                </h4>
                <p class="description"><?php _e('Control the aspect ratio or fixed height for leadership featured images.', 'bp-leadership'); ?></p>

                <div class="bp-leadership-ratio-fields" data-toggle-group="image" style="<?php echo $image_use_height === 'yes' ? 'display:none' : ''; ?>">
                    <div class="bp-addon-settings__field">
                        <label for="bp-leadership-image-ratio"><?php _e('Featured Image Aspect Ratio', 'bp-leadership'); ?></label>
                        <input type="text"
                               id="bp-leadership-image-ratio"
                               name="image_ratio"
                               value="<?php echo esc_attr($image_ratio); ?>"
                               placeholder="2/1"
                               class="regular-text">
                    </div>
                </div>

                <div class="bp-addon-settings__field bp-leadership-toggle-field">
                    <label class="bp-leadership-switch">
                        <input type="checkbox"
                               name="image_use_height"
                               value="yes"
                               data-toggle-target="image"
                               <?php checked($image_use_height, 'yes'); ?>>
                        <span class="bp-leadership-switch__slider"></span>
                        <span class="bp-leadership-switch__label"><?php _e('Use height instead of aspect ratio', 'bp-leadership'); ?></span>
                    </label>
                </div>

                <div class="bp-leadership-height-fields" data-toggle-group="image" style="<?php echo $image_use_height !== 'yes' ? 'display:none' : ''; ?>">
                    <div class="bp-addon-settings__field">
                        <label for="bp-leadership-image-height"><?php _e('Featured Image Height', 'bp-leadership'); ?></label>
                        <div class="bp-leadership-range-wrap">
                            <input type="range"
                                   id="bp-leadership-image-height"
                                   name="image_height"
                                   min="100"
                                   max="600"
                                   step="10"
                                   value="<?php echo esc_attr($image_height); ?>">
                            <span class="bp-leadership-range-value"><?php echo esc_html($image_height); ?>px</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <style>
            .bp-leadership-settings .bp-addon-settings__section {
                padding-bottom: 20px;
                margin-bottom: 20px;
                border-bottom: 1px solid var(--bp-gray-light, #e5e7eb);
            }
            .bp-leadership-settings .bp-addon-settings__section:last-child {
                border-bottom: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }
            .bp-leadership-settings h4 {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .bp-leadership-ratio-fields {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
            .bp-leadership-height-fields {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
            .bp-leadership-toggle-field {
                margin-top: 12px;
                margin-bottom: 12px;
            }

            /* Switch toggle */
            .bp-leadership-switch {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                user-select: none;
            }
            .bp-leadership-switch input[type="checkbox"] {
                display: none;
            }
            .bp-leadership-switch__slider {
                position: relative;
                width: 40px;
                height: 22px;
                background: var(--bp-gray-light, #d1d5db);
                border-radius: 11px;
                transition: background 0.2s;
                flex-shrink: 0;
            }
            .bp-leadership-switch__slider::after {
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
            .bp-leadership-switch input:checked + .bp-leadership-switch__slider {
                background: var(--bp-primary, #1c3c8b);
            }
            .bp-leadership-switch input:checked + .bp-leadership-switch__slider::after {
                transform: translateX(18px);
            }
            .bp-leadership-switch__label {
                font-size: 13px;
                color: var(--bp-gray, #6b7280);
            }

            /* Range slider */
            .bp-leadership-range-wrap {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .bp-leadership-range-wrap input[type="range"] {
                flex: 1;
                -webkit-appearance: none;
                height: 6px;
                background: var(--bp-gray-light, #e5e7eb);
                border-radius: 3px;
                outline: none;
            }
            .bp-leadership-range-wrap input[type="range"]::-webkit-slider-thumb {
                -webkit-appearance: none;
                width: 18px;
                height: 18px;
                background: var(--bp-primary, #1c3c8b);
                border-radius: 50%;
                cursor: pointer;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            }
            .bp-leadership-range-wrap input[type="range"]::-moz-range-thumb {
                width: 18px;
                height: 18px;
                background: var(--bp-primary, #1c3c8b);
                border-radius: 50%;
                cursor: pointer;
                border: none;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            }
            .bp-leadership-range-value {
                min-width: 50px;
                text-align: right;
                font-size: 13px;
                font-weight: 600;
                color: var(--bp-dark, #1f2937);
                font-variant-numeric: tabular-nums;
            }
        </style>

        <script>
        (function() {
            const container = document.querySelector('.bp-leadership-settings');
            if (!container) return;

            // Toggle between ratio and height fields
            container.querySelectorAll('[data-toggle-target]').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    var group = this.getAttribute('data-toggle-target');
                    var ratioFields = container.querySelector('.bp-leadership-ratio-fields[data-toggle-group="' + group + '"]');
                    var heightFields = container.querySelector('.bp-leadership-height-fields[data-toggle-group="' + group + '"]');
                    if (this.checked) {
                        ratioFields.style.display = 'none';
                        heightFields.style.display = '';
                    } else {
                        ratioFields.style.display = '';
                        heightFields.style.display = 'none';
                    }
                });
            });

            // Update range value display
            container.querySelectorAll('input[type="range"]').forEach(function(range) {
                var valueSpan = range.parentElement.querySelector('.bp-leadership-range-value');
                range.addEventListener('input', function() {
                    valueSpan.textContent = this.value + 'px';
                });
            });
        })();
        </script>
        <?php
    }

    public static function save_settings($saved, $settings) {
        if (isset($settings['story_ratio'])) {
            update_option(self::OPT_STORY_RATIO, sanitize_text_field($settings['story_ratio']));
        }
        if (isset($settings['featured_ratio'])) {
            update_option(self::OPT_FEATURED_RATIO, sanitize_text_field($settings['featured_ratio']));
        }

        update_option(self::OPT_USE_HEIGHT, isset($settings['use_height']) && $settings['use_height'] === 'yes' ? 'yes' : 'no');

        if (isset($settings['story_height'])) {
            update_option(self::OPT_STORY_HEIGHT, absint($settings['story_height']));
        }
        if (isset($settings['featured_height'])) {
            update_option(self::OPT_FEATURED_HEIGHT, absint($settings['featured_height']));
        }

        if (isset($settings['image_ratio'])) {
            update_option(self::OPT_IMAGE_RATIO, sanitize_text_field($settings['image_ratio']));
        }

        update_option(self::OPT_IMAGE_USE_HEIGHT, isset($settings['image_use_height']) && $settings['image_use_height'] === 'yes' ? 'yes' : 'no');

        if (isset($settings['image_height'])) {
            update_option(self::OPT_IMAGE_HEIGHT, absint($settings['image_height']));
        }

        return true;
    }

    public static function output_css_variables() {
        $use_height       = get_option(self::OPT_USE_HEIGHT, 'no');
        $image_use_height = get_option(self::OPT_IMAGE_USE_HEIGHT, 'no');

        $breakpoints = [
            'desktop' => ['suffix' => '',        'media' => '',                         'ratio_default' => '2/1', 'height_default' => 250],
            'tablet'  => ['suffix' => '_tablet',  'media' => '@media(max-width:1024px)', 'ratio_default' => '3/2', 'height_default' => 200],
            'mobile'  => ['suffix' => '_mobile',  'media' => '@media(max-width:767px)',  'ratio_default' => '1/1', 'height_default' => 180],
        ];

        $css = '';

        foreach ($breakpoints as $key => $bp) {
            $s  = $bp['suffix'];
            $rd = $bp['ratio_default'];
            $hd = $bp['height_default'];
            $var_suffix = $s ? str_replace('_', '-', $s) : '';

            $vars = [];

            if ($use_height === 'yes') {
                $story_h    = absint(get_option('bp_leadership_story_height' . $s, $hd));
                $featured_h = absint(get_option('bp_leadership_featured_height' . $s, $hd));
                $vars[] = '--bp-leadership-story-height' . $var_suffix . ':' . $story_h . 'px';
                $vars[] = '--bp-leadership-featured-story-height' . $var_suffix . ':' . $featured_h . 'px';
                $vars[] = '--bp-leadership-story-ratio' . $var_suffix . ':auto';
                $vars[] = '--bp-leadership-featured-story-ratio' . $var_suffix . ':auto';
            } else {
                $story_r    = sanitize_text_field(get_option('bp_leadership_story_ratio' . $s, $rd));
                $featured_r = sanitize_text_field(get_option('bp_leadership_featured_ratio' . $s, $rd));
                $vars[] = '--bp-leadership-story-ratio' . $var_suffix . ':' . $story_r;
                $vars[] = '--bp-leadership-featured-story-ratio' . $var_suffix . ':' . $featured_r;
                $vars[] = '--bp-leadership-story-height' . $var_suffix . ':auto';
                $vars[] = '--bp-leadership-featured-story-height' . $var_suffix . ':auto';
            }

            if ($image_use_height === 'yes') {
                $image_h = absint(get_option('bp_leadership_image_height' . $s, $hd));
                $vars[] = '--bp-leadership-image-height' . $var_suffix . ':' . $image_h . 'px';
                $vars[] = '--bp-leadership-image-ratio' . $var_suffix . ':auto';
            } else {
                $image_r = sanitize_text_field(get_option('bp_leadership_image_ratio' . $s, $rd));
                $vars[] = '--bp-leadership-image-ratio' . $var_suffix . ':' . $image_r;
                $vars[] = '--bp-leadership-image-height' . $var_suffix . ':auto';
            }

            $rule = ':root{' . implode(';', $vars) . '}';
            $css .= $bp['media'] ? $bp['media'] . '{' . $rule . '}' : $rule;
        }

        echo '<style id="bp-leadership-vars">' . $css . '</style>' . "\n";
    }
}
