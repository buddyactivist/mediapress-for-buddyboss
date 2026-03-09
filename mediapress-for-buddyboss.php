<?php
/*
Plugin Name: MediaPress for BuddyBoss
Plugin URI:  https://example.org
Description: Patch di compatibilità per MediaPress su BuddyBoss Platform e temi come ReadyLaunch.
Version:     0.2.0
Author:      mediapress-for-buddyboss
Text Domain: mediapress-for-buddyboss
*/

defined('ABSPATH') || exit;

if ( ! class_exists('MPFB_Compat') ) {
    require_once __DIR__ . '/inc/class-mpfb-logger.php';
    require_once __DIR__ . '/inc/class-mpfb-compat.php';

    add_action('plugins_loaded', function(){
        MPFB_Logger::init();
        MPFB_Compat::init();
    }, 5);
}
