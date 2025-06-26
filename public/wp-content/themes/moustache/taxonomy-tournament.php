<?php

/**
 * Tournament template
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

get_header();

/**
 * Display club list
 *
 * @param array $clubs Array of club objects
 * @return void
 */
function display_club_list(array $clubs): void
{
    foreach ($clubs as $club) {
        if ('Kampbart' === get_the_title($club)) {
            printf('<li>%s</li>', esc_html(get_the_title($club)));
        } else {
            printf(
                '<li><a href="%1$s">%2$s</a></li>',
                esc_url(get_permalink($club)),
                esc_html(get_the_title($club))
            );
        }
    }
}

/**
 * Display match date
 *
 * @param string $datetime DateTime string
 * @param string $format Format string for date_i18n
 * @return void
 */
function display_match_date(string $datetime, string $format): void
{
    $timestamp = strtotime($datetime);
    echo esc_html(date_i18n($format, $timestamp));
}

/**
 * Get match date time
 *
 * @param array $match Match data
 * @return string
 */
function get_match_datetime(array $match): string
{
    return $match['new_date_time'] ?? $match['date_time'] ?? '';
}

$term = get_queried_object();
?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
    <div class="o-grid u-text-center">
        <div class="o-grid__item u-1/1">
            <section class="o-section-md">
                <header>
                    <h1><?php echo esc_html(single_term_title('', false)); ?></h1>
                    <?php
                    $division = get_field('tournament_division', $term);
                    if ($division) : ?>
                        <h2><?php echo esc_html($division->name); ?></h2>
                    <?php endif; ?>

                    <nav class="u-soft-bottom-md">
                        <a href="#terminliste"><?php esc_html_e('Terminliste', 'moustache'); ?></a>
                        <a href="#tabell"><?php esc_html_e('Tabell', 'moustache'); ?></a>
                        <a href="#statistikk"><?php esc_html_e('Statistikk', 'moustache'); ?></a>
                    </nav>
                </header>

                <div class="u-flow">
                    <?php
                    // Get tournament data
                    $clubs = get_field('tournament_clubs', $term);
                    $withdrawals = get_field('tournament_withdrawals', $term);
                    $clubs_withdrawn = get_field('tournament_whitdrawn_clubs', $term);

                    // Display participating clubs
                    if ($clubs) : ?>
                        <div>
                            <p><?php esc_html_e('Disse lagene deltar:', 'moustache'); ?></p>
                            <ul><?php display_club_list($clubs); ?></ul>

                            <?php if ($withdrawals) : ?>
                                <p><?php esc_html_e('Disse lagene har trukket seg:', 'moustache'); ?></p>
                                <ul><?php display_club_list($clubs_withdrawn); ?></ul>
                            <?php endif; ?>
                        </div>
                    <?php endif;

                    // Get fixtures
                    if (is_tax()) {
                        $fixtures_query = new WP_Query([
                            'posts_per_page' => -1,
                            'post_type'      => 'fixture',
                            'tax_query'      => [
                                [
                                    'taxonomy' => $term->taxonomy,
                                    'field'    => 'term_id',
                                    'terms'    => $term->term_id,
                                ],
                            ],
                            'meta_query'     => [
                                'relation' => 'OR',
                                ['key' => 'new_date_time', 'compare' => 'EXISTS'],
                                ['key' => 'date_time', 'compare' => 'EXISTS'],
                            ],
                        ]);

                        if ($fixtures_query->have_posts()) {
                            $fixtures = $fixtures_query->posts;

                            // Sort fixtures by date
                            usort($fixtures, function ($a, $b) {
                                $a_datetime = get_match_datetime([
                                    'new_date_time' => get_field('new_date_time', $a->ID),
                                    'date_time' => get_field('date_time', $a->ID)
                                ]);

                                $b_datetime = get_match_datetime([
                                    'new_date_time' => get_field('new_date_time', $b->ID),
                                    'date_time' => get_field('date_time', $b->ID)
                                ]);

                                return strtotime($a_datetime) - strtotime($b_datetime);
                            });

                            // Include fixture list template
                            include(locate_template('template-parts/fixture-list.php'));
                        }
                    }
                    ?>

                    <div id="tabell" role="region" aria-labelledby="caption" tabindex="0">
                        <?php
                        // Check if this is the specific tournament that should show the standings table
                        $should_show_standings = (
                            $term->slug === 'uteserie-2025'
                        );

                        if ($should_show_standings) {
                            // Show the dynamic standings table for the specific tournament
                            include(locate_template('template-parts/standings-table.php'));
                        } else {
                            // Show the original tournament content for other tournaments
                            echo wp_kses_post(get_field('tournament_content', $term));
                        }
                        ?>
                    </div>

                    <div id="statistikk" role="region" aria-labelledby="caption" tabindex="0">
                        <?php include(locate_template('template-parts/tournament-stats.php')); ?>
                    </div>

                </div>
            </section>
        </div>
    </div>
</div>

<?php
get_footer();
