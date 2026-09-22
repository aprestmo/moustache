<?php

/**
 * Template part for displaying fixture result
 *
 * @package Moustache
 */

$fixture_id = get_the_ID();
$home_team = moustache_get_home_team($fixture_id);
$away_team = moustache_get_away_team($fixture_id);
$walkover = get_field('walkover', $fixture_id);
$walkover_result = (int) get_field('walkover_result', $fixture_id);
$walkover_winner = get_field('walkover_winner', $fixture_id);
$result_only = moustache_fixture_is_result_only($fixture_id);
$recorded = moustache_get_recorded_result($fixture_id);
$unplayed_reason = moustache_fixture_unplayed_reason($fixture_id);
$date_time = moustache_get_fixture_datetime($fixture_id);
$postponed = get_field('postponed', $fixture_id);
$new_date_time = get_field('new_date_time', $fixture_id);
$match_started = $date_time && ($ts = moustache_acf_datetime_timestamp($date_time)) && $ts < time();

if ($unplayed_reason === 'abandoned') : ?>
    <td>&mdash;</td>
<?php elseif ($walkover && $result_only) : ?>
    <td style="color: red">
        <?php esc_html_e('You can\'t select both walkover and inadequate report', 'moustache'); ?>
    </td>
<?php elseif ($walkover) :
    $kampbart_home = moustache_is_kampbart($home_team);
    $home_score = 0;
    $away_score = 0;

    if ($walkover_winner === 'kampbart') {
        if ($kampbart_home) {
            $home_score = $walkover_result;
        } else {
            $away_score = $walkover_result;
        }
    } else {
        if ($kampbart_home) {
            $away_score = $walkover_result;
        } else {
            $home_score = $walkover_result;
        }
    }

    $result_type = $walkover_winner === 'kampbart' ? 'u-tc--green' : 'u-tc--red';
?>
    <td class="<?php echo esc_attr($result_type); ?>">
        <?php printf('%d&ndash;%d', $home_score, $away_score); ?>
        <abbr title="<?php esc_attr_e('Walkover', 'moustache'); ?>"><?php esc_html_e('WO', 'moustache'); ?></abbr>
    </td>
<?php elseif ($unplayed_reason === 'canceled') : ?>
    <td>&mdash;</td>
<?php elseif ($result_only) : ?>
    <td>
        <?php
        if ($recorded['home_ft'] !== null && $recorded['away_ft'] !== null) {
            $ft = $recorded['home_ft'] . '–' . $recorded['away_ft'];
            if ($recorded['home_ht'] !== null && $recorded['away_ht'] !== null) {
                echo esc_html($ft . ' (' . $recorded['home_ht'] . '–' . $recorded['away_ht'] . ')');
            } else {
                echo esc_html($ft);
            }
        } elseif ($recorded['text_ht']) {
            printf(
                '%s (%s)',
                esc_html($recorded['text_ft']),
                esc_html($recorded['text_ht'])
            );
        } else {
            echo esc_html($recorded['text_ft']);
        }
        ?>
    </td>
<?php elseif (!$match_started || ($postponed && empty($new_date_time))) : ?>
    <td></td>
<?php else :
    $kampbart_goals_first_half = 0;
    $opponent_goals_first_half = 0;
    $kampbart_final = 0;
    $opponent_final = 0;

    foreach (moustache_get_goals($fixture_id) as $goal) {
        if ($goal['side'] === 'kampbart') {
            $kampbart_final++;
            if ($goal['half'] === 'first') {
                $kampbart_goals_first_half++;
            }
        } else {
            $opponent_final++;
            if ($goal['half'] === 'first') {
                $opponent_goals_first_half++;
            }
        }
    }

    if ($kampbart_final > $opponent_final) {
        $result_type = 'u-tc--green';
    } elseif ($kampbart_final === $opponent_final) {
        $result_type = 'u-tc--orange';
    } else {
        $result_type = 'u-tc--red';
    }

    $kampbart_home = moustache_is_kampbart($home_team);
    $home_final = $kampbart_home ? $kampbart_final : $opponent_final;
    $away_final = $kampbart_home ? $opponent_final : $kampbart_final;
    $home_ht = $kampbart_home ? $kampbart_goals_first_half : $opponent_goals_first_half;
    $away_ht = $kampbart_home ? $opponent_goals_first_half : $kampbart_goals_first_half;
?>
    <td class="<?php echo esc_attr($result_type); ?>">
        <?php printf('%d&ndash;%d (%d&ndash;%d)', $home_final, $away_final, $home_ht, $away_ht); ?>
    </td>
<?php endif; ?>
