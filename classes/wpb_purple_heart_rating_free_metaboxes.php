<?php
/**
 * @package    WPBuddy Plugin
 * @subpackage Purple Heart Rating (Free)
 */
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class WPB_Purple_Heart_Rating_Free_Metaboxes {

	/**
	 * @var null|WPB_Purple_Heart_Rating_Free
	 * @since 1.0
	 */
	private $_purple_heart = null;

	/**
	 * @since 1.0
	 *
	 * @param $purple_heart
	 */
	public function __construct( $purple_heart ) {
		$this->_purple_heart = $purple_heart;
	}


	/**
	 * Displays the about metabox
	 * @since 1.0
	 */
	public function about() {
		?>
	<a href="http://wp-buddy.com/" target="_blank" xmlns="http://www.w3.org/1999/html"><img src="https://wpbuddy.libra.uberspace.de/secure/wp-buddy-logo.png" alt="WPBuddy Logo" /></a><?php
	}


	/**
	 * Displays the help links
	 * @since 1.0
	 */
	public function links() {
		?>
	<ul>
		<li><a href="http://wp-buddy.com/documentations/plugins/purple-heart-rating/" target="_blank"><?php echo __( 'Installation manual', $this->_purple_heart->get_textdomain() ); ?></a></li>
		<li><a href="http://wp-buddy.com/documentations/plugins/purple-heart-rating/faq/" target="_blank"><?php echo __( 'Frequently Asked Questions', $this->_purple_heart->get_textdomain() ); ?></a>
		</li>
		<li><a href="http://wp-buddy.com/documentations/plugins/purple-heart-rating/report-a-bug/" target="_blank"><?php echo __( 'Report a bug', $this->_purple_heart->get_textdomain() ); ?></a></li>
		<li>
			<a href="http://wp-buddy.com/documentations/plugins/purple-heart-rating/request-a-function/" target="_blank"><?php echo __( 'Request a function', $this->_purple_heart->get_textdomain() ); ?></a>
		</li>
		<li>
			<a href="http://wp-buddy.com/documentations/plugins/purple-heart-rating/submit-a-translation/" target="_blank"><?php echo __( 'Submit a translation', $this->_purple_heart->get_textdomain() ); ?></a>
		</li>
		<li><a href="http://wp-buddy.com/" target="_blank"><?php echo __( 'More cool stuff by WPBuddy', $this->_purple_heart->get_textdomain() ); ?></a></li>
	</ul>
	<?php
	}

	/**
	 * Display the social icons
	 * @since 1.0
	 */
	public function social() {
		?>
	<div class="wpbph-social">
		<p>

		<div class="g-plusone" data-size="medium" data-href="http://wp-buddy.com/products/plugins/purple-heart-rating-wordpress-plugin/"></div>
		</p>

		<script type="text/javascript">
			(function() {
				var po = document.createElement( 'script' );
				po.type = 'text/javascript';
				po.async = true;
				po.src = 'https://apis.google.com/js/plusone.js';
				var s = document.getElementsByTagName( 'script' )[0];
				s.parentNode.insertBefore( po, s );
			})();
		</script>

		<p>
		<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwp-buddy.com%2Fproducts%2Fplugins%2Fpurple-heart-rating-wordpress-plugin%2F&amp;send=false&amp;layout=button_count&amp;width=150&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:150px; height:21px;" allowTransparency="true"></iframe>
		</p>

		<p>
			<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://wp-buddy.com/products/plugins/purple-heart-rating-wordpress-plugin/" data-text="Check out the Purple Heart Rating WordPress Plugin" data-related="wp_buddy">Tweet</a>
		</p>
		<script>!function( d, s, id ) {
			var js, fjs = d.getElementsByTagName( s )[0];
			if( !d.getElementById( id ) ) {
				js = d.createElement( s );
				js.id = id;
				js.src = "//platform.twitter.com/widgets.js";
				fjs.parentNode.insertBefore( js, fjs );
			}
		}( document, "script", "twitter-wjs" );</script>
	</div>
	<?php
	}

	/**
	 * Displays the subscribe metabox
	 * @since 1.0
	 */
	public function subscribe() {
		global $current_user;
		get_currentuserinfo();
		$name = $current_user->user_firstname;
		if( empty( $name ) ) $name = $current_user->display_name;
		?>
	<div class="wpbuddy-cr-form">
		<label for="text1210658"><?php echo __( 'Your first name', $this->_purple_heart->get_textdomain() ); ?></label> <input id="text1210658" name="209681" type="text" value="<?php echo $name; ?>" />
		<label for="text1210692"><?php echo __( 'Your E-Mail address', $this->_purple_heart->get_textdomain() ); ?></label>
		<input id="text1210692" name="email" value="<?php echo $current_user->user_email; ?>" type="text" />
		<a href="https://10955.cleverreach.com/f/54067/wcs/" target="_blank" class="button button-primary"><?php echo __( 'Subscribe for free', $this->_purple_heart->get_textdomain() ); ?></a>
	</div>
	<?php
	}


	/**
	 * Displays the ads-metabox
	 * @since 1.0
	 */
	public function ads() {
		$ads     = $this->get_ads();
		$ads_max = count( $ads ) - 1;
		$a_id    = mt_rand( 0, $ads_max );
		echo '<a href="' . $ads[$a_id]['link'] . '" target="_blank"><img src="' . $ads[$a_id]['image'] . '" alt="Ad" /></a>';
	}

	/**
	 * Displays the general metabox
	 * @since 1.0
	 */
	public function general() {
		?>

	<div id="wpbph-preview">
		<?php echo $this->_purple_heart->rating_frontend( null, 'example' ); ?>
	</div>

	<?php settings_fields( 'wpbph_settings_group' ); ?>
	<h4><?php echo __( 'Customize', $this->_purple_heart->get_textdomain() ); ?></h4>

	<div class="wpbph-backend-input wpbph-editable">
		<label for="wpbph_headline"><?php echo __( 'Headline', $this->_purple_heart->get_textdomain() ); ?></label>
		<input class="regular-text" data-standard-value="<?php echo $this->_purple_heart->options_standards( 'headline' ); ?>" id="wpbph_headline" type="text" value="<?php echo $this->_purple_heart->get_option( 'headline' ); ?>" name="wpbph[headline]" data-editclass="wpbph-headline" />
	</div>

	<div class="wpbph-backend-input wpbph-editable">
		<label for="wpbph_description"><?php echo __( 'Description', $this->_purple_heart->get_textdomain() ); ?></label>
		<textarea cols="30" rows="5" data-standard-value="<?php echo $this->_purple_heart->options_standards( 'description' ); ?>" name="wpbph[description]" data-editclass="wpbph-description" id="wpbph_description"><?php echo esc_textarea( $this->_purple_heart->get_option( 'description' ) ); ?></textarea>
	</div>

	<div class="wpbph-backend-input wpbph-editable">
		<label for="wpbph_button_more"><?php echo __( '"More Button" label', $this->_purple_heart->get_textdomain() ); ?></label>
		<input class="regular-text" data-standard-value="<?php echo $this->_purple_heart->options_standards( 'more_button_label' ); ?>" id="wpbph_button_more" type="text" value="<?php echo $this->_purple_heart->get_option( 'more_button_label' ); ?>" name="wpbph[more_button_label]" data-editclass="wpbph-button-more" />
	</div>

	<div class="wpbph-backend-input wpbph-editable">
		<label for="wpbph_more_button_headline"><?php echo __( '"More Button" headline', $this->_purple_heart->get_textdomain() ); ?></label>
		<input class="regular-text" data-standard-value="<?php echo $this->_purple_heart->options_standards( 'more_button_headline' ); ?>" id="wpbph_more_button_headline" type="text" value="<?php echo $this->_purple_heart->get_option( 'more_button_headline' ); ?>" name="wpbph[more_button_headline]" data-editclass="popover-title" />
	</div>

	<div class="wpbph-backend-input wpbph-editable">
		<label for="wpbph_more_button_description"><?php echo __( '"More Button" description', $this->_purple_heart->get_textdomain() ); ?></label>
		<textarea cols="30" rows="5" data-standard-value="<?php echo $this->_purple_heart->options_standards( 'more_button_description' ); ?>" id="wpbph_more_button_description" name="wpbph[more_button_description]" data-editclass="popover-content"><?php echo esc_textarea( $this->_purple_heart->get_option( 'more_button_description' ) ); ?></textarea>
	</div>

	<a href="#" class="button wpbph-reset"><i class="icon icon-exchange"></i> <?php echo __( 'Reset to defaults', $this->_purple_heart->get_textdomain() ); ?></a>

	<h4><?php echo __( 'Options', $this->_purple_heart->get_textdomain() ); ?></h4>

	<?php
		$display = $this->_purple_heart->get_option( 'display', array() );
		if( ! is_array( $display ) ) $display = array();
		?>

	<?php
		$post_types = $this->_purple_heart->get_option( 'post_types', array() );
		if( ! is_array( $post_types ) ) $post_types = array();
		?>

	<div class="wpbph-backend-input wpbph-option">
		<label for="wpbph_option_post_types"><?php echo __( 'On which post types?', $this->_purple_heart->get_textdomain() ); ?></label>
		<select multiple="multiple" name="wpbph[post_types][]" id="wpbph_option_post_types">

			<?php
			foreach( get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' ) as $post_type ) {
				echo '<option ' . ( ( in_array( $post_type->name, $post_types ) ) ? 'selected="selected"' : '' ) . ' value="' . $post_type->name . '">' . $post_type->labels->name . '</option>';
			}
			?>

		</select>
	</div>

	<div class="wpbph-backend-input wpbph-option">
		<label for="wpbph_option_ip_save_time"><?php echo __( 'IP Save Time', $this->_purple_heart->get_textdomain() ); ?></label>
		<input id="wpbph_option_ip_save_time" class="small-text" data-standard-value="<?php echo $this->_purple_heart->options_standards( 'ip_save_time' ); ?>" type="text" name="wpbph[ip_save_time]" value="<?php echo $this->_purple_heart->get_option( 'ip_save_time' ); ?>" />
		<span class="description"><?php echo __( 'hours (Keep empty to save for the maxium duration = 365 days)', $this->_purple_heart->get_textdomain() ); ?></span>
	</div>

	<div class="wpbph-backend-input wpbph-option">
		<label for="wpbph_option_tracking"><?php echo __( 'Allow tracking?', $this->_purple_heart->get_textdomain() ); ?></label>
		<select name="wpbph[tracking]" id="wpbph_option_tracking">
			<option <?php if( (bool) $this->_purple_heart->get_option( 'tracking' ) ) echo 'selected="selected"'; ?> value="1"><?php echo __( 'Yes', $this->_purple_heart->get_textdomain() ); ?></option>
			<option <?php if( ! (bool) $this->_purple_heart->get_option( 'tracking' ) ) echo 'selected="selected"'; ?> value="0"><?php echo __( 'No', $this->_purple_heart->get_textdomain() ); ?></option>
		</select>
		<input type="hidden" name="wpbph[show_tracking_popup]" value="<?php echo $this->_purple_heart->get_option( 'show_tracking_popup' ); ?>" />

		<p class="description"><?php echo __( 'This helps us to improve the plugin. Thank you!', $this->_purple_heart->get_textdomain() ) . ' <a href="http://wp-buddy.com/wiki/allow-tracking/" target="_blank">' . __( 'More info about what we track.', $this->_purple_heart->get_textdomain() ) . '</a>'; ?></p>
	</div>

	<?php if( defined( 'STARRATING_WPLOAD' ) ): ?>

		<h4><?php echo __( 'Other', $this->_purple_heart->get_textdomain() ); ?></h4>

		<div class="wpbph-backend-input wpbph-option">
			<label for="wpbph_gd_star_import"><?php echo __( 'GD Star Rating detected', $this->_purple_heart->get_textdomain() ); ?></label>
			<a id="wpbph_gd_star_import" href="<?php echo admin_url( 'admin.php?page=wpbph-settings&action=import_gd' ); ?>" class="btn button"><i class="icon icon-download-alt"></i> <?php echo __( 'Import data from the GD Star Rating Plugin', $this->_purple_heart->get_textdomain() ); ?>
			</a>

			<p class="description"><?php echo __( 'By clicking this button all ratings generated by the GD Star Rating plugin will be imported to the Purple Heart Rating Plugin. After that the GD Star Rating Plugin will be deactivated. Important: this will overwrite your existing Purple Heart Ratings.', $this->_purple_heart->get_textdomain() ); ?></p>
		</div>

		<?php endif; ?>

	<p><?php submit_button( '', 'primary', false ); ?></p><p>&nbsp;</p><?php
	}

	/**
	 * Returns the adds
	 * @since 1.0
	 * @access private
	 * @return array
	 */
	private function get_ads() {
		// if the ads are already set, return it!
		$ads = get_site_transient( 'wpbph_ads' );
		if( $ads ) return $ads;

		// what wp-version do we have here?
		global $wp_version;

		// prepare the elements for the POST-call
		$post_elements = array(
			'action'     => 'get_ads',
			'wp_version' => $wp_version,
		);

		// some more options for the POST-call
		$options = array(
			'timeout'    => 30,
			'body'       => $post_elements,
			'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url()
		);

		$data = wp_remote_post( 'http://wp-buddy.com/plugins/ads/', $options );

		if( ! is_wp_error( $data ) && 200 == $data['response']['code'] ) {
			if( $body = json_decode( $data['body'], true ) ) {
				if( is_array( $body ) && isset( $body['ads'] ) ) {
					$ads = $body['ads'];
					set_site_transient( 'wpbph_ads', $ads, 604800 );
					return $ads;
				}
			}
		}

		return array();
	}
}