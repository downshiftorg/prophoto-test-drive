<?php
/*
Plugin Name: ProPhoto Test Drive
Plugin URI: https://github.com/netrivet/prophoto-test-drive
Description: Test-drive and design with ProPhoto 6 while showing ProPhoto 5 to non-admin visitors
Author: ProPhoto
Version: 6.0.0
Author URI: http://www.prophoto.com
License: GPLv2
*/

if (pp_td_theme_installed('prophoto5') && pp_td_theme_installed('prophoto6') && pp_td_site_p6_ready()) {
    add_action('plugins_loaded', 'pp_td_init');
}

/**
 * Does a theme exist with the directory name?
 *
 * @param string $themeDir
 * @return boolean
 */
function pp_td_theme_installed($themeDir) {
    return file_exists(WP_CONTENT_DIR . "/themes/$themeDir/style.css");
}

/**
 * Is the site capable of running ProPhoto 6?
 *
 * @return boolean
 */
function pp_td_site_p6_ready() {
    $phpCompatible = function_exists('stream_supports_lock');
    $wpCompatible = function_exists('__return_empty_string');
    $gdCompatible = function_exists('imagecreatetruecolor');
    $jsonCompatible = extension_loaded('json');

    return $phpCompatible && $wpCompatible && $gdCompatible && $jsonCompatible;
}

/**
 * Initialize the ProPhoto test drive plugin
 *
 * @return void
 */
function pp_td_init() {
    add_filter('template', 'pp_td_filter_active_theme');
    add_filter('stylesheet', 'pp_td_filter_active_stylesheet');
    add_filter('pp_classes_loaded', 'pp_td_p5_init');
    add_filter('pp_admin_middleware', 'pp_td_filter_admin_middleware');

    if (! current_user_can('edit_theme_options')) {
        return;
    }

    if (isset($_GET['pp_td_test_drive'])) {
        update_option('pp_td_theme', 'prophoto6');
    }

    if (isset($_GET['pp_td_deactivate_td_mode'])) {
        delete_option('pp_td_theme');
    }

    if (isset($_GET['pp_td_activate_p6'])) {
        update_option('template', 'prophoto6');
        update_option('stylesheet', 'prophoto6');
        delete_option('pp_td_theme');
    }
}

/**
 * Filter the active theme
 *
 * @param string $activeTheme - directory of active theme (`prophoto5` or `prophoto6`)
 * @return string
 */
function pp_td_filter_active_theme($activeTheme) {
    $tdTheme = pp_td_determine_theme();
    if (false === $tdTheme) {
        return $activeTheme;
    }

    return $tdTheme;
}

/**
 * Filter the active theme stylesheet
 *
 * @param string $stylesheet
 * @return string
 */
function pp_td_filter_active_stylesheet($stylesheet) {
    $tdTheme = pp_td_determine_theme();
    if (false === $tdTheme) {
        return $stylesheet;
    }

    return $tdTheme;
}

/**
 * Determine the test drive theme, if applicable
 *
 * @return string|false
 */
function pp_td_determine_theme() {
    if (! current_user_can('edit_theme_options')) {
        return false;
    }

    $tdTheme = get_option('pp_td_theme');

    return empty($tdTheme) ? false : $tdTheme;
}

/**
 * Register function for showing ProPhoto 5 test-drive admin notice
 *
 * @return void
 */
function pp_td_p5_init() {
    add_action('admin_notices', 'pp_td_p5_admin_notice');
}

/**
 * Register middleware for ProPhoto 6 to display test-drive admin notice
 *
 * @param array $adminMiddlewares
 * @return array
 */
function pp_td_filter_admin_middleware(array $adminMiddlewares) {
    $adminMiddlewares['ProPhotoTestDriveAdminMiddleware'] = true;
    return $adminMiddlewares;
}

/**
 * The admin notice for ProPhoto 5
 *
 * @return void
 */
function pp_td_p5_admin_notice() {
    $activate = admin_url('themes.php?activated=true&pp_td_test_drive=prophoto6');
    $msg  = 'It looks like you have installed <b>ProPhoto 6</b>. ';
    $msg .= "<a href='$activate'>Click here</a> to activate it <em>only for logged-in admin users</em> ";
    $msg .= 'while continuing to show <b>ProPhoto 5</b> to your normal site visitors.';
    echo pp_td_admin_notice($msg);
}

/**
 * Helper function for rendering a formatted admin notice in ProPhoto 5 or 6
 *
 * @param string $msg
 * @return string
 */
function pp_td_admin_notice($msg) {
    return pp_td_admin_msg($msg, 'updated');
}

/**
 * Helper function for rendering a formatted admin error in ProPhoto 5 or 6
 *
 * @param string $msg
 * @return string
 */
function pp_td_admin_error($msg) {
    return pp_td_admin_msg($msg, 'error');
}

/**
 * Helper function for rendering a formatted admin message in ProPhoto 5 or 6
 *
 * @param string $msg
 * @return string
 */
function pp_td_admin_msg($msg, $class) {
    return "<div class='$class pp-admin-msg'><p style='margin: 0.75em'>$msg</p></div>";
}

/**
 * Admin middleware class for hooking functionality into ProPhoto6
 */
class ProPhotoTestDriveAdminMiddleware
{
    /**
     * If ProPhoto 6 is being test driven (but is not truly active), show an admin notice
     */
    public function __construct()
    {
        if (get_option('template') !== 'prophoto6') {
            add_action('admin_notices', array($this, 'addAdminNotice'));
        }
    }

    /**
     * Add admin notice explaining that ProPhoto 6 is being test-driven
     */
    public function addAdminNotice()
    {
        $deactivateTdMode = admin_url('?pp_td_deactivate_td_mode=1');

        if (! $this->p6SiteRegistered()) {
            $activate = admin_url('themes.php?activated=true');
            $msg  = 'You are currently <b>test-driving ProPhoto 6</b>, but ProPhoto 6 ';
            $msg .= 'is <b style="text-decoration: underline;">not registered.</b> ';
            $msg .= 'This means you will not receive critical updates and bugfixes. ';
            $msg .= "To register <a href='$activate'>click here</a>, or you may choose instead to ";
            $msg .= "<a href='$deactivateTdMode'>switch out of test-drive mode</a>.";
            echo pp_td_admin_error($msg);
            return;
        }

        $activateP6 = admin_url('?pp_td_activate_p6=1');
        $msg  = 'You are currently <b style="text-decoration: underline;">test-driving ProPhoto 6</b>. ';
        $msg .= 'Logged-in users can see and customize P6, but all normal ';
        $msg .= 'site visitors will see your P5 site. ';
        $msg .= "<a href='$deactivateTdMode'>Click here</a> to switch out of test-drive mode, or ";
        $msg .= "<a href='$activateP6'>here</a> to fully activate P6 for all users.";
        echo pp_td_admin_notice($msg);
    }

    /**
     * Is the ProPhoto 6 site registeredd
     *
     * Make a container instead of type-hinting the constructor because this plugin can run
     * in PHP 5.2 environments that choke on namespace syntax.
     *
     * @return boolean
     */
    protected function p6SiteRegistered()
    {
        try {
            $container = include(WP_CONTENT_DIR . '/themes/prophoto6/services.php');
            $site = $container->make('ProPhoto\Core\Model\Site\Site');
            return $site->isRegistered();
        } catch (Exception $e) {
            return false;
        }
    }
}
