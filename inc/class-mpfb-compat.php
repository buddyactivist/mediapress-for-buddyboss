<?php
defined('ABSPATH') || exit;

class MPFB_Compat {

    const LOG_PREFIX = 'mpfb';

    public static function init() {
        add_filter('template_include', [__CLASS__, 'force_page_template_for_mediapress'], 20);
        add_action('init', [__CLASS__, 'register_bp_fallbacks'], 1);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_compat_assets'], 999);
        add_action('wp', [__CLASS__, 'maybe_add_body_class'], 20);
        add_action('admin_notices', [__CLASS__, 'maybe_show_admin_notice']);
    }

    public static function force_page_template_for_mediapress($template) {
        if ( is_admin() ) return $template;

        $post_id = get_queried_object_id();
        if ( $post_id && self::post_has_mediapress($post_id) ) {
            $candidates = ['page.php', 'index.php'];
            foreach ($candidates as $file) {
                $found = locate_template($file);
                if ( $found ) {
                    MPFB_Logger::log('Forzato template ' . $file . ' per post_id ' . $post_id);
                    return $found;
                }
            }
        }
        return $template;
    }

    public static function post_has_mediapress($post_id) {
        if ( ! $post_id ) return false;

        $content = get_post_field('post_content', $post_id);
        if ( $content && has_shortcode( $content, 'mediapress' ) ) return true;

        $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
        $checks = ['/gallery', '/mediapress', '/media', '/mpp'];
        foreach ($checks as $c) {
            if ( stripos($uri, $c) !== false ) return true;
        }

        $template_slug = get_page_template_slug( $post_id );
        if ( $template_slug && ( stripos($template_slug, 'blank') !== false || stripos($template_slug, 'landing') !== false ) ) {
            return true;
        }

        return false;
    }

    public static function register_bp_fallbacks() {
        if ( ! function_exists('bp_is_active') ) {
            function bp_is_active($component = '') { return false; }
            MPFB_Logger::log('Fallback bp_is_active registrata');
        }

        if ( ! function_exists('bp_get_template_part') ) {
            function bp_get_template_part($slug, $name = null) {
                $file = $name ? "{$slug}-{$name}.php" : "{$slug}.php";
                if ( locate_template($file) ) {
                    locate_template($file, true, false);
                } else {
                    MPFB_Logger::log("bp_get_template_part fallback: template non trovato {$file}");
                }
            }
            MPFB_Logger::log('Fallback bp_get_template_part registrata');
        }

        if ( ! function_exists('bp_core_get_userlink') ) {
            function bp_core_get_userlink($user_id = 0) {
                $user_id = intval($user_id);
                $user = get_userdata($user_id);
                if ( ! $user ) return '';
                return esc_html($user->display_name);
            }
            MPFB_Logger::log('Fallback bp_core_get_userlink registrata');
        }
    }

    public static function enqueue_compat_assets() {
        $handle_css = 'mpfb-compat-css';
        $css_url = plugins_url('assets/css/mpfb-compat.css', __DIR__ . '/../');
        wp_register_style($handle_css, $css_url, [], '0.2.0');
        wp_enqueue_style($handle_css);

        $extra = "
        .mpfb-compat-active .site-header, .mpfb-compat-active header.site-header, .mpfb-compat-active .bb-header, .mpfb-compat-active .rl-header, .mpfb-compat-active .readylaunch-header {
            display:block !important; visibility:visible !important; z-index:9999 !important;
        }
        .mpfb-compat-active .site-footer, .mpfb-compat-active footer.site-footer, .mpfb-compat-active .bb-footer, .mpfb-compat-active .rl-footer, .mpfb-compat-active .readylaunch-footer {
            display:block !important; visibility:visible !important;
        }";
        wp_add_inline_style($handle_css, $extra);
    }

    public static function maybe_add_body_class() {
        if ( is_admin() ) return;
        $post_id = get_queried_object_id();
        if ( self::post_has_mediapress($post_id) ) {
            add_filter('body_class', function($classes){
                $classes[] = 'mpfb-compat-active';
                return $classes;
            });
            MPFB_Logger::log('Aggiunta body class mpfb-compat-active per post ' . $post_id);
        }
    }

    public static function maybe_show_admin_notice() {
        if ( ! current_user_can('manage_options') ) return;
        $post_id = get_queried_object_id();
        if ( $post_id && self::post_has_mediapress($post_id) ) {
            echo '<div class="notice notice-info is-dismissible"><p><strong>mediapress-for-buddyboss</strong> compatibilità MediaPress attiva. Testa la pagina galleria su staging e controlla il log in wp-content/uploads/mpfb.log</p></div>';
        }
    }
}
