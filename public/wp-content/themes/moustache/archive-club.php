<?php

/**
 * Clubs overview template
 *
 * @package Moustache 5
 */

get_header(); ?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg u-soft-top-md u-push-bottom-lg">
    <?php
    $posts = get_posts(
        array(
            'numberposts' => -1,
            'post_type'   => 'club',
            'orderby'     => 'title',
            'order'       => 'ASC',
        )
    );

    if ($posts) :
    ?>

        <h2><?php esc_html_e('Klubboversikt', 'moustache'); ?></h2>
        <ul class="u-soft-top-md u-flush-left">
            <?php foreach ($posts as $post) : ?>
                <li class="o-grid__item u-1/2 u-1/5@md">
                    <a href="/<?php echo ($post->post_name); ?>/">
                        <?php echo get_the_title($post->ID); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
