<?php

/**
 * Single player template
 */
get_header(); ?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
  <div class="o-grid u-text-center">
    <div class="o-grid__item u-1/1 u-2/3@sm">
      <section class="o-section-md">

        <h1><?php the_title(); ?></h1>

        <?php
        $image = get_field('image');
        if (!empty($image)) :
        ?>
          <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php _e('Portrait of ', 'moustache');
                                                                  the_title(); ?>">
        <?php else : ?>
          <img src="" alt="">
        <?php endif; ?>

        <?php if (get_field('shirt_number')) : ?>
          <dl>
            <dt><?php esc_html_e('Shirt Number', 'moustache'); ?>:</dt>
            <dd><?php esc_html_e(get_field('shirt_number')); ?></dd>
          </dl>
        <?php endif; ?>

        <?php if (get_field('shirt_name')) : ?>
          <dl>
            <dt><?php esc_html_e('Shirt Name', 'moustache'); ?>:</dt>
            <dd><?php esc_html_e(get_field('shirt_name')); ?></dd>
          </dl>
        <?php endif; ?>

        <?php
        if (!empty(get_field('dob'))) {
          _e('Date of Birth', 'moustache');
          echo get_field('dob');
        }
        ?>

        <?php
        $labels    = get_field_object('position');
        $positions = get_field('position');

        if (!empty($positions)) {
          _e('Position', 'moustache');
          foreach ($positions as $position) {
            echo $labels['choices'][$position];
          }
        }
        ?>

        <?php
        if (!empty(get_field('former_clubs'))) {
          _e('Former Clubs', 'moustache');
          echo get_field('former_clubs');
        }
        ?>

        <?php
        if (!empty(get_field('best_memory'))) {
          _e('Best Memory', 'moustache');
          echo get_field('best_memory');
        }
        ?>

        <hr>

        <?php
        $id = get_the_ID();

        $posts = get_posts([
          'post_type' => 'fixture',
          'fields' => 'ids',
          'nopaging' => true,
          'posts_per_page' => -1,
          'meta_key' => 'present',
          'meta_value' => '"' . $id . '"',
          'meta_compare' => 'LIKE',
        ]);

        $stats = [];
        foreach ($posts as $p) {
          $terms = get_the_terms($p, 'tournament');

          foreach ($terms as $term) {
            if (!$stats[$term->name]) {
              $stats[$term->name] = new StdClass();
              $stats[$term->name]->count = 0;
              $stats[$term->name]->name = $term->name;
              $stats[$term->name]->ids = [];
              $stats[$term->name]->goalsFirst = 0;
              $stats[$term->name]->goalsSecond = 0;
              $stats[$term->name]->assistsFirst = 0;
              $stats[$term->name]->assistsSecond = 0;
              $stats[$term->name]->yellowCardFirst = 0;
              $stats[$term->name]->yellowCardSecond = 0;
              $stats[$term->name]->redCardFirst = 0;
              $stats[$term->name]->redCardSecond = 0;
            }

            $stats[$term->name]->ids[] = $p;
            $stats[$term->name]->count++;

            /* Goals */

            while (have_rows('goals_assists_first_half', $p)) {
              the_row();

              $scorer = get_sub_field('goal_scorer_first_half');

              if ($scorer) {
                if ($scorer->ID === $id) {
                  $stats[$term->name]->goalsFirst++;
                }
              }
            }

            while (have_rows('goals_assists_second_half', $p)) {
              the_row();

              $scorer = get_sub_field('goal_scorer_second_half');
              if ($scorer) {
                if ($scorer->ID === $id) {
                  $stats[$term->name]->goalsSecond++;
                }
              }
            }

            /* Assists */

            while (have_rows('goals_assists_first_half', $p)) {
              the_row();

              $assists = get_sub_field('assist_first_half');

              if ($assists) {
                if ($assists->ID === $id) {
                  $stats[$term->name]->assistsFirst++;
                }
              }
            }

            while (have_rows('goals_assists_second_half', $p)) {
              the_row();

              $assists = get_sub_field('assist_second_half');

              if ($assists) {
                if ($assists->ID === $id) {
                  $stats[$term->name]->assistsSecond++;
                }
              }
            }

            /* Yellow Cards */

            $yellowCards = get_sub_field('yellow_card_first_half', $p);

            if ($yellowCards) {
              foreach ($yellowCards as $yellowCard) {
                if ($yellowCard->ID === $id) {
                  $stats[$term->name]->yellowCardsFirst++;
                }
              }
            }

            $yellowCards = get_field('yellow_card_second_half', $p);

            if ($yellowCards) {
              foreach ($yellowCards as $yellowCard) {
                if ($yellowCard->ID === $id) {
                  $stats[$term->name]->yellowCardsSecond++;
                }
              }
            }

            /* Red Cards */

            $redCards = get_sub_field('red_cards_first_half');

            if ($redCards) {
              foreach ($redCards as $redCard) {
                if ($redCard->ID === $id) {
                  $stats[$term->name]->redCardsFirst++;
                }
              }
            }

            $redCards = get_field('red_cards_second_half');

            if ($redCards) {
              foreach ($redCards as $redCard) {
                if ($redCard->ID === $id) {
                  $stats[$term->name]->redCardsSecond++;
                }
              }
            }
          }
        }
        ?>

        <table>
          <caption><?php esc_html_e('Player Stats', 'moustache'); ?></caption>
          <thead>
            <tr>
              <th>Sesong</th>
              <th>Kamper</th>
              <th>Mål</th>
              <th>Assists</th>
              <th>Gule kort</th>
              <th>Røde kort</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($stats as $key => $playerstat) {
              $season = $playerstat->name;
              $matches = $playerstat->count;
              $goals = $playerstat->goalsFirst + $playerstat->goalsSecond;
              $assists = $playerstat->assistsFirst + $playerstat->assistsSecond;
              $yellowCards = $playerstat->yellowCardsFirst + $playerstat->yellowCardsSecond;
              $redCards = $playerstat->redCardsFirst + $playerstat->redCardsSecond;

              echo '<tr>';
              echo '<td>' . esc_html($season) . '</td>';
              echo '<td>' . esc_html($matches) . '</td>';
              echo '<td>' . esc_html($goals) . '</td>';
              echo '<td>' . esc_html($assists) . '</td>';
              echo '<td>' . esc_html($yellowCards) . '</td>';
              echo '<td>' . esc_html($redCards) . '</td>';
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>

      </section>
    </div>
  </div>
</div>

<?php get_footer(); ?>
