<?php
/*
Plugin Name: Techvila image optimization and cdn
Plugin URI: https://techvila.com/free-cdn
Description: Optimize images and speed up your site techvila cdn
Version: 1.0.0
Author: Ruhul Amin
Text Domain: techvila-cdn
*/
define('tioa_cdn', 'cdn25.techvila.com/'.parse_url( get_site_url(), PHP_URL_HOST )); 
define('TIOA_ENABLED', TRUE);

add_action( 'init', 'tioa_cdn_rewrite_init', 5 );
if ( ! function_exists( 'tioa_cdn_rewrite_init' ) ):

function tioa_cdn_rewrite_init() {
    // Only bother if the CDN is enabled
    if ( defined( 'TIOA_ENABLED' ) && TIOA_ENABLED ) {
        add_action( 'template_redirect', 'tioa_cdn_rewrite_buffer_start' );
    }
}

endif;

// Define action for starting and flushing output buffers
if ( ! function_exists( 'tioa_cdn_rewrite_buffer_start' ) ):

function tioa_cdn_rewrite_buffer_flush( $content ) {
    // setup the domains that will be replaced
    $extensions = array( 'jpe?g', 'gif', 'png', 'css', 'bmp', 'js', 'ico' );
    $domain = parse_url( get_bloginfo( 'url' ), PHP_URL_HOST );
    $cdn = ( defined( 'tioa_cdn' ) ) ? explode( '|', tioa_cdn ) : NULL; // just in case
    if ( is_null( $cdn ) ) {
        return $content;
    }
    // how many replacement domains have been specfied
    $nDomains = count($cdn); $cdnIdx = 0; $rCount = 1;
    if ( 0 === $nDomains ) {
        return preg_replace( "#=([\"'])(https?://{$domain})?/([^/](?:(?!\\1).)+)\.(" . implode( '|', $extensions ) . ")(\?((?:(?!\\1).)+))?\\1#",
            '=$1//' . $cdn[$cdnIdx] . '/$3.$4$5$1', $content );
    } else {
        // loop over the content until no more replacements are required
        while( $rCount > 0 ) {
            $content = preg_replace( "#=([\"'])(https?://{$domain})?/([^/](?:(?!\\1).)+)\.(" . implode( '|', $extensions ) . ")(\?((?:(?!\\1).)+))?\\1#",
                '=$1//' . $cdn[$cdnIdx] . '/$3.$4$5$1', $content, 1, $rCount );
            $cdnIdx = ( $cdnIdx + 1) % $nDomains;
        }
        // and finally output the modified content
        return $content;
    }
}

function tioa_cdn_rewrite_buffer_start() {
    ob_start( 'tioa_cdn_rewrite_buffer_flush' );
}

endif;