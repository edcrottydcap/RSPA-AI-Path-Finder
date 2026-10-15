<?php
/**
 * Settings → AI Path Finder: options, embed instructions, and a live preview.
 *
 * @package RSPA_AI_Path_Finder
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'rspa_apf_register_settings' );
add_action( 'admin_menu', 'rspa_apf_admin_menu' );
add_action( 'update_option_' . RSPA_APF_OPTION, 'rspa_apf_settings_changed', 10, 2 );
add_action( 'add_option_' . RSPA_APF_OPTION, 'rspa_apf_settings_added', 10, 2 );
add_action( 'init', 'rspa_apf_maybe_flush', 20 );
add_filter( 'plugin_action_links_' . plugin_basename( RSPA_APF_FILE ), 'rspa_apf_action_links' );

/**
 * Default option values.
 *
 * @return array
 */
function rspa_apf_defaults() {
	return array(
		'slug'         => 'ai-path-finder',
		'share_mode'   => 'quiz',
		'share_custom' => '',
		'ping_url'     => '',
		'og_image'          => '',
		'coupon'            => 'PATHFINDER',
		'launcher'          => '',
		'launcher_text'     => '',
		'launcher_position' => 'bottom-right',
		'launcher_progress' => '1',
		'reward'            => '',
		'reward_code'       => '',
		'reward_headline'   => '',
		'reward_details'    => '',
		'reward_url'        => '',
		'reward_cta'        => '',
		'reward_unlock'     => 'complete',
		'reward_count'      => '3',
		'launcher_pages'    => implode( "\n", rspa_apf_hub_paths() ),
	);
}

/**
 * The RSPA AI Resource Hub pages — the default targets for the floating button.
 *
 * @return string[]
 */
function rspa_apf_hub_paths() {
	return array(
		'/ai-resource-hub/',
		'/ai-terminology/',
		'/ai-tools/',
		'/ai-internal-applications/',
		'/ai-merchant-facing-applications/',
		'/ai-misconceptions-best-practices/',
		'/rspa-ai-education-content/',
	);
}

/**
 * Read one setting.
 *
 * @param string $key Setting key.
 * @return string
 */
function rspa_apf_setting( $key ) {
	$opts = wp_parse_args( (array) get_option( RSPA_APF_OPTION, array() ), rspa_apf_defaults() );
	return isset( $opts[ $key ] ) ? (string) $opts[ $key ] : '';
}

/**
 * Where LinkedIn and email shares send people.
 *
 * @return string
 */
function rspa_apf_share_target() {
	switch ( rspa_apf_setting( 'share_mode' ) ) {
		case 'hub':
			return 'https://www.gorspa.org/ai-resource-hub/';
		case 'custom':
			$custom = rspa_apf_setting( 'share_custom' );
			return $custom ? $custom : rspa_apf_quiz_url();
		default:
			return rspa_apf_quiz_url();
	}
}

/**
 * Register the option and its sanitizer.
 */
function rspa_apf_register_settings() {
	register_setting(
		'rspa_apf',
		RSPA_APF_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'rspa_apf_sanitize',
			'default'           => rspa_apf_defaults(),
		)
	);
}

/**
 * Sanitize submitted settings.
 *
 * @param mixed $in Raw input.
 * @return array
 */
function rspa_apf_sanitize( $in ) {
	$in  = is_array( $in ) ? $in : array();
	$out = rspa_apf_defaults();

	$slug = isset( $in['slug'] ) ? sanitize_title( $in['slug'] ) : '';
	if ( $slug ) {
		$out['slug'] = $slug;
	}
	if ( isset( $in['share_mode'] ) && in_array( $in['share_mode'], array( 'quiz', 'hub', 'custom' ), true ) ) {
		$out['share_mode'] = $in['share_mode'];
	}
	foreach ( array( 'share_custom', 'ping_url', 'og_image' ) as $key ) {
		$out[ $key ] = isset( $in[ $key ] ) ? esc_url_raw( trim( $in[ $key ] ), array( 'https', 'http' ) ) : '';
	}
	$coupon        = isset( $in['coupon'] ) ? strtoupper( preg_replace( '/[^A-Za-z0-9-]/', '', (string) $in['coupon'] ) ) : '';
	$out['coupon'] = '' !== $coupon ? substr( $coupon, 0, 20 ) : 'PATHFINDER';
	$out['launcher']          = empty( $in['launcher'] ) ? '' : '1';
	$out['launcher_text']     = isset( $in['launcher_text'] ) ? sanitize_text_field( $in['launcher_text'] ) : '';
	$out['launcher_position'] = ( isset( $in['launcher_position'] ) && array_key_exists( $in['launcher_position'], rspa_apf_launcher_positions() ) ) ? $in['launcher_position'] : 'bottom-right';
	$out['launcher_progress'] = empty( $in['launcher_progress'] ) ? '' : '1';
	$out['reward']            = empty( $in['reward'] ) ? '' : '1';
	$out['reward_code']       = isset( $in['reward_code'] ) ? substr( strtoupper( preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $in['reward_code'] ) ), 0, 30 ) : '';
	$out['reward_headline']   = isset( $in['reward_headline'] ) ? sanitize_text_field( $in['reward_headline'] ) : '';
	$out['reward_details']    = isset( $in['reward_details'] ) ? sanitize_textarea_field( $in['reward_details'] ) : '';
	$out['reward_url']        = isset( $in['reward_url'] ) ? esc_url_raw( trim( $in['reward_url'] ), array( 'https', 'http' ) ) : '';
	$out['reward_cta']        = isset( $in['reward_cta'] ) ? sanitize_text_field( $in['reward_cta'] ) : '';
	$out['reward_unlock']     = ( isset( $in['reward_unlock'] ) && in_array( $in['reward_unlock'], array( 'receipt', 'count', 'complete' ), true ) ) ? $in['reward_unlock'] : 'complete';
	$out['reward_count']      = (string) max( 1, min( 10, absint( $in['reward_count'] ?? 3 ) ) );
	if ( isset( $in['launcher_pages'] ) ) {
		$lines                 = array_filter( array_map( 'trim', preg_split( '/[\r\n,]+/', (string) $in['launcher_pages'] ) ) );
		$out['launcher_pages'] = implode( "\n", array_map( 'sanitize_text_field', $lines ) );
	}
	return $out;
}

/**
 * The slug may have changed: rebuild rewrite rules on the next request, after
 * init has registered the new rule.
 *
 * @param array $old Old value.
 * @param array $new New value.
 */
function rspa_apf_settings_changed( $old, $new ) {
	if ( ( $old['slug'] ?? '' ) !== ( $new['slug'] ?? '' ) ) {
		update_option( 'rspa_apf_flush', 1 );
	}
}

/**
 * First save of the settings (add_option fires instead of update_option).
 *
 * @param string $option Option name.
 * @param array  $value  New value.
 */
function rspa_apf_settings_added( $option, $value ) {
	rspa_apf_settings_changed( rspa_apf_defaults(), $value );
}

/**
 * Flush rewrite rules once, when flagged.
 */
function rspa_apf_maybe_flush() {
	if ( get_option( 'rspa_apf_flush' ) ) {
		delete_option( 'rspa_apf_flush' );
		flush_rewrite_rules();
	}
}

/**
 * "Settings" link on the Plugins screen.
 *
 * @param string[] $links Action links.
 * @return string[]
 */
function rspa_apf_action_links( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=rspa-ai-path-finder' ) ) . '">' . esc_html__( 'Settings', 'rspa-ai-path-finder' ) . '</a>' );
	return $links;
}

/**
 * Add Settings → AI Path Finder.
 */
function rspa_apf_admin_menu() {
	add_options_page(
		__( 'RSPA AI Path Finder', 'rspa-ai-path-finder' ),
		__( 'AI Path Finder', 'rspa-ai-path-finder' ),
		'manage_options',
		'rspa-ai-path-finder',
		'rspa_apf_settings_page'
	);
}

/**
 * Render the settings page.
 */
function rspa_apf_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	wp_enqueue_script( 'rspa-ai-path-finder-embed' );

	$opts     = wp_parse_args( (array) get_option( RSPA_APF_OPTION, array() ), rspa_apf_defaults() );
	$quiz_url = rspa_apf_quiz_url();
	$info     = rspa_apf_widget_info();
	$clash    = get_page_by_path( $opts['slug'] );
	$name     = RSPA_APF_OPTION;
	$docs     = 'https://github.com/edcrottydcap/RSPA-AI-Path-Finder/blob/main/docs/wordpress-plugin.md';
	?>
	<div class="wrap rspa-apf-admin">
		<h1><?php esc_html_e( 'RSPA AI Path Finder', 'rspa-ai-path-finder' ); ?></h1>
		<style>
			.rspa-apf-admin .card{max-width:860px}
			.rspa-apf-admin code.copy{cursor:pointer;user-select:all;display:inline-block;padding:4px 8px}
			.rspa-apf-admin .rspa-apf-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-top:8px}
			.rspa-apf-admin .rspa-apf-grid h3{margin:0 0 6px}
			.rspa-apf-admin .rspa-apf-grid p{margin:4px 0}
			.rspa-apf-admin .preview{max-width:720px;background:#f1f1f1;padding:12px;border:1px solid #dcdcde}
		</style>

		<div class="card">
			<h2><?php esc_html_e( 'Your quiz is live', 'rspa-ai-path-finder' ); ?></h2>
			<p>
				<a href="<?php echo esc_url( $quiz_url ); ?>" target="_blank" rel="noopener"><strong><?php echo esc_html( $quiz_url ); ?></strong></a>
				&nbsp;<a class="button" href="<?php echo esc_url( $quiz_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open the quiz', 'rspa-ai-path-finder' ); ?></a>
			</p>
			<p class="description">
				<?php
				printf(
					/* translators: 1: widget version, 2: curriculum date */
					esc_html__( 'Widget version %1$s · curriculum last updated %2$s', 'rspa-ai-path-finder' ),
					esc_html( $info['version'] ? $info['version'] : '?' ),
					esc_html( $info['content_date'] ? $info['content_date'] : '?' )
				);
				?>
			</p>
			<?php if ( $clash ) : ?>
				<div class="notice notice-warning inline"><p>
					<?php
					printf(
						/* translators: %s: page slug */
						esc_html__( 'A page already uses the address "/%s/". The quiz takes priority at that address — pick a different quiz address below if you want that page back.', 'rspa-ai-path-finder' ),
						esc_html( $opts['slug'] )
					);
					?>
				</p></div>
			<?php endif; ?>
		</div>

		<div class="card">
			<h2><?php esc_html_e( 'Put it on your site — pick any one', 'rspa-ai-path-finder' ); ?></h2>
			<p class="description" style="max-width:760px"><?php esc_html_e( 'The quiz opens in a popup over the page by default, so it never has to fit into your layout. Visitors finish the quiz and close it, right where they were.', 'rspa-ai-path-finder' ); ?></p>
			<div class="rspa-apf-grid">
				<div>
					<h3><?php esc_html_e( '1. Floating button — easiest', 'rspa-ai-path-finder' ); ?></h3>
					<p><?php esc_html_e( 'No page editing at all. Tick "Show the floating button" below, list the pages, and save. A "Find your AI path" button appears in the corner of those pages.', 'rspa-ai-path-finder' ); ?></p>
					<p><a href="#launcher"><?php esc_html_e( 'Jump to the setting ↓', 'rspa-ai-path-finder' ); ?></a></p>
				</div>
				<div>
					<h3><?php esc_html_e( '2. Shortcode', 'rspa-ai-path-finder' ); ?></h3>
					<p><?php esc_html_e( 'Paste into any page, post, page builder, or text widget. Click a code to copy it.', 'rspa-ai-path-finder' ); ?></p>
					<p><?php esc_html_e( 'Promo card (opens the popup):', 'rspa-ai-path-finder' ); ?><br><code class="copy">[pathfinder display="card"]</code></p>
					<p><?php esc_html_e( 'Just a button:', 'rspa-ai-path-finder' ); ?><br><code class="copy">[pathfinder display="button"]</code></p>
					<p><?php esc_html_e( 'The whole quiz, inline on the page:', 'rspa-ai-path-finder' ); ?><br><code class="copy">[pathfinder]</code></p>
				</div>
				<div>
					<h3><?php esc_html_e( '3. Block editor', 'rspa-ai-path-finder' ); ?></h3>
					<p><?php esc_html_e( 'Edit a page, click +, search "AI Path Finder". In the right sidebar choose Promo card, Just a button, or Embedded quiz.', 'rspa-ai-path-finder' ); ?></p>
				</div>
				<div>
					<h3><?php esc_html_e( '4. Link to its own page', 'rspa-ai-path-finder' ); ?></h3>
					<p><?php esc_html_e( 'Link any menu item, email, or QR code to the address above. Result links work too, e.g.:', 'rspa-ai-path-finder' ); ?></p>
					<p><code class="copy"><?php echo esc_html( add_query_arg( 'r', '201i', $quiz_url ) ); ?></code></p>
				</div>
			</div>
			<details style="margin-top:12px">
				<summary><?php esc_html_e( 'For your web developer: custom page templates', 'rspa-ai-path-finder' ); ?></summary>
				<p><?php esc_html_e( 'If a page template doesn\'t display the editor content, place the quiz from the theme instead:', 'rspa-ai-path-finder' ); ?></p>
				<p><code class="copy">&lt;?php if ( function_exists( 'rspa_ai_path_finder' ) ) { rspa_ai_path_finder( array( 'display' =&gt; 'card' ) ); } ?&gt;</code></p>
			</details>
			<p><a href="<?php echo esc_url( $docs ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Full step-by-step guide with screenshots →', 'rspa-ai-path-finder' ); ?></a></p>
		</div>

		<form method="post" action="options.php" class="card">
			<h2><?php esc_html_e( 'Settings', 'rspa-ai-path-finder' ); ?></h2>
			<?php settings_fields( 'rspa_apf' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="rspa-apf-slug"><?php esc_html_e( 'Quiz address', 'rspa-ai-path-finder' ); ?></label></th>
					<td>
						<code><?php echo esc_html( trailingslashit( home_url() ) ); ?></code><input id="rspa-apf-slug" name="<?php echo esc_attr( $name ); ?>[slug]" type="text" class="regular-text" style="width:14em" value="<?php echo esc_attr( $opts['slug'] ); ?>"><code>/</code>
						<p class="description"><?php esc_html_e( 'The quiz\'s own page. Shared result links and social cards point here.', 'rspa-ai-path-finder' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'LinkedIn & email shares send people to', 'rspa-ai-path-finder' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="<?php echo esc_attr( $name ); ?>[share_mode]" value="quiz" <?php checked( $opts['share_mode'], 'quiz' ); ?>> <?php esc_html_e( 'The quiz page (recommended — readers take it themselves)', 'rspa-ai-path-finder' ); ?></label><br>
							<label><input type="radio" name="<?php echo esc_attr( $name ); ?>[share_mode]" value="hub" <?php checked( $opts['share_mode'], 'hub' ); ?>> <?php esc_html_e( 'The AI Resource Hub (gorspa.org/ai-resource-hub/)', 'rspa-ai-path-finder' ); ?></label><br>
							<label><input type="radio" name="<?php echo esc_attr( $name ); ?>[share_mode]" value="custom" <?php checked( $opts['share_mode'], 'custom' ); ?>> <?php esc_html_e( 'Another URL:', 'rspa-ai-path-finder' ); ?></label>
							<input type="url" class="regular-text" name="<?php echo esc_attr( $name ); ?>[share_custom]" value="<?php echo esc_attr( $opts['share_custom'] ); ?>" placeholder="https://www.gorspa.org/ai-resource-hub/">
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rspa-apf-ping"><?php esc_html_e( 'Response counter (optional)', 'rspa-ai-path-finder' ); ?></label></th>
					<td>
						<input id="rspa-apf-ping" type="url" class="regular-text" name="<?php echo esc_attr( $name ); ?>[ping_url]" value="<?php echo esc_attr( $opts['ping_url'] ); ?>" placeholder="https://formspree.io/f/…">
						<p class="description"><?php esc_html_e( 'Leave blank to collect nothing. If set, each completed quiz sends one anonymous POST: {level, track, role, ts} — no names, emails, or IPs are added by the quiz.', 'rspa-ai-path-finder' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rspa-apf-coupon"><?php esc_html_e( 'Coupon code on the receipt', 'rspa-ai-path-finder' ); ?></label></th>
					<td>
						<input id="rspa-apf-coupon" type="text" class="regular-text" style="width:14em;text-transform:uppercase" maxlength="20" name="<?php echo esc_attr( $name ); ?>[coupon]" value="<?php echo esc_attr( $opts['coupon'] ); ?>">
						<p class="description"><?php esc_html_e( 'Every receipt lists the value of its resources, then this code takes 100% off. Letters, numbers, and dashes.', 'rspa-ai-path-finder' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rspa-apf-og"><?php esc_html_e( 'Social card image (optional)', 'rspa-ai-path-finder' ); ?></label></th>
					<td>
						<input id="rspa-apf-og" type="url" class="regular-text" name="<?php echo esc_attr( $name ); ?>[og_image]" value="<?php echo esc_attr( $opts['og_image'] ); ?>" placeholder="https://www.gorspa.org/wp-content/uploads/…png">
						<p class="description"><?php esc_html_e( 'A 1200×630 PNG or JPG from the Media Library, shown when the quiz link is posted on LinkedIn, Facebook, or Teams. Blank uses the RSPA logo.', 'rspa-ai-path-finder' ); ?></p>
					</td>
				</tr>
			</table>
			<h2 id="launcher"><?php esc_html_e( 'Floating button (no page editing needed)', 'rspa-ai-path-finder' ); ?></h2>
			<p class="description" style="max-width:720px"><?php esc_html_e( 'Adds a "Find your AI path" button to the corner of the pages listed below; it opens the quiz in a popup. Works with any theme or custom page template, so it\'s the quickest way onto the AI Resource Hub pages.', 'rspa-ai-path-finder' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Show the floating button', 'rspa-ai-path-finder' ); ?></th>
					<td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[launcher]" value="1" <?php checked( $opts['launcher'], '1' ); ?>> <?php esc_html_e( 'On the pages below', 'rspa-ai-path-finder' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><label for="rspa-apf-lpages"><?php esc_html_e( 'Pages', 'rspa-ai-path-finder' ); ?></label></th>
					<td>
						<textarea id="rspa-apf-lpages" name="<?php echo esc_attr( $name ); ?>[launcher_pages]" rows="8" class="large-text code" style="max-width:520px"><?php echo esc_textarea( $opts['launcher_pages'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'One page address per line, e.g. /ai-tools/ (full URLs work too). Use * for every page. Prefilled with the seven AI Resource Hub pages.', 'rspa-ai-path-finder' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rspa-apf-ltext"><?php esc_html_e( 'Button text', 'rspa-ai-path-finder' ); ?></label></th>
					<td><input id="rspa-apf-ltext" type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[launcher_text]" value="<?php echo esc_attr( $opts['launcher_text'] ); ?>" placeholder="<?php esc_attr_e( 'Find your AI path →', 'rspa-ai-path-finder' ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="rspa-apf-lpos"><?php esc_html_e( 'Position', 'rspa-ai-path-finder' ); ?></label></th>
					<td>
						<select id="rspa-apf-lpos" name="<?php echo esc_attr( $name ); ?>[launcher_position]">
							<?php foreach ( rspa_apf_launcher_positions() as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( rspa_apf_launcher_position(), $value ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Progress tracker', 'rspa-ai-path-finder' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[launcher_progress]" value="1" <?php checked( $opts['launcher_progress'], '1' ); ?>> <?php esc_html_e( 'After the quiz, turn the button into the visitor\'s path', 'rspa-ai-path-finder' ); ?></label>
						<p class="description"><?php esc_html_e( 'Shows "AI 201 path · 2 of 7", a Next-up link, the checklist, and encouragement. Visiting a page on the path ticks it off. Progress is kept in the visitor\'s own browser only.', 'rspa-ai-path-finder' ); ?></p>
					</td>
				</tr>
			</table>
			<h2 id="reward"><?php esc_html_e( 'RSPA reward (discount code)', 'rspa-ai-path-finder' ); ?></h2>
			<p class="description" style="max-width:720px"><?php esc_html_e( 'Reward visitors for working through their path with a real RSPA discount code (membership, an event, a course). It prints as a tear-off coupon on the receipt and appears in the floating button\'s panel once unlocked.', 'rspa-ai-path-finder' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Offer a reward', 'rspa-ai-path-finder' ); ?></th>
					<td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[reward]" value="1" <?php checked( $opts['reward'], '1' ); ?>> <?php esc_html_e( 'Show the reward on receipts', 'rspa-ai-path-finder' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><label for="rspa-apf-rcode"><?php esc_html_e( 'Discount code', 'rspa-ai-path-finder' ); ?></label></th>
					<td><input id="rspa-apf-rcode" type="text" class="regular-text" style="width:16em;text-transform:uppercase" maxlength="30" name="<?php echo esc_attr( $name ); ?>[reward_code]" value="<?php echo esc_attr( $opts['reward_code'] ); ?>" placeholder="AIPATH15"></td>
				</tr>
				<tr>
					<th scope="row"><label for="rspa-apf-rhead"><?php esc_html_e( 'Headline', 'rspa-ai-path-finder' ); ?></label></th>
					<td><input id="rspa-apf-rhead" type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[reward_headline]" value="<?php echo esc_attr( $opts['reward_headline'] ); ?>" placeholder="<?php esc_attr_e( '15% off RetailNOW registration', 'rspa-ai-path-finder' ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="rspa-apf-rdet"><?php esc_html_e( 'Fine print', 'rspa-ai-path-finder' ); ?></label></th>
					<td><textarea id="rspa-apf-rdet" rows="2" class="large-text" style="max-width:520px" name="<?php echo esc_attr( $name ); ?>[reward_details]" placeholder="<?php esc_attr_e( 'New registrations only. Expires December 31.', 'rspa-ai-path-finder' ); ?>"><?php echo esc_textarea( $opts['reward_details'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="rspa-apf-rurl"><?php esc_html_e( 'Redeem link', 'rspa-ai-path-finder' ); ?></label></th>
					<td>
						<input id="rspa-apf-rurl" type="url" class="regular-text" name="<?php echo esc_attr( $name ); ?>[reward_url]" value="<?php echo esc_attr( $opts['reward_url'] ); ?>" placeholder="https://www.gorspa.org/…">
						<input type="text" style="width:12em" name="<?php echo esc_attr( $name ); ?>[reward_cta]" value="<?php echo esc_attr( $opts['reward_cta'] ); ?>" placeholder="<?php esc_attr_e( 'Redeem now', 'rspa-ai-path-finder' ); ?>" aria-label="<?php esc_attr_e( 'Button text', 'rspa-ai-path-finder' ); ?>">
						<p class="description"><?php esc_html_e( 'Optional. Where the code is used, and the button text.', 'rspa-ai-path-finder' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Unlocks', 'rspa-ai-path-finder' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="<?php echo esc_attr( $name ); ?>[reward_unlock]" value="complete" <?php checked( $opts['reward_unlock'], 'complete' ); ?>> <?php esc_html_e( 'When the whole path is picked up (recommended)', 'rspa-ai-path-finder' ); ?></label><br>
							<label><input type="radio" name="<?php echo esc_attr( $name ); ?>[reward_unlock]" value="count" <?php checked( $opts['reward_unlock'], 'count' ); ?>> <?php esc_html_e( 'After picking up', 'rspa-ai-path-finder' ); ?></label>
							<input type="number" min="1" max="10" style="width:4em" name="<?php echo esc_attr( $name ); ?>[reward_count]" value="<?php echo esc_attr( $opts['reward_count'] ); ?>" aria-label="<?php esc_attr_e( 'Number of items', 'rspa-ai-path-finder' ); ?>"> <?php esc_html_e( 'items', 'rspa-ai-path-finder' ); ?><br>
							<label><input type="radio" name="<?php echo esc_attr( $name ); ?>[reward_unlock]" value="receipt" <?php checked( $opts['reward_unlock'], 'receipt' ); ?>> <?php esc_html_e( 'Right away, on every receipt', 'rspa-ai-path-finder' ); ?></label>
						</fieldset>
						<p class="description"><?php esc_html_e( 'Unlocking happens in the visitor\'s browser, with no login, so the code is visible to anyone who looks at the page source. Use a promotional code you\'re happy to have shared, not a single-use secret.', 'rspa-ai-path-finder' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<div class="card" style="max-width:none">
			<h2><?php esc_html_e( 'Live preview', 'rspa-ai-path-finder' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Exactly what visitors see when the quiz is embedded. Preview runs are never counted.', 'rspa-ai-path-finder' ); ?></p>
			<div class="preview"><?php echo rspa_apf_render( array( 'preview' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in renderer. ?></div>
		</div>
		<script>
			document.querySelectorAll('.rspa-apf-admin code.copy').forEach(function (el) {
				el.title = <?php echo wp_json_encode( __( 'Click to copy', 'rspa-ai-path-finder' ) ); ?>;
				el.addEventListener('click', function () {
					if (navigator.clipboard) { navigator.clipboard.writeText(el.textContent); }
					var was = el.style.background; el.style.background = '#d1e7dd';
					setTimeout(function () { el.style.background = was; }, 700);
				});
			});
		</script>
	</div>
	<?php
}
