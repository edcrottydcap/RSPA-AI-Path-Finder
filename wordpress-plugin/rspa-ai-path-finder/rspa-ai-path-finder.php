<?php
/**
 * Plugin Name:       RSPA AI Path Finder
 * Plugin URI:        https://github.com/edcrottydcap/RSPA-AI-Path-Finder
 * Description:       The RSPA AI Path Finder quiz — six questions, one personalized AI curriculum printed as a receipt. Opens in a popup from a floating button, a card, or the [pathfinder] shortcode; also available as a block, a widget, and its own page.
 * Version:           1.2.0
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Author:            Ed Crotty, for the RSPA AI Resource Hub
 * Author URI:        https://github.com/edcrottydcap/RSPA-AI-Path-Finder
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rspa-ai-path-finder
 *
 * @package RSPA_AI_Path_Finder
 */

defined( 'ABSPATH' ) || exit;

define( 'RSPA_APF_VERSION', '1.2.0' );
define( 'RSPA_APF_FILE', __FILE__ );
define( 'RSPA_APF_DIR', plugin_dir_path( __FILE__ ) );
define( 'RSPA_APF_URL', plugin_dir_url( __FILE__ ) );
define( 'RSPA_APF_OPTION', 'rspa_apf_settings' );

require_once RSPA_APF_DIR . 'includes/settings.php';
require_once RSPA_APF_DIR . 'includes/endpoint.php';
require_once RSPA_APF_DIR . 'includes/embed.php';
require_once RSPA_APF_DIR . 'includes/widget.php';

register_activation_hook( __FILE__, 'rspa_apf_activate' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Register the quiz URL and flush rewrite rules so it works right away.
 */
function rspa_apf_activate() {
	rspa_apf_add_rewrite_rule();
	flush_rewrite_rules();
}
