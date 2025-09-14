<?php

// =============================================================================
// VIEWS/INTEGRITY/WP-HEADER.PHP
// -----------------------------------------------------------------------------
// Header output for Integrity.
// =============================================================================

x_get_view( 'global', '_header' );
x_get_view( 'global', '_slider-above' );
?><header class="<?php x_masthead_class(); ?>" role="banner"><?php
x_get_view( 'global', '_topbar' );
x_get_view( 'global', '_navbar' );
x_get_view( 'integrity', '_breadcrumbs' );
?></header><?php
x_get_view( 'global', '_slider-below' );
x_get_view( 'integrity', '_landmark-header' );