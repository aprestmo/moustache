<?php

// =============================================================================
// VIEWS/RENEW/WP-HEADER.PHP
// -----------------------------------------------------------------------------
// Header output for Renew.
// =============================================================================

x_get_view( 'global', '_header' );
x_get_view( 'global', '_slider-above' );
?><header class="<?php x_masthead_class(); ?>" role="banner"><?php
x_get_view( 'global', '_topbar' );
x_get_view( 'global', '_navbar' );
?></header><?php
x_get_view( 'global', '_slider-below' );
x_get_view( 'renew', '_landmark-header' );