<?php
define( 'WP_CACHE', true );


/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'kampbart_1665138869' );
/** MySQL database username */
define( 'DB_USER', 'kampbart_1665138869' );
/** MySQL database password */
define( 'DB_PASSWORD', 'C5ZqP4Qqx0wLp2EMgx3GQr7Gbi1zgPtML8F99YpD28rYHCmfod65J' );
/** MySQL hostname */
define('DB_HOST', 'localhost');
/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');
/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
define('DISABLE_WP_CRON', true);
define('DISALLOW_FILE_EDIT', TRUE);
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'y6606^I>0D|VE)g%-`y{&fklPdgv+Ib0h<|8a4r^N1UAqC>V`5%m!aquc.XN6Q=^');
define('SECURE_AUTH_KEY',  'u7pB,@9Yh|P2Y!Q~=8<NK7oBgE&Xc7i`ghDh3X_0Llo;Vf!d:iw^Osz~eP|4-@U4');
define('LOGGED_IN_KEY',    '/ #30WA|z5pOhqv_RuMf/sxl{1bY~H>rb~rO,s.NIxyIWY5OsVOR+Pz2eD25E-oQ');
define('NONCE_KEY',        'r//UX/AMX`m!%.jko9@dv_!nqQ+GLiLBcUq$0|66-iJ!s.}?0jyJU|[;+,y|MWVk');
define('AUTH_SALT',        '.u m)ic> t!L:5`S{zY?xA]^>/ZYOG7IlYHCFCa<GOsgm1>QI|tVL-8j;~xE#6;E');
define('SECURE_AUTH_SALT', '^{`+Pcq+lxOM:DajY4DHSo72(FZ5`lPEakYf~zrKFm0/mc.z^]lXR{/M}+t(E>aP');
define('LOGGED_IN_SALT',   '{}HWfkZ9f[ipTH?}#lKZ)Eq.QS-gMgzB0n#l9fTN|8x$pW5U GqfI5<+nED)&<HY');
define('NONCE_SALT',       'VG8.Teoy6o;qWhi=S>*q:IgI1^>p7&]-_%/1/7TBn-vCC!_a-e<T6||q{C3i|0Gq');
/**#@-*/
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'kb_';
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
/**
 * Debug defines
 *
 */
define('SCRIPT_DEBUG', false);
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);
define('SAVEQUERIES', false);

/**
 * Disable Auto Update
 *
 */
define( 'WP_AUTO_UPDATE_CORE', true );

/**
 * Google Maps API Key
 */
 define('GOOGLE_MAPS_API_KEY', 'AIzaSyBAIR41UOwT9MHOsZ3M_5KP3KsLYVPO1xo');

/* That's all, stop editing! Happy blogging. */
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');