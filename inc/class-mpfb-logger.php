<?php
defined('ABSPATH') || exit;

class MPFB_Logger {
    public static $log_file;

    public static function init() {
        $upload_dir = wp_upload_dir();
        self::$log_file = trailingslashit($upload_dir['basedir']) . 'mpfb.log';
        if ( ! file_exists(self::$log_file) ) {
            @file_put_contents(self::$log_file, "mpfb log creato " . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        if ( file_exists(self::$log_file) && filesize(self::$log_file) > 1024 * 1024 * 3 ) {
            @file_put_contents(self::$log_file, "=== log rotazione " . date('Y-m-d H:i:s') . " ===" . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    public static function log($message) {
        if ( ! self::$log_file ) self::init();
        $time = date('Y-m-d H:i:s');
        $line = "[$time] mpfb - " . $message . PHP_EOL;
        @file_put_contents(self::$log_file, $line, FILE_APPEND | LOCK_EX);
    }
}
