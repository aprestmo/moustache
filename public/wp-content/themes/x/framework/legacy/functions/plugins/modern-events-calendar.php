<?php

// =============================================================================
// FUNCTIONS/GLOBAL/PLUGINS/MODERN-EVENT-CALENDAR.PHP
// -----------------------------------------------------------------------------
// Plugin setup for theme compatibility.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Filter Container Classes
// =============================================================================

// Filter Container Classes
// =============================================================================

function x_mec_container_class() {
  return 'x-container max width';
}

add_filter( 'mec_single_page_html_class', 'x_mec_container_class' );
add_filter( 'mec_archive_page_html_class', 'x_mec_container_class' );
add_filter( 'mec_category_page_html_class', 'x_mec_container_class' );
