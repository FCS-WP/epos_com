<?php
$args = array(
    'post_type'      => 'videos_hub',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
);
$query = new WP_Query($args);

if ($query->have_posts()) :
    while ($query->have_posts()) : $query->the_post();
        $video_id = get_the_ID();
        $title = get_the_title();
        $video_file = get_field('video_source', $video_id);
        $thumbnail = get_field('thumbnail', $video_id);

        echo '<div class="video-item">';
        echo '<h3>'.esc_html($title).'</h3>';

        if ($thumbnail) {
            echo '<img src="'.esc_url($thumbnail['url']).'" alt="'.esc_attr($title).'" />';
        }
        if ($video_file) {
            echo '<video controls><source src="'.esc_url($video_file['url']).'" type="video/mp4"></video>';
        }
        echo '</div>';
    endwhile;
    wp_reset_postdata();
else :
    echo '<p>No videos found.</p>';
endif;
