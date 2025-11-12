<?php
function job_openings_shortcode()
{
    ob_start();

    $jobs_api = get_workable_jobs_epos_filtered([
        'location' => [
            ['country' => 'Malaysia', 'countryCode' => 'MY']
        ],
    ]);
    $employment_types = [];
    foreach ($jobs_api as $job) {
        $employment_types[] = $job['type'];
    }

    $employment_types = array_unique($employment_types);
    sort($employment_types);
?>

    <div class="job-openings-wrapper">
        
        <div class="job-filters">
            <input type="text" id="job-search" placeholder="Search jobs..." class="job-search-full" />
            <!-- Work Type dropdown -->
            <div class="custom-select" id="work-filter">
                <div class="select-btn">
                    <span class="btn-text">Work Type</span>
                    <span class="arrow-dwn"><i class="fa-solid fa-chevron-down"></i></span>
                </div>
                <ul class="list-items">
                    <?php if (!empty($employment_types)): ?>
                        <?php foreach ($employment_types as $type): ?>
                            <li class="item">
                                <span class="checkbox"><i class="fa-solid fa-check check-icon"></i></span>
                                <span class="item-text" data-value="<?php echo esc_attr(strtolower(str_replace(' ', '-', $type))); ?>">
                                    <?php echo esc_html(str_replace('full', 'Full Time', $type)); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="item disabled"><span class="item-text">No work types found</span></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="label-search-group" style="display: none;">
            <div class="show-tags-choised"></div>
            <button id="clear-filters"><i class="fa-solid fa-rotate-right"></i> Clear Filters</button>
        </div>

        <div class="job-list" id="job-list">
            <?php foreach ($jobs_api as $job): ?>
                <?php
                $apply_url = "https://apply.workable.com/epos/j/" . $job['shortcode'];
                $date_string = $job['published'];
                $timestamp = strtotime($date_string);
                $current = current_time('timestamp');
                $diff = human_time_diff($timestamp, $current) . ' ago';
                ?>
                <div class="job-item"
                    data-location="<?php echo esc_attr($job['location']['country']); ?>"
                    data-department="<?php echo esc_attr($job['department']['0']); ?>"
                    data-work="<?php echo esc_attr($job['type']); ?>">
                    <a href="<?php echo esc_url($apply_url); ?>">
                        <div class="job-name-wrapper">
                            <h3 class="job-name"><a href="<?php echo esc_url($apply_url); ?>"><?php echo esc_html($job['title']); ?></a></h3>
                            <div class="job-meta">
                                <span class="job-posted">Posted <?php echo esc_html($diff); ?></span>
                            </div>
                        </div>
                        <div class="job-metas">
                            <div class="job-type-wrapper"><span class="job-type"><?php echo ucfirst(str_replace('on_site', 'On site', $job['workplace'])); ?></span></div>
                            <div class="job-location-wrapper"><span class="job-location"><?php echo ucfirst($job['location']['country']); ?></span></div>
                            <div class="job-department-wrapper"><span class="job-department"><?php echo ucfirst($job['department']['0']); ?></span></div>
                            <div class="job-work-wrapper"><span class="job-work"><?php echo ucfirst(str_replace('full', 'Full Time', $job['type'])); ?></span></div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>

            <div class="job-loading-overlay" id="job-loading" style="display: none;">
                <div class="job-loading-inner">
                    <img src="https://www.epos.com.sg/wp-content/uploads/2025/11/EPOS_Full-Color.png" alt="Loading..." class="loading-logo">
                </div>
            </div>
        </div>
        <div id="no-jobs" style="display: none; text-align:center; padding: 40px 0; font-size: 16px; color: #666;">
            No jobs found matching your criteria.
        </div>
        <div class="load-more-wrapper" style="text-align: center; margin-top: 20px;">
            <button id="load-more" class="load-more-btn">Load More</button>
        </div>
    </div>

<?php
    return ob_get_clean();
}
add_shortcode('job_openings', 'job_openings_shortcode');
