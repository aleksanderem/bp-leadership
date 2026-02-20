<?php
/**
 * Single Leadership Template
 */

defined('ABSPATH') || exit;

get_header('elementor');
?>

<?php while (have_posts()) : the_post();
    $first_name       = get_field('first_name');
    $last_name        = get_field('last_name');
    $header_content   = get_field('header_content');
    $body_content     = get_field('body_content');
    $linkedin_url     = get_field('linkedin_url');
    $video_url        = get_field('video_url');
    $video_file       = get_field('video_file');
    $featured_stories = get_field('featured_stories');
    $full_name        = trim("$first_name $last_name");
?>

<main id="content" class="leadership-single">
    <div class="leadership-single__container">

        <div class="leadership-single__hero">
            <?php if (has_post_thumbnail()): ?>
                <div class="leadership-single__photo">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <div class="leadership-single__info">
                <h1 class="leadership-single__name"><?php echo esc_html($full_name); ?></h1>

                <?php if ($linkedin_url): ?>
                    <a href="<?php echo esc_url($linkedin_url); ?>" class="leadership-single__linkedin" target="_blank" rel="noopener noreferrer">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        LinkedIn
                    </a>
                <?php endif; ?>

                <?php if ($header_content): ?>
                    <div class="leadership-single__header-content">
                        <?php echo $header_content; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($body_content): ?>
            <div class="leadership-single__content">
                <?php echo $body_content; ?>
            </div>
        <?php endif; ?>

        <?php if ($video_url || $video_file): ?>
            <div class="leadership-single__video">
                <?php if ($video_url): ?>
                    <div class="leadership-single__video-embed">
                        <?php echo wp_oembed_get($video_url); ?>
                    </div>
                <?php elseif ($video_file): ?>
                    <video controls preload="metadata" class="leadership-single__video-player">
                        <source src="<?php echo esc_url($video_file); ?>">
                    </video>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php
            if ($featured_stories && !is_array($featured_stories)) {
                $featured_stories = [$featured_stories];
            }
        ?>
        <?php if (!empty($featured_stories)): ?>
            <div class="leadership-single__stories">
                <h2><?php _e('Featured Stories', 'bp-leadership'); ?></h2>
                <div class="leadership-single__stories-grid">
                    <?php foreach ($featured_stories as $story):
                        if (!$story instanceof WP_Post) continue;
                        $story_url  = get_field('story_url', $story->ID);
                        $story_logo = get_field('story_logo', $story->ID);
                    ?>
                        <a href="<?php echo esc_url($story_url); ?>" class="leadership-single__story-card" target="_blank" rel="noopener noreferrer">
                            <?php if (has_post_thumbnail($story->ID)): ?>
                                <div class="leadership-single__story-thumb">
                                    <?php echo get_the_post_thumbnail($story->ID, 'medium'); ?>
                                </div>
                            <?php endif; ?>
                            <div class="leadership-single__story-content">
                                <?php if ($story_logo): ?>
                                    <img src="<?php echo esc_url($story_logo['url']); ?>" alt="<?php echo esc_attr($story_logo['alt'] ?: $story->post_title); ?>" class="leadership-single__story-logo">
                                <?php endif; ?>
                                <h3><?php echo esc_html($story->post_title); ?></h3>
                                <?php if ($story->post_excerpt): ?>
                                    <p><?php echo esc_html($story->post_excerpt); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<style>
.leadership-single__container {
    max-width: 960px;
    margin: 0 auto;
    padding: 60px 20px;
}

.leadership-single__hero {
    display: flex;
    gap: 40px;
    align-items: flex-start;
    margin-bottom: 48px;
}

.leadership-single__photo {
    flex-shrink: 0;
    width: 240px;
}

.leadership-single__photo img {
    width: 100%;
    height: auto;
    border-radius: 12px;
    object-fit: cover;
}

.leadership-single__info {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding-top: 12px;
}

.leadership-single__name {
    font-size: 2.25rem;
    line-height: 1.2;
    margin: 0;
}

.leadership-single__header-content {
    line-height: 1.6;
    font-size: 1rem;
    color: #374151;
}

.leadership-single__linkedin {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #0a66c2;
    text-decoration: none;
    font-weight: 500;
}

.leadership-single__linkedin:hover {
    text-decoration: underline;
}

.leadership-single__content {
    margin-bottom: 48px;
    line-height: 1.7;
    font-size: 1.05rem;
}

.leadership-single__video {
    margin-bottom: 48px;
}

.leadership-single__video-embed iframe {
    width: 100%;
    aspect-ratio: 16/9;
    border-radius: 12px;
}

.leadership-single__video-player {
    width: 100%;
    border-radius: 12px;
}

.leadership-single__stories h2 {
    font-size: 1.5rem;
    margin-bottom: 24px;
}

.leadership-single__stories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}

.leadership-single__story-card {
    display: flex;
    flex-direction: column;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    background: #f9fafb;
    transition: box-shadow 0.2s;
}

.leadership-single__story-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.leadership-single__story-thumb img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.leadership-single__story-content {
    padding: 20px;
}

.leadership-single__story-logo {
    height: 24px;
    width: auto;
    object-fit: contain;
    margin-bottom: 8px;
}

.leadership-single__story-content h3 {
    font-size: 1.1rem;
    margin: 0 0 8px;
}

.leadership-single__story-content p {
    font-size: 0.9rem;
    color: #6b7280;
    margin: 0;
}

@media (max-width: 640px) {
    .leadership-single__hero {
        flex-direction: column;
        gap: 24px;
    }
    .leadership-single__photo {
        width: 100%;
        max-width: 280px;
    }
    .leadership-single__name {
        font-size: 1.75rem;
    }
}
</style>

<?php endwhile; ?>

<?php get_footer('elementor'); ?>
