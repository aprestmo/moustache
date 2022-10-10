<?php

/**
 * Enable upload of additional filetypes in media library
 *
 * @since 1.0
 */
add_filter('upload_mimes', function ($mimes) {

  $mime_types = array(
    // 'ac3' => 'audio/ac3',
    // 'mpa' => 'audio/MPA',
    // 'flv' => 'video/x-flv',
    // 'ai' => 'application/postscript',
    // 'eps' => 'application/postscript',
    // 'ppt' => 'application/vnd.ms-powerpoint',
    // 'pps' => 'application/vnd.ms-powerpoint',
    'svg' => 'image/svg+xml'
  );

  return array_merge($mimes, $mime_types);
});
