<?php
get_header();
$video_id   = get_the_ID();
$title      = get_the_title();
$desc      = get_the_content();
$categories = get_the_terms($video_id, 'videos_hub_category');
$category   = !empty($categories) && !is_wp_error($categories) ? $categories[0]->name : '';
$video_file = get_field('video_source', $video_id);
$thumbnail  = get_field('thumbnail', $video_id);
?>

<div class="video-single-page container">
    <a href="/my/epos-video-hub" class="back-link">← Back to Video Hub</a>

    <div class="video-main">
        <p class="video-category"><?php echo implode(', ', wp_list_pluck(get_the_terms($video_id, 'videos_hub_category') ?: [], 'name')); ?></p>
        <h1 class="video-title"><?php echo esc_html($title); ?></h1>
        <p class="video-desc"><?php echo esc_html($desc); ?></p>
        <?php if ($video_file): ?>
            <div class="video-player">
                <figure class="video-card" id="videoCard">
                    <video
                        id="myVideo"
                        class="media"
                        poster="<?php echo esc_url($thumbnail['url']); ?>"
                        preload="none"
                        playsinline
                        aria-label="Video demo"
                        tabindex="0"
                        data-src="<?php echo esc_url($video_file['url']); ?>">
                    </video>

                    <div class="play-overlay" aria-hidden="true">
                        <div class="play-btn" id="playBtn" role="button" aria-label="Play video" tabindex="0">
                            <img class="play-icon"
                                src="/wp-content/uploads/2025/10/EPOSMY-Webpage-Resources-Section-04-button.webp"
                                alt="Play video">
                        </div>
                    </div>
                </figure>
                <div class="vid-info">
                    <p class="vid-title"><?php echo esc_html($title); ?></p>
                    <p class="vid-desc"><?php echo esc_html($desc); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="related-videos">
        <h2>More from <?php echo esc_html($category ?: 'Getting Started'); ?></h2>
        <div class="related-videos-grid">
            <?php
            $args = [
                'post_type'      => 'videos_hub',
                'posts_per_page' => 3,
                'post__not_in'   => [$video_id],
                'orderby'        => 'date',
                'order'          => 'DESC',
                'tax_query' => [
                    [
                        'taxonomy' => 'videos_hub_category',
                        'field' => 'slug',
                        'terms' => $category,
                    ],
                ],
            ];
            $related = new WP_Query($args);
            if ($related->have_posts()):
                while ($related->have_posts()): $related->the_post();
                    $thumb = get_field('thumbnail');
            ?>
                    <a href="<?php the_permalink(); ?>" class="related-video-item">
                        <?php if ($thumb): ?>
                            <img src="<?php echo esc_url($thumb['url']); ?>" alt="<?php the_title_attribute(); ?>">
                        <?php endif; ?>
                        <h3><?php the_title(); ?></h3>
                    </a>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </div>
</div>
<div class="support-section content-area" id="content">
    <?php echo do_shortcode('[block id="video-hub-footer"]'); ?>
</div>
<?php get_footer(); ?>