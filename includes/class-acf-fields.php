<?php
/**
 * ACF Field Definitions (programmatic)
 */

defined('ABSPATH') || exit;

class BP_Leadership_ACF_Fields {

    public static function register() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        self::register_featured_story_fields();
        self::register_leadership_fields();
    }

    private static function register_featured_story_fields() {
        acf_add_local_field_group([
            'key'      => 'group_featured_story_details',
            'title'    => 'Featured Story Details',
            'fields'   => [
                [
                    'key'      => 'field_story_url',
                    'label'    => 'Story URL',
                    'name'     => 'story_url',
                    'type'     => 'url',
                    'required' => 1,
                    'placeholder' => 'https://',
                ],
                [
                    'key'           => 'field_story_logo',
                    'label'         => 'Logo',
                    'name'          => 'story_logo',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'thumbnail',
                    'mime_types'    => 'jpg,jpeg,png,svg,webp',
                    'instructions'  => 'Publication or source logo for this story.',
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'featured_story',
                    ],
                ],
            ],
            'position'           => 'normal',
            'style'              => 'default',
            'label_placement'    => 'top',
            'instruction_placement' => 'label',
        ]);
    }

    private static function register_leadership_fields() {
        acf_add_local_field_group([
            'key'      => 'group_leadership_details',
            'title'    => 'Leadership Details',
            'fields'   => [
                [
                    'key'      => 'field_first_name',
                    'label'    => 'First Name',
                    'name'     => 'first_name',
                    'type'     => 'text',
                    'required' => 1,
                ],
                [
                    'key'      => 'field_last_name',
                    'label'    => 'Last Name',
                    'name'     => 'last_name',
                    'type'     => 'text',
                    'required' => 1,
                ],
                [
                    'key'          => 'field_header_content',
                    'label'        => 'Header Content',
                    'name'         => 'header_content',
                    'type'         => 'wysiwyg',
                    'tabs'         => 'all',
                    'toolbar'      => 'full',
                    'media_upload' => 1,
                    'instructions' => 'Content displayed in the hero section below the name.',
                ],
                [
                    'key'          => 'field_body_content',
                    'label'        => 'Body Content',
                    'name'         => 'body_content',
                    'type'         => 'wysiwyg',
                    'tabs'         => 'all',
                    'toolbar'      => 'full',
                    'media_upload' => 1,
                    'instructions' => 'Main body content displayed below the hero section.',
                ],
                [
                    'key'         => 'field_linkedin_url',
                    'label'       => 'LinkedIn URL',
                    'name'        => 'linkedin_url',
                    'type'        => 'url',
                    'placeholder' => 'https://linkedin.com/in/',
                ],
                [
                    'key'          => 'field_video_url',
                    'label'        => 'Video URL',
                    'name'         => 'video_url',
                    'type'         => 'url',
                    'instructions' => 'YouTube or Vimeo link. Takes priority over uploaded file.',
                    'placeholder'  => 'https://youtube.com/watch?v=',
                ],
                [
                    'key'           => 'field_video_file',
                    'label'         => 'Video File',
                    'name'          => 'video_file',
                    'type'          => 'file',
                    'return_format' => 'url',
                    'mime_types'    => 'mp4,webm,mov',
                    'instructions'  => 'Upload a video file. Used only if Video URL is empty.',
                ],
                [
                    'key'           => 'field_featured_story',
                    'label'         => 'Featured Story',
                    'name'          => 'featured_story',
                    'type'          => 'post_object',
                    'post_type'     => ['featured_story'],
                    'return_format' => 'object',
                    'allow_null'    => 1,
                    'instructions'  => 'Select a single Featured Story to highlight.',
                ],
                [
                    'key'           => 'field_exclude_from_search',
                    'label'         => 'Exclude from Search',
                    'name'          => 'exclude_from_search',
                    'type'          => 'true_false',
                    'default_value' => 0,
                    'ui'            => 1,
                    'instructions'  => 'Hide this leader from site search results.',
                ],
                [
                    'key'           => 'field_featured_stories',
                    'label'         => 'Featured Stories',
                    'name'          => 'featured_stories',
                    'type'          => 'relationship',
                    'post_type'     => ['featured_story'],
                    'filters'       => ['search'],
                    'return_format' => 'object',
                    'min'           => 0,
                    'max'           => '',
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'leadership',
                    ],
                ],
            ],
            'position'           => 'normal',
            'style'              => 'default',
            'label_placement'    => 'top',
            'instruction_placement' => 'label',
        ]);
    }
}
