<?php
/**
 * Plugin Name: WP Survey
 * Plugin URI: https://sawahsolutions.com
 * Description: Modern survey plugin with image support and Facebook integration
 * Version: 1.2.20
 * Author: Mohamed Sawah
 * Author URI: https://sawahsolutions.com
 * Text Domain: wp-survey
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) exit;

define('WP_SURVEY_VERSION', '1.2.20');
define('WP_SURVEY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_SURVEY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_SURVEY_PLUGIN_FILE', __FILE__);

require_once WP_SURVEY_PLUGIN_DIR . 'includes/class-wp-survey-database.php';
require_once WP_SURVEY_PLUGIN_DIR . 'includes/class-wp-survey-activator.php';
require_once WP_SURVEY_PLUGIN_DIR . 'admin/class-wp-survey-admin.php';
require_once WP_SURVEY_PLUGIN_DIR . 'public/class-wp-survey-public.php';

register_activation_hook(__FILE__, ['WP_Survey_Activator', 'activate']);

function wp_survey_init() {
    load_plugin_textdomain('wp-survey', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    if (is_admin()) {
        new WP_Survey_Admin();
    }
    
    new WP_Survey_Public();
}
add_action('plugins_loaded', 'wp_survey_init');
