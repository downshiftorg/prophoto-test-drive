<?php
/*
Plugin Name: ProPhoto Test Drive
Plugin URI: https://github.com/downshiftorg/prophoto-test-drive
Description: Test-drive and design with ProPhoto 6 while showing another theme to non-admin visitors
Author: ProPhoto
Version: 6.0.0
Author URI: http://www.prophoto.com
License: GPLv2
*/

if (pp_td_init_preconditions_met()) {
    add_action('plugins_loaded', 'pp_td_init');
}

/**
 * Check all preconditions for running plugin
 *
 * @return boolean
 */
function pp_td_init_preconditions_met() {
    $p6 = pp_td_get_p6_theme();

    if (null === $p6) {
        return false;
    }

    // 6.0.0-beta15 included safeguard for updating test-driven theme
    $p6Version = include("{$p6->get_stylesheet_directory()}/version.php");
    if (version_compare('6.0.0-beta15', $p6Version) === 1) {
        add_action('all_admin_notices', 'pp_td_old_zip_notice');
        return false;
    }

    if (pp_td_site_is_p6_compatible()) {
        return true;
    }

    add_action('all_admin_notices', 'pp_td_incapable_notice');

    return false;
}

/**
 * Get the ProPhoto 6 Theme object
 *
 * The documentation for wp_get_themes() says it is expensive, so
 * we cache the result with a local static variable for performance
 *
 * @return WP_Theme|null
 */
function pp_td_get_p6_theme() {
    static $p6 = false;

    if (false !== $p6) {
        return $p6;
    }

    foreach (wp_get_themes() as $theme) {
        if ((string) $theme === 'ProPhoto 6') {
            $p6 = $theme;
            return $p6;
        }
    }

    $p6 = null;
    return $p6;
}

/**
 * Is the site capable of running ProPhoto 6?
 *
 * @return boolean
 */
function pp_td_site_is_p6_compatible() {
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
    if (! current_user_can('edit_theme_options')) {
        return;
    }

    add_filter('template', 'pp_td_filter_active_theme');
    add_filter('stylesheet', 'pp_td_filter_active_stylesheet');
    add_filter('pp_admin_middleware', 'pp_td_filter_admin_middleware');

    $p6 = pp_td_get_p6_theme();

    if (isset($_GET['pp_td_test_drive'])) {
        update_option('pp_td_theme', $p6->get_template());
        return;
    }

    if (isset($_GET['pp_td_deactivate_td_mode'])) {
        delete_option('pp_td_theme');
        return;
    }

    if (isset($_GET['pp_td_activate_p6'])) {
        update_option('template', $p6->get_template());
        update_option('stylesheet', $p6->get_stylesheet());
        delete_option('pp_td_theme');
        return;
    }

    if (! get_option('pp_td_theme') && get_option('template') !== $p6->get_template()) {
        add_filter('admin_init', 'pp_td_non_p6_init');
    }
}

/**
 * Filter the active theme
 *
 * @param string $activeTheme
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
    $tdTheme = get_option('pp_td_theme');

    return empty($tdTheme) ? false : $tdTheme;
}

/**
 * Register function for test-drive admin notices on other themes
 *
 * @return void
 */
function pp_td_non_p6_init() {
    add_action('admin_notices', 'pp_td_offer_test_drive_admin_notice');
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
 * Show an admin notice offering to allow test-driving P6 from another theme
 *
 * @return void
 */
function pp_td_offer_test_drive_admin_notice() {
    $currentThemeName = pp_td_get_current_theme_name();
    $activate = admin_url('themes.php?activated=true&pp_td_test_drive=prophoto6');
    $msg  = 'It looks like you have installed <b>ProPhoto 6</b>. ';
    $msg .= "<a href='$activate'>Click here</a> to activate it <em>only for logged-in admin users</em> ";
    $msg .= "while continuing to show <b>$currentThemeName</b> to your normal site visitors.";
    echo pp_td_admin_notice($msg);
}

/**
 * Get the name of the passed theme template, or current active theme
 *
 * ProPhoto 2-5 all use "ProPhoto" as the theme name, so
 * append the major version for disambiguation
 *
 * @param string $template
 * @return string
 */
function pp_td_get_current_theme_name($template = null) {
    $theme = wp_get_theme($template);
    if ((string) $theme === 'ProPhoto') {
        return 'ProPhoto ' . intval($theme->get('Version'));
    }

    return (string) $theme;
}

/**
 * Render an error message for users who cannot test-drive because of PHP 5.2
 *
 * @return void
 */
function pp_td_incapable_notice() {
    $msg  = 'Your site is <b>not capable of running ProPhoto 6</b>. This is almost always caused ';
    $msg .= 'by running a version of PHP below 5.3. Please contact your webhost tech support ';
    $msg .= 'and ask them to upgrade you to <b>at least PHP 5.3</b> <em>(5.5 or 5.6 is ideal)</em>.';
    echo pp_td_admin_error($msg);
}

/**
 * Render an error message for users with < beta15 installed
 *
 * @return void
 */
function pp_td_old_zip_notice() {
    $msg  = 'The ProPhoto 6 build you have installed is <b>too old to be test-driven safely</b>. ';
    $msg .= 'Please contact ProPhoto support to get a more recent zip file to replace it.';
    echo pp_td_admin_error($msg);
}

/**
 * Helper function for rendering a formatted admin notice
 *
 * @param string $msg
 * @return string
 */
function pp_td_admin_notice($msg) {
    return pp_td_admin_msg($msg, 'updated');
}

/**
 * Helper function for rendering a formatted admin error
 *
 * @param string $msg
 * @return string
 */
function pp_td_admin_error($msg) {
    return pp_td_admin_msg($msg, 'error');
}

/**
 * Helper function for rendering a formatted admin message
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
        $p6 = pp_td_get_p6_theme();
        if (get_option('template') !== $p6->get_template()) {
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
        $activeTheme = pp_td_get_current_theme_name(get_option('template'));
        $msg  = 'You are currently <b style="text-decoration: underline;">test-driving ProPhoto 6</b>. ';
        $msg .= 'Logged-in users can see and customize P6, but all normal ';
        $msg .= "site visitors will see your site with the <b>$activeTheme theme</b> active. ";
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
            $p6 = pp_td_get_p6_theme();
            $container = include("{$p6->get_stylesheet_directory()}/services.php");
            if (! $container) {
                return false;
            }
            $site = $container->make('ProPhoto\Core\Model\Site\Site');
            return $site->isRegistered();
        } catch (Exception $e) {
            return false;
        }
    }
}
