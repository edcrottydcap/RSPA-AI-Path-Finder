<?php
/**
 * Classic widget for themes that still use Appearance → Widgets without blocks.
 *
 * @package RSPA_AI_Path_Finder
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'widgets_init',
	function () {
		register_widget( 'RSPA_APF_Widget' );
	}
);

/**
 * "RSPA AI Path Finder" widget.
 */
class RSPA_APF_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'rspa_ai_path_finder',
			__( 'RSPA AI Path Finder', 'rspa-ai-path-finder' ),
			array(
				'description'           => __( 'The AI Path Finder quiz, or a button that opens it.', 'rspa-ai-path-finder' ),
				'show_instance_in_rest' => true,
			)
		);
	}

	/**
	 * Front end.
	 *
	 * @param array $args     Sidebar args.
	 * @param array $instance Saved settings.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] ?? '', $instance, $this->id_base );
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme markup.
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme markup.
		}
		echo rspa_apf_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in renderer.
			array(
				'display' => $instance['display'] ?? 'button',
				'text'    => $instance['text'] ?? '',
			)
		);
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme markup.
	}

	/**
	 * Admin form.
	 *
	 * @param array $instance Saved settings.
	 * @return void
	 */
	public function form( $instance ) {
		$title   = $instance['title'] ?? '';
		$display = $instance['display'] ?? 'button';
		$text    = $instance['text'] ?? '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (optional):', 'rspa-ai-path-finder' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>"><?php esc_html_e( 'Show as:', 'rspa-ai-path-finder' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display' ) ); ?>">
				<option value="button" <?php selected( $display, 'button' ); ?>><?php esc_html_e( 'Button — opens the quiz in a popup (best for sidebars)', 'rspa-ai-path-finder' ); ?></option>
				<option value="card" <?php selected( $display, 'card' ); ?>><?php esc_html_e( 'Promo card with button', 'rspa-ai-path-finder' ); ?></option>
				<option value="embed" <?php selected( $display, 'embed' ); ?>><?php esc_html_e( 'Embedded quiz (needs ~360px or wider)', 'rspa-ai-path-finder' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Button text:', 'rspa-ai-path-finder' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" value="<?php echo esc_attr( $text ); ?>" placeholder="<?php esc_attr_e( 'Find your AI path →', 'rspa-ai-path-finder' ); ?>">
		</p>
		<?php
	}

	/**
	 * Save.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Old settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'   => sanitize_text_field( $new_instance['title'] ?? '' ),
			'display' => in_array( $new_instance['display'] ?? '', array( 'embed', 'card' ), true ) ? $new_instance['display'] : 'button',
			'text'    => sanitize_text_field( $new_instance['text'] ?? '' ),
		);
	}
}
