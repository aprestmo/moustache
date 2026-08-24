<?php

/**
 * The template for displaying all single club posts
 *
 * @package Moustache
 */

get_header();

// Get the current club ID
$club_id = get_the_ID();

// Query for fixtures related to this club as either home or away team
$args = array(
    'post_type'      => 'fixture',
    'posts_per_page' => -1,
    'meta_query'     => array(
        'relation' => 'OR',
        array(
            'key'     => 'home_team', // ACF field name for home team
            'value'   => $club_id,
            'compare' => 'LIKE',
        ),
        array(
            'key'     => 'away_team', // ACF field name for away team
            'value'   => $club_id,
            'compare' => 'LIKE',
        ),
    ),
    'orderby'        => 'meta_value',
    'meta_key'       => 'date_time', // ACF field name for datetime
    'order'          => 'ASC',
);

$fixtures_query = new WP_Query($args);

// Get the date of the oldest fixture
$oldest_fixture_date = '';
if ($fixtures_query->have_posts()) {
    // Get the first fixture post without advancing the query
    $oldest_fixture = $fixtures_query->posts[0];
    $oldest_fixture_date = get_field('date_time', $oldest_fixture->ID);
}
?>

<article class="mou-site-wrap mou-site-wrap--padding wysiwyg">
    <div class="o-grid o-section-md">
        <div class="o-grid__item">
            <h1><?php printf(esc_html__('Matches against %s', 'moustache'), get_the_title()); ?></h1>
            <?php
            $now = strtotime('now');
            $past = strtotime($oldest_fixture_date);
            ?>
            <?php
            // Check if there is exactly one post
            if ($fixtures_query->post_count > 1) {
                // Display the date of the oldest fixture
                if ($past < $now) {
                    echo wp_kses_post(sprintf(
                        __('First meeting with %1$s was <time datetime="%2$s">%3$s</time>.', 'moustache'),
                        esc_html(get_the_title()),
                        esc_attr($oldest_fixture_date),
                        wp_date('j. F Y', strtotime($oldest_fixture_date))
                    ));
                } else {
                    echo wp_kses_post(sprintf(
                        __('First meeting with %1$s is <time datetime="%2$s">%3$s</time>.', 'moustache'),
                        esc_html(get_the_title()),
                        esc_attr($oldest_fixture_date),
                        wp_date('j. F Y', strtotime($oldest_fixture_date))
                    ));
                }
            }

            ?>

            <div class="table-scroll" role="region" aria-labelledby="caption" tabindex="0">
                <table>
                    <?php if ($fixtures_query->have_posts()) : ?>
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Date', 'moustache'); ?></th>
                                <th><?php esc_html_e('Match report', 'moustache'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($fixtures_query->have_posts()) :
                                $fixtures_query->the_post();
                                $fixture_id = get_the_ID(); // Get the current fixture ID
                                $date_time = get_field('date_time');
                                $unplayed_reason = moustache_fixture_unplayed_reason($fixture_id);

                                // Check if the fixture date is in the past
                                if ($date_time && strtotime($date_time) < time()) {
                            ?>
                                    <tr<?php echo $unplayed_reason ? ' class="is-' . esc_attr($unplayed_reason) . '"' : ''; ?>>
                                        <td>
                                            <?php
                                            $formatted_datetime = wp_date('j. F Y', strtotime($date_time));
                                            echo esc_html($formatted_datetime);
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($unplayed_reason) : ?>
                                                <?php echo esc_html(moustache_fixture_unplayed_label($unplayed_reason)); ?>
                                            <?php else :
                                            // Query for related posts for the current fixture ID
                                            $related_posts_query = new WP_Query(array(
                                                'post_type'      => 'post',
                                                'posts_per_page' => -1,
                                                'meta_query'     => array(
                                                    array(
                                                        'key'     => 'match_report', // ACF relationship field name
                                                        'value'   => $fixture_id,
                                                        'compare' => 'LIKE',
                                                    ),
                                                ),
                                            ));

                                            if ($related_posts_query->have_posts()) :
                                                while ($related_posts_query->have_posts()) : $related_posts_query->the_post(); ?>
                                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            <?php endwhile;
                                            else :
                                                esc_html_e('No match report found.', 'moustache');
                                            endif;

                                            wp_reset_postdata();
                                            ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php
                                }
                            endwhile;
                            ?>
                        </tbody>
                </table>
            </div>
        <?php endif; ?>
        </div>
    </div>
</article>

<?php wp_reset_postdata(); ?>

<?php get_footer(); ?>