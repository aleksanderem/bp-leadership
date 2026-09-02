<?php
/**
 * Custom Elementor Loop Query Sources
 *
 * Usage: In Elementor Loop Grid widget, set Query ID to "assigned_stories".
 */

defined('ABSPATH') || exit;

class BP_Leadership_Elementor_Queries {

    private static $running = false;

    public static function init() {
        add_action('elementor/query/assigned_stories', [__CLASS__, 'assigned_stories_query'], 10, 2);
        add_action('elementor/query/featured_story_single', [__CLASS__, 'featured_story_single_query'], 10, 2);
    }

    /**
     * Resolve the leadership post ID in every context Elementor renders from:
     * normal page view, editor preview, and AJAX pagination (Load More),
     * where get_queried_object() is not available.
     */
    private static function resolve_leader_id() {
        $queried = get_queried_object();
        if (is_object($queried) && ($queried->post_type ?? '') === 'leadership') {
            return (int) $queried->ID;
        }

        global $post;
        if (is_object($post) && ($post->post_type ?? '') === 'leadership') {
            return (int) $post->ID;
        }

        // Elementor AJAX / editor: current document context
        if (class_exists('\Elementor\Plugin')) {
            $doc = \Elementor\Plugin::$instance->documents->get_current();
            if ($doc) {
                $pid = (int) $doc->get_main_id();
                if ($pid && get_post_type($pid) === 'leadership') {
                    return $pid;
                }
            }
        }

        // Last resort: post ID passed in the request (Elementor Pro pagination/ajax)
        foreach (['post_id', 'p', 'e_post_id', 'editor_post_id', 'initial_document_id'] as $key) {
            $pid = isset($_REQUEST[$key]) ? (int) $_REQUEST[$key] : 0;
            if ($pid && get_post_type($pid) === 'leadership') {
                return $pid;
            }
        }

        return 0;
    }

    public static function assigned_stories_query($query, $widget) {
        // Prevent recursion - this hook fires on every WP_Query via pre_get_posts
        if (self::$running) {
            return;
        }
        self::$running = true;

        $leader_id = self::resolve_leader_id();

        if (!$leader_id) {
            $query->set('post__in', [0]);
            self::$running = false;
            return;
        }

        // Use get_post_meta directly to avoid triggering WP_Query via ACF
        $story_ids = [];

        $multiple = get_post_meta($leader_id, 'featured_stories', true);
        if (is_string($multiple) && !empty($multiple)) {
            $multiple = maybe_unserialize($multiple);
        }
        // Handle comma-separated string (e.g. from DC fix plugin filtering)
        if (is_string($multiple) && !empty($multiple)) {
            $multiple = array_map('intval', array_filter(explode(',', $multiple)));
        }
        if (is_array($multiple)) {
            foreach ($multiple as $sid) {
                $sid = (int) $sid;
                if ($sid > 0 && !in_array($sid, $story_ids)) {
                    $story_ids[] = $sid;
                }
            }
        }

        if (empty($story_ids)) {
            $query->set('post__in', [0]);
            self::$running = false;
            return;
        }

        // Exclude the primary featured story to avoid duplicate
        $primary_story_id = (int) get_post_meta($leader_id, 'featured_story', true);
        if ($primary_story_id > 0) {
            $story_ids = array_values(array_diff($story_ids, [$primary_story_id]));
        }

        if (empty($story_ids)) {
            $query->set('post__in', [0]);
            self::$running = false;
            return;
        }

        $query->set('post_type', 'featured_story');
        $query->set('post__in', $story_ids);
        $query->set('orderby', 'post__in');

        self::$running = false;
    }

    public static function featured_story_single_query($query, $widget) {
        if (self::$running) {
            return;
        }
        self::$running = true;

        $leader_id = self::resolve_leader_id();

        if (!$leader_id) {
            $query->set('post__in', [0]);
            self::$running = false;
            return;
        }

        $story_id = get_post_meta($leader_id, 'featured_story', true);

        if (!is_numeric($story_id) || $story_id <= 0) {
            $query->set('post__in', [0]);
            self::$running = false;
            return;
        }

        $query->set('post_type', 'featured_story');
        $query->set('post__in', [(int) $story_id]);
        $query->set('posts_per_page', 1);

        self::$running = false;
    }
}
