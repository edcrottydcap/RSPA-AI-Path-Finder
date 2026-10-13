<?php
/**
 * Every way to put the quiz on a page: block, shortcodes, template tag,
 * floating launcher, and the shared renderer the widget also uses.
 *
 * @package RSPA_AI_Path_Finder
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'rspa_apf_register_assets', 5 );
add_action( 'init', 'rspa_apf_register_block' );
add_action( 'init', 'rspa_apf_register_shortcodes' );
add_action( 'wp_footer', 'rspa_apf_launcher' );

/**
 * Script and style handles referenced by block.json and the shortcodes.
 */
function rspa_apf_register_assets() {
	wp_register_script( 'rspa-ai-path-finder-embed', RSPA_APF_URL . 'assets/embed.js', array(), RSPA_APF_VERSION, true );
	wp_register_style( 'rspa-ai-path-finder', RSPA_APF_URL . 'assets/embed.css', array(), RSPA_APF_VERSION );
	wp_register_script(
		'rspa-ai-path-finder-editor',
		RSPA_APF_URL . 'blocks/path-finder/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		RSPA_APF_VERSION,
		true
	);
	wp_add_inline_script(
		'rspa-ai-path-finder-editor',
		'window.rspaApfEditor = ' . wp_json_encode(
			array(
				'previewUrl' => rspa_apf_quiz_url(
					array(
						'embed'   => '1',
						'preview' => '1',
					)
				),
				'quizUrl'    => rspa_apf_quiz_url(),
				'defaults'   => rspa_apf_card_defaults(),
			)
		) . ';',
		'before'
	);
}

/**
 * Register the "AI Path Finder" block from block.json.
 */
function rspa_apf_register_block() {
	register_block_type(
		RSPA_APF_DIR . 'blocks/path-finder',
		array( 'render_callback' => 'rspa_apf_render_block' )
	);
}

/**
 * [rspa_ai_path_finder], plus the short forms [pathfinder] / [PATHFINDER].
 */
function rspa_apf_register_shortcodes() {
	foreach ( array( 'rspa_ai_path_finder', 'pathfinder', 'PATHFINDER' ) as $tag ) {
		if ( ! shortcode_exists( $tag ) ) {
			add_shortcode( $tag, 'rspa_apf_shortcode' );
		}
	}
}

/**
 * Default copy for the promo card.
 *
 * @return array{heading:string,blurb:string,text:string}
 */
function rspa_apf_card_defaults() {
	return array(
		'heading' => __( 'Not sure where to start with AI?', 'rspa-ai-path-finder' ),
		'blurb'   => __( 'Six quick questions place you at AI 101–301 and print a personalized reading path from this hub. About 60 seconds, no signup.', 'rspa-ai-path-finder' ),
		'text'    => __( 'Find your AI path →', 'rspa-ai-path-finder' ),
	);
}

/**
 * Block render callback.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function rspa_apf_render_block( $attributes ) {
	$html = rspa_apf_render(
		array(
			'display'    => $attributes['display'] ?? 'embed',
			'open'       => $attributes['open'] ?? 'popup',
			'text'       => $attributes['buttonText'] ?? '',
			'heading'    => $attributes['heading'] ?? '',
			'blurb'      => $attributes['blurb'] ?? '',
			'new_tab'    => ! empty( $attributes['newTab'] ),
			'min_height' => $attributes['initialHeight'] ?? 640,
		)
	);
	return '<div ' . get_block_wrapper_attributes() . '>' . $html . '</div>';
}

/**
 * Shortcode handler.
 *
 *   [pathfinder]                                   the quiz, inline
 *   [pathfinder display="card"]                    promo card; button opens the quiz in a popup
 *   [pathfinder display="button" text="Start →"]   just the button
 *   open="popup|page"  new_tab="yes"  height="640"  heading="…"  blurb="…"  class="…"
 *
 * @param array|string $atts Shortcode attributes.
 * @param string|null  $content Unused.
 * @param string       $tag Shortcode tag used.
 * @return string
 */
function rspa_apf_shortcode( $atts, $content = null, $tag = 'rspa_ai_path_finder' ) {
	$atts = shortcode_atts(
		array(
			'display' => 'embed',
			'open'    => 'popup',
			'text'    => '',
			'heading' => '',
			'blurb'   => '',
			'new_tab' => 'no',
			'height'  => 640,
			'class'   => '',
		),
		$atts,
		'rspa_ai_path_finder'
	);
	return rspa_apf_render(
		array(
			'display'    => $atts['display'],
			'open'       => $atts['open'],
			'text'       => $atts['text'],
			'heading'    => $atts['heading'],
			'blurb'      => $atts['blurb'],
			'new_tab'    => in_array( strtolower( (string) $atts['new_tab'] ), array( 'yes', 'true', '1' ), true ),
			'min_height' => $atts['height'],
			'class'      => $atts['class'],
		)
	);
}

/**
 * Template tag for theme developers:
 *   <?php if ( function_exists( 'rspa_ai_path_finder' ) ) { rspa_ai_path_finder(); } ?>
 *
 * @param array $args Same keys as rspa_apf_render().
 */
function rspa_ai_path_finder( $args = array() ) {
	echo rspa_apf_render( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in renderer.
}

/**
 * URL of the page currently being viewed — where result links should return.
 *
 * @return string
 */
function rspa_apf_current_url() {
	if ( is_singular() ) {
		return (string) get_permalink();
	}
	global $wp;
	return home_url( user_trailingslashit( $wp->request ?? '' ) );
}

/**
 * Iframe URL for an embed on the current page: result links come back here,
 * and a shared ?r=201i on this page opens that receipt.
 *
 * @param bool $preview Admin/editor preview (never counted, no host).
 * @return string
 */
function rspa_apf_embed_src( $preview = false ) {
	$query = array( 'embed' => '1' );
	if ( $preview ) {
		$query['preview'] = '1';
	} else {
		$query['host'] = rspa_apf_current_url();
		$code          = rspa_apf_result_code( isset( $_GET['r'] ) ? sanitize_text_field( wp_unslash( $_GET['r'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $code ) {
			$query['r'] = $code;
		}
	}
	return rspa_apf_quiz_url( $query );
}

/**
 * Shared renderer.
 *
 * @param array $args {
 *     @type string $display    'embed' (quiz inline, auto-sizing), 'button', or 'card' (promo card with a button).
 *     @type string $open       Button/card: 'popup' (quiz opens over the page) or 'page' (go to the quiz page).
 *     @type string $text       Button label.
 *     @type string $heading    Card heading.
 *     @type string $blurb      Card text.
 *     @type bool   $new_tab    'page' links open in a new tab.
 *     @type int    $min_height Iframe height before the quiz reports its own.
 *     @type bool   $preview    Admin/editor preview — never counted.
 *     @type string $class      Extra CSS classes.
 * }
 * @return string
 */
function rspa_apf_render( $args = array() ) {
	$d    = rspa_apf_card_defaults();
	$args = wp_parse_args(
		$args,
		array(
			'display'    => 'embed',
			'open'       => 'popup',
			'text'       => '',
			'heading'    => '',
			'blurb'      => '',
			'new_tab'    => false,
			'min_height' => 640,
			'preview'    => false,
			'class'      => '',
		)
	);

	wp_enqueue_style( 'rspa-ai-path-finder' );
	wp_enqueue_script( 'rspa-ai-path-finder-embed' );
	$class = trim( 'rspa-apf ' . implode( ' ', array_map( 'sanitize_html_class', preg_split( '/\s+/', (string) $args['class'] ) ) ) );

	if ( 'button' === $args['display'] || 'card' === $args['display'] ) {
		$label  = '' !== trim( (string) $args['text'] ) ? $args['text'] : $d['text'];
		$button = rspa_apf_button( $label, $args['open'], $args['new_tab'] );
		if ( 'button' === $args['display'] ) {
			return '<div class="' . esc_attr( $class ) . ' rspa-apf--button">' . $button . '</div>';
		}
		return sprintf(
			'<div class="%1$s rspa-apf--card"><p class="rspa-apf-card__eyebrow">%2$s</p><div class="rspa-apf-card__body"><div class="rspa-apf-card__text"><p class="rspa-apf-card__heading">%3$s</p><p class="rspa-apf-card__blurb">%4$s</p></div>%5$s</div></div>',
			esc_attr( $class ),
			esc_html__( 'RSPA AI Path Finder', 'rspa-ai-path-finder' ),
			esc_html( '' !== trim( (string) $args['heading'] ) ? $args['heading'] : $d['heading'] ),
			esc_html( '' !== trim( (string) $args['blurb'] ) ? $args['blurb'] : $d['blurb'] ),
			$button
		);
	}

	$height = max( 200, min( 2000, absint( $args['min_height'] ) ) );
	return sprintf(
		'<div class="%1$s rspa-apf--embed"><iframe data-rspa-pathfinder src="%2$s" title="%3$s" style="height:%4$dpx" allow="clipboard-write" loading="lazy"></iframe><noscript><a href="%5$s">%6$s</a></noscript></div>',
		esc_attr( $class ),
		esc_url( rspa_apf_embed_src( (bool) $args['preview'] ) ),
		esc_attr__( 'RSPA AI Path Finder quiz', 'rspa-ai-path-finder' ),
		$height,
		esc_url( rspa_apf_quiz_url() ),
		esc_html__( 'Take the RSPA AI Path Finder quiz', 'rspa-ai-path-finder' )
	);
}

/**
 * The call-to-action link. With open=popup it carries the iframe URL for
 * assets/embed.js; without JavaScript it is still a plain link to the quiz.
 *
 * @param string $label   Button text.
 * @param string $open    'popup' or 'page'.
 * @param bool   $new_tab Open the page in a new tab.
 * @param string $extra   Extra class.
 * @return string
 */
function rspa_apf_button( $label, $open = 'popup', $new_tab = false, $extra = '' ) {
	$attrs = '';
	if ( 'page' === $open ) {
		$attrs = $new_tab ? ' target="_blank" rel="noopener"' : '';
	} else {
		$attrs = ' data-rspa-apf-open="' . esc_url( rspa_apf_embed_src() ) . '" aria-haspopup="dialog"';
	}
	return sprintf(
		'<a class="%1$s" href="%2$s"%3$s>%4$s</a>',
		esc_attr( trim( 'rspa-apf-button ' . $extra ) ),
		esc_url( rspa_apf_quiz_url() ),
		$attrs,
		esc_html( $label )
	);
}

/**
 * Should the floating launcher show on this request?
 *
 * @return bool
 */
function rspa_apf_launcher_here() {
	if ( is_admin() || ! rspa_apf_setting( 'launcher' ) || get_query_var( 'rspa_apf' ) ) {
		return false;
	}
	global $wp;
	$here  = trim( (string) ( $wp->request ?? '' ), '/' );
	$paths = preg_split( '/[\r\n,]+/', rspa_apf_setting( 'launcher_pages' ) );
	foreach ( $paths as $path ) {
		$path = trim( wp_parse_url( trim( $path ), PHP_URL_PATH ) ?? '', '/' );
		if ( '*' === trim( $path ) || ( '' !== $path && $path === $here ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Floating "Find your AI path" button, printed in the footer of chosen pages.
 * Works with any theme or page template — nothing to edit on the page.
 */
function rspa_apf_launcher() {
	if ( ! rspa_apf_launcher_here() ) {
		return;
	}
	wp_enqueue_style( 'rspa-ai-path-finder' );
	wp_enqueue_script( 'rspa-ai-path-finder-embed' );
	$text = rspa_apf_setting( 'launcher_text' );
	$side = 'left' === rspa_apf_setting( 'launcher_position' ) ? 'left' : 'right';
	echo '<div class="rspa-apf rspa-apf-launcher rspa-apf-launcher--' . esc_attr( $side ) . '">' . rspa_apf_button( $text ? $text : rspa_apf_card_defaults()['text'], 'popup' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rspa_apf_button.
}
