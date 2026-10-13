<?php
/**
 * Serves the quiz itself at /ai-path-finder/ (the slug is configurable).
 *
 * The same URL is the standalone quiz page and the iframe source used by the
 * block, shortcode, and widget (with ?embed=1). The widget is the repo's
 * index.html, copied to app/path-finder.html by scripts/build-plugin.mjs; this
 * file only injects settings into it.
 *
 * @package RSPA_AI_Path_Finder
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'rspa_apf_add_rewrite_rule' );
add_filter( 'query_vars', 'rspa_apf_query_vars' );
add_action( 'template_redirect', 'rspa_apf_serve' );

/**
 * Pretty URL: /{slug}/ → index.php?rspa_apf=1.
 */
function rspa_apf_add_rewrite_rule() {
	$slug = rspa_apf_setting( 'slug' );
	add_rewrite_rule( '^' . preg_quote( $slug, '/' ) . '/?$', 'index.php?rspa_apf=1', 'top' );
}

/**
 * Let WordPress read our query var (also makes /?rspa_apf=1 work without
 * pretty permalinks).
 *
 * @param string[] $vars Public query vars.
 * @return string[]
 */
function rspa_apf_query_vars( $vars ) {
	$vars[] = 'rspa_apf';
	return $vars;
}

/**
 * Public URL of the quiz page.
 *
 * @param array $args Optional query args (embed, r, host, preview).
 * @return string
 */
function rspa_apf_quiz_url( $args = array() ) {
	if ( get_option( 'permalink_structure' ) ) {
		$url = home_url( user_trailingslashit( rspa_apf_setting( 'slug' ) ) );
	} else {
		$url = add_query_arg( 'rspa_apf', '1', home_url( '/' ) );
	}
	return $args ? add_query_arg( array_map( 'rawurlencode', $args ), $url ) : $url;
}

/**
 * A result code from ?r= (e.g. "201i"), or '' if absent or malformed.
 *
 * @param mixed $raw Raw value.
 * @return string
 */
function rspa_apf_result_code( $raw ) {
	$raw = is_string( $raw ) ? $raw : '';
	return preg_match( '/^(101|102|201|301)[fpim]?$/', $raw ) ? $raw : '';
}

/**
 * Widget version info read from the bundled HTML's meta tags.
 *
 * @return array{version:string,content_date:string}
 */
function rspa_apf_widget_info() {
	$html = rspa_apf_widget_html();
	$info = array(
		'version'      => '',
		'content_date' => '',
	);
	if ( preg_match( '/name="rspa-pathfinder-version" content="([^"]+)"/', $html, $m ) ) {
		$info['version'] = $m[1];
	}
	if ( preg_match( '/name="rspa-pathfinder-content-date" content="([^"]+)"/', $html, $m ) ) {
		$info['content_date'] = $m[1];
	}
	return $info;
}

/**
 * The bundled widget HTML.
 *
 * @return string
 */
function rspa_apf_widget_html() {
	static $html = null;
	if ( null === $html ) {
		$file = RSPA_APF_DIR . 'app/path-finder.html';
		$html = is_readable( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
	return $html;
}

/**
 * Output the quiz page and stop WordPress from rendering a theme template.
 */
function rspa_apf_serve() {
	if ( ! get_query_var( 'rspa_apf' ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only, public page.
	$embed   = ! empty( $_GET['embed'] );
	$preview = ! empty( $_GET['preview'] );
	$host    = isset( $_GET['host'] ) ? esc_url_raw( wp_unslash( $_GET['host'] ) ) : '';
	// phpcs:enable

	// Result links point back at the page the quiz is embedded on — but only
	// pages on this site, so the parameter can't be used to brand other URLs.
	$host        = $host ? wp_validate_redirect( $host, '' ) : '';
	$quiz_url    = rspa_apf_quiz_url();
	$result_base = $host ? remove_query_arg( 'r', $host ) : $quiz_url;

	$config = array(
		'shareTarget' => rspa_apf_share_target(),
		'pingUrl'     => $preview ? '' : rspa_apf_setting( 'ping_url' ),
		'resultBase'  => $result_base,
	);

	$html = rspa_apf_widget_html();
	if ( '' === $html ) {
		status_header( 500 );
		echo esc_html__( 'RSPA AI Path Finder: app/path-finder.html is missing from the plugin folder. Reinstall the plugin.', 'rspa-ai-path-finder' );
		exit;
	}

	$head  = '<script>window.RSPA_PATHFINDER_CONFIG = ' . wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES ) . ';</script>' . "\n";
	$head .= '<link rel="canonical" href="' . esc_url( $quiz_url ) . '">' . "\n";
	if ( $embed || $preview ) {
		$head .= '<meta name="robots" content="noindex">' . "\n";
	}
	$html = preg_replace( '/<meta property="og:url" content="[^"]*">/', '<meta property="og:url" content="' . esc_url( $quiz_url ) . '">', $html, 1 );
	$og_image = rspa_apf_setting( 'og_image' );
	if ( $og_image ) {
		$html = preg_replace( '/<meta property="og:image" content="[^"]*">/', '<meta property="og:image" content="' . esc_url( $og_image ) . '">', $html, 1 );
		$html = str_replace( '<meta name="twitter:card" content="summary">', '<meta name="twitter:card" content="summary_large_image">', $html );
	}
	$html = str_replace( '</head>', $head . '</head>', $html );

	status_header( 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Content-Type-Options: nosniff' );
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled, trusted plugin file; injected values escaped above.
	exit;
}
