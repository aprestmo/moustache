<?php

return [

  // Legacy Includes (Classic Stacks)
  'preinit' => [
    'legacy/stack-defaults',
    'legacy/functions/helpers',
    'legacy/functions/frontend/view-routing',
    'legacy/functions/thumbnails',
    'legacy/functions/setup',
    'legacy/functions/fonts',
    'legacy/functions/plugins/cornerstone',

    // Plugin Integrations (Classic Stacks only)
    [ class_exists( 'bbPress' ), 'legacy/functions/plugins/bbpress' ],
    [ get_option("x_buddypress_enable", true) && class_exists( 'BuddyPress' ), 'legacy/functions/plugins/buddypress' ],
    [ class_exists( 'WPCF7_ContactForm' ), 'legacy/functions/plugins/contact-form-7' ],
    [ class_exists( 'GFForms' ), 'legacy/functions/plugins/gravity-forms' ],
    [ class_exists( 'LS_Sliders' ), 'legacy/functions/plugins/layerslider' ],
    [ class_exists( 'MEC' ), 'legacy/functions/plugins/modern-events-calendar'],
    [ class_exists( 'RevSlider' ), 'legacy/functions/plugins/revolution-slider' ],
    [ defined( 'WPB_VC_VERSION' ), 'legacy/functions/plugins/visual-composer' ],
    [ class_exists( 'WC_API' ), 'legacy/functions/plugins/woocommerce' ],
    [ defined( 'ICL_SITEPRESS_VERSION' ), 'legacy/functions/plugins/wpml' ],

    'legacy/functions/updates/class-x-tgmpa-integration',
    'legacy/functions/updates/class-tgm-plugin-activation',
    'legacy/cranium/setup',
    'legacy/setup',

  ],
  'init' => [
    'legacy/functions/frontend/conditionals',
  ],
  'front_end' => array(
    'legacy/functions/frontend/breadcrumbs',
    // Theme
    'legacy/functions/frontend/portfolio',
    'legacy/functions/frontend/view-routing',
    'legacy/functions/frontend/styles',
    'legacy/functions/frontend/scripts',
    'legacy/functions/frontend/content',
    'legacy/functions/frontend/classes',
    'legacy/functions/frontend/meta',
    'legacy/functions/frontend/integrity',
    'legacy/functions/frontend/renew',
    'legacy/functions/frontend/icon',
    'legacy/functions/frontend/ethos',
    'legacy/functions/frontend/social',
    'legacy/functions/frontend/breadcrumbs',
    'legacy/functions/frontend/pagination',
    'legacy/functions/frontend/featured'
  ),
  'logged_in' => [],
  'admin' => [
    'legacy/functions/admin/class-validation',
    'legacy/functions/updates/class-theme-updater',
    'legacy/functions/updates/class-plugin-updater',
    'legacy/functions/admin/class-validation-updates',
    'legacy/functions/admin/class-validation-theme-options-manager',
    'legacy/functions/admin/class-validation-extensions',
    'legacy/functions/admin/setup',

    // Theme
    'legacy/functions/admin/customizer',
    'legacy/functions/admin/meta-boxes',
    'legacy/functions/admin/meta-entries',
    'legacy/functions/admin/taxonomies'
  ],
  'app_init' => [
    'legacy/functions/theme-options-controls',
  ]
];
