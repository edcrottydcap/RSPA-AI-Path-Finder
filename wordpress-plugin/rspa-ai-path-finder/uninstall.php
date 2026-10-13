<?php
/**
 * Remove the plugin's settings when it is deleted (not merely deactivated).
 *
 * @package RSPA_AI_Path_Finder
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'rspa_apf_settings' );
delete_option( 'rspa_apf_flush' );
