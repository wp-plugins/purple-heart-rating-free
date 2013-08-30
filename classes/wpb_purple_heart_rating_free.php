<?php

/**
 * @package    WPBuddy Plugin
 * @subpackage Purple Heart Rating (Free)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * @version 1.2
 */
class WPB_Purple_Heart_Rating_Free extends WPB_Plugin {

	/**
	 * _plugin_textdomain
	 *
	 * (default value: wpbsg )
	 *
	 * @var string
	 * @access protected
	 * @since  1.0
	 */
	protected $_plugin_textdomain = 'purple-heart-rating-free';


	/**
	 * _plugin_name
	 *
	 * (default value: wpbuddy_rating )
	 *
	 * @var string
	 * @access protected
	 * @since  1.0
	 */
	protected $_plugin_name = 'purple-heart-rating-free';


	/**
	 * _plugin_version
	 * The plugin version
	 *
	 * (default value: '1.0')
	 *
	 * @var string
	 * @access private
	 * @since  1.0
	 */
	public $_plugin_version = '1.2';


	/**
	 * Just do the normal startup stuff (adding actions and so on ...)
	 *
	 * @param null   $file
	 * @param null   $plugin_url
	 * @param string $inclusion
	 * @param bool   $auto_update
	 *
	 * @since  1.0
	 *
	 * @access public
	 * @return \WPB_Purple_Heart_Rating_Free
	 */
	function __construct( $file = null, $plugin_url = null, $inclusion = 'plugin', $auto_update = false ) {

		// call the parent constructor first
		parent::__construct( $file, $plugin_url, $inclusion, $auto_update );

		// do the admin stuff
		$this->do_admin();

		// do the non-admin stuff
		$this->do_non_admin();

		return $this;
	}


	/**
	 * Doing the admin stuff
	 * @since 1.0
	 */
	private function do_admin() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'wp_loaded', array( &$this, 'check_for_pro_version' ) );

		// creates a new menu for the settings sections
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

		// Enqueue Javascript and CSS
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts_backend' ), 100 );

		// register settings
		add_action( 'admin_init', array( &$this, 'settings' ) );

		// add two function for the ajax post action: one for the logged-in users and one for the not-logged-in-users
		add_action( 'wp_ajax_wpbph_ajax_rate', array( &$this, 'ajax_rate' ) );
		add_action( 'wp_ajax_nopriv_wpbph_ajax_rate', array( &$this, 'ajax_rate' ) );

		// add the pointer to the menu (for asking to allow tracking)
		add_action( 'admin_footer', array( &$this, 'wp_pointer_message' ) );

		// add action to save the allow/disallow tracking
		add_action( 'wp_ajax_wpbph_ajax_tracking', array( &$this, 'ajax_tracking' ) );

		// add settings links to the plugins admin page
		if ( 'plugin' == $this->_inclusion ) {
			add_filter( 'plugin_action_links_' . plugin_basename( $this->_plugin_file ), array( &$this, 'plugin_action_links' ) );
		}

		add_action( 'wp_ajax_wpbph_ajax_refresh_post_ratings', array( &$this, 'ajax_refresh_post_ratings' ) );
		add_action( 'wp_ajax_nopriv_wpbph_ajax_refresh_post_ratings', array( &$this, 'ajax_refresh_post_ratings' ) );

		add_action( 'admin_head', array( &$this, 'upgrade_notices' ) );

	}

	public function check_for_pro_version() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			$is_other_page = true;
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		// check if the pro-version is installed, if so, deactivate this plugin
		if ( is_plugin_active( 'purple-heart-rating/purple-heart-rating.php' ) ) {
			deactivate_plugins( 'purple-heart-rating-free/purple-heart-rating-free.php' );
			if ( isset( $is_other_page ) ) {
				wp_die(
					__( 'The Purple Heart Plugin (Free Version) has been deactivated because the PRO-Version is installed.', $this->_plugin_textdomain )
					. '<br /><br />'
					. '<a href="' . admin_url( 'plugins.php?deactivate=true' ) . '">' . __( 'Go back to the plugins section', $this->_plugin_textdomain ) . '</a>'
				);
			}
			wp_die(
				__( 'The Purple Heart Plugin (Free Version) cannot be activated because the PRO-Version is installed already.', $this->_plugin_textdomain )
				. '<br /><br />'
				. '<a href="' . admin_url( 'plugins.php?deactivate=true' ) . '">' . __( 'Go back to the plugins section', $this->_plugin_textdomain ) . '</a>'
			);
		}
	}

	/**
	 * Doing the non-admin stuff
	 * @since 1.0
	 */
	private function do_non_admin() {
		if ( is_admin() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts_frontend' ), 100 );
		add_filter( 'the_content', array( &$this, 'add_rating_content' ), 60, 1 );

		add_action( 'wp_head', array( &$this, 'wp_head' ) );
	}


	/**
	 * Adds the rating HTML code to the post, page or custom post type
	 *
	 * @param string   $content
	 *
	 * @global WP_Post $post
	 *
	 * @return string the original content with the HTML code of the rating added
	 * @since 1.0
	 *
	 */
	public function add_rating_content( $content ) {

		// only show the rating on single pages
		if ( ! is_singular() ) {
			return $content;
		}

		global $post;

		// stop if this is not a post
		if ( ! $post instanceof WP_Post ) {
			return $content;
		}

		// stop if $post ist not an object
		if ( ! is_object( $post ) ) {
			return $content;
		}

		// get the selected post types out of the options
		$post_types_selected = $this->get_option( 'post_types' );

		// if this is the first call, the post types option in empty. so we have to create an array out of it
		if ( ! is_array( $post_types_selected ) ) {
			$post_types_selected = array();
		}

		// check whether to display the rating on this post type
		if ( ! in_array( $post->post_type, $post_types_selected ) ) {
			return $content;
		}

		$content = $content . $this->rating_frontend( $post );

		return $content;
	}


	/**
	 * Creates the admin menu
	 * @since 1.0
	 */
	public function admin_menu() {

		$this->_settings_menu_slug = add_menu_page(
			__( 'Purple Heart Rating', $this->_plugin_textdomain ),
			__( 'Purple Heart Rating', $this->_plugin_textdomain ),
			'manage_options',
			'wpbph-settings',
			array( &$this, 'settings_page' ),
			$this->plugins_url( 'assets/img/purple-heart-icon.png' )
		);
	}


	/**
	 * Registers the rating settings option
	 * @since 1.0
	 */
	public function settings() {
		register_setting( 'wpbph_settings_group', 'wpbph' );
	}


	/**
	 * Returns the standard rating option values
	 *
	 * @param $option
	 *
	 * @since 1.0
	 * @return string
	 */
	public function options_standards( $option ) {
		$standards = array(
			'headline'                   => __( 'Rate us with a heart, or not!', $this->_plugin_textdomain ),
			'description'                => __( 'So that we are getting better and you have more benefits of our posts.', $this->_plugin_textdomain ),
			'more_button_label'          => __( 'What?', $this->_plugin_textdomain ),
			'more_button_headline'       => __( 'What?', $this->_plugin_textdomain ),
			'more_button_description'    => __( 'We want to know what you think about this site. Share your feedback with the author - and help us to improve this page.', $this->_plugin_textdomain ),
			'tracking'                   => 0,
			'show_tracking_popup'        => 1,
			'ip_save_time'               => '',
			'free_version_upgraded_time' => current_time( 'timestamp' ),
		);

		if ( isset( $standards[$option] ) ) {
			return $standards[$option];
		}

		return '';
	}


	/**
	 * Returns the content of a rating option
	 *
	 * @param      $option
	 *
	 * @since 1.0
	 * @since 1.1 no param $is_static any longer
	 * @return string
	 */
	public function get_option( $option ) {
		$options = get_option( 'wpbph' );
		if ( isset( $options[$option] ) ) {
			return $options[$option];
		}

		return $this->options_standards( $option );
	}


	/**
	 * The same as get_option but can be accessed statically
	 *
	 * @param $option
	 *
	 * @uses  self::get_option
	 *
	 * @return string
	 * @since 1.0
	 */
	public static function get_option_static( $option ) {
		$options = get_option( 'wpbph' );
		if ( isset( $options[$option] ) ) {
			return $options[$option];
		}
	}


	/**
	 * settings_page function.
	 * This displays the settings page for the the plugin
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function settings_page() {

		$this->import_gd();

		if ( isset( $_GET['settings-updated'] ) && 'true' == $_GET['settings-updated'] ) {
			echo '<div class="error updated"><p><strong>' . __( 'Settings updated.', $this->_plugin_textdomain ) . '</strong></p></div>';
			$this->track( array( 'settings_updated' => date( 'r' ) ) );
		}

		// get the columns
		global $screen_layout_columns;

		$metabox_class = new WPB_Purple_Heart_Rating_Free_Metaboxes( $this );

		// General metabox
		add_meta_box( 'wpbph_metabox_general', __( 'General', $this->_plugin_textdomain ), array( $metabox_class, 'general' ), $this->_settings_menu_slug, 'normal', 'core' );

		// Side metaboxes
		add_meta_box( 'wpbph_metabox_about', __( 'About', $this->_plugin_textdomain ), array( $metabox_class, 'about' ), $this->_settings_menu_slug, 'side', 'default' );

		add_meta_box( 'wpbph_metabox_social', __( 'Like this plugin?', $this->_plugin_textdomain ), array( $metabox_class, 'social' ), $this->_settings_menu_slug, 'side', 'default' );

		add_meta_box( 'wpbph_metabox_ads', __( 'Discover', $this->_plugin_textdomain ), array( $metabox_class, 'ads' ), $this->_settings_menu_slug, 'side', 'default' );

		add_meta_box( 'wpbph_metabox_links', __( 'Helpful links', $this->_plugin_textdomain ), array( $metabox_class, 'links' ), $this->_settings_menu_slug, 'side', 'default' );

		add_meta_box( 'wpbph_metabox_subscribe', __( 'Grab our free newsletter!', $this->_plugin_textdomain ), array( $metabox_class, 'subscribe' ), $this->_settings_menu_slug, 'side', 'default' );

		?>

		<div class="wrap" id="wpbph-settings">
			<h2>
				<i class="icon icon-heart wpbph-screen-icon"></i> <?php echo __( 'Purple Heart Rating Settings', $this->_plugin_textdomain ); ?>
			</h2><br />

			<form action="options.php" method="post" class="wpbph-settings-form">

				<?php
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				?>
				<input type="hidden" value="<?php echo wp_create_nonce( 'gpaisrpro-options-ajax-nonce' ); ?>" name="gpaisrpro-options-ajax-nonce" id="gpaisrpro-options-ajax-nonce" />

				<div id="poststuff" class="metabox-holder has-right-sidebar">

					<div id="side-info-column" class="inner-sidebar">
						<?php do_meta_boxes( $this->_settings_menu_slug, 'side', array() ); ?>
					</div>

					<div id="post-body" class="has-sidebar">
						<div id="post-body-content" class="has-sidebar-content">
							<?php do_meta_boxes( $this->_settings_menu_slug, 'normal', array() ); ?>
						</div>
					</div>

					<br class="clear" />

					<script type="text/javascript">
						//<![CDATA[
						jQuery(document).ready(function ($) {

							/* close postboxes that should be closed */
							jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

							/* postboxes setup */
							postboxes.add_postbox_toggles('<?php echo $this->_settings_menu_slug; ?>');

						});
					</script>
				</div>
				<!-- poststuff -->

			</form>

		</div><!-- /wpbph-settings -->

	<?php

	}


	/**
	 * enqueue_scripts_backend function.
	 * Adds CSS and Javascripts to the specified pages
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param mixed $hook_suffix
	 *
	 * @return void
	 */
	public function enqueue_scripts_backend( $hook_suffix ) {

		// always include the wp-pointer
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );

		if ( ! ( 'toplevel_page_wpbph-settings' == $hook_suffix
				XOR 'purple-heart-rating-settings_page_wpbph-settings-comments' == $hook_suffix )
		) {
			return;
		}

		// define the style here because it's needed on edit post pages and the settings pages
		wp_register_style( 'wpbph_backend_style', $this->plugins_url( 'assets/css/rating-backend.css', $this->_plugin_file ), false, null );
		wp_register_style( 'wpbph_frontend_style', $this->plugins_url( 'assets/css/rating-frontend.css', $this->_plugin_file ), false, null );
		wp_register_style( 'wpbph_frontend_fontawesome', '//netdna.bootstrapcdn.com/font-awesome/3.0.2/css/font-awesome.css', false, null );

		// define the Javascripts here because it's needed on the edit post pages and the settings pages
		wp_register_script( 'wpbph_backend_js', $this->plugins_url( 'assets/js/rating-backend.js', $this->_plugin_file ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-slider' ), null );

		wp_enqueue_style( 'wpbph_frontend_style' );
		wp_enqueue_style( 'wpbph_frontend_fontawesome' );
		wp_enqueue_style( 'wpbph_backend_style' );
		wp_enqueue_script( 'wpbph_backend_js' );
		wp_enqueue_script( 'postbox' ); // just for the open-and-close function to the postboxes
		wp_enqueue_script( 'jquery-color' );

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Enqueues the js and css files for the frontend
	 * @since 1.0
	 * @global WP_Post $post
	 */
	public function enqueue_scripts_frontend() {
		global $post;
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		// get the selected post types out of the options
		$post_types_selected = $this->get_option( 'post_types' );

		// if this is the first call, the post types option in empty. so we have to create an array out of it
		if ( ! is_array( $post_types_selected ) ) {
			$post_types_selected = array();
		}

		// check whether to display the rating on this post type
		if ( ! in_array( $post->post_type, $post_types_selected ) ) {
			return;
		}

		wp_register_style( 'wpbph_frontend_style', $this->plugins_url( 'assets/css/rating-frontend.css', $this->_plugin_file ), false, $this->_plugin_version );
		wp_enqueue_style( 'wpbph_frontend_style' );

		wp_register_style( 'wpbph_frontend_fontawesome', '//netdna.bootstrapcdn.com/font-awesome/3.0.2/css/font-awesome.css', false, null );
		wp_enqueue_style( 'wpbph_frontend_fontawesome' );

		wp_register_script( 'wpbph_frontend_js', $this->plugins_url( 'assets/js/rating-frontend.js', $this->_plugin_file ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-slider' ), $this->_plugin_version );
		wp_enqueue_script( 'wpbph_frontend_js' );

		wp_localize_script( 'wpbph_frontend_js', 'WPBAjaxRating', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'current_post_id' => $post->ID ) );
	}


	/**
	 * Creates the HTML code of the rating
	 * Define $usage as 'example' to load the latest post (needed internally for backend purposes)
	 *
	 * @param int|null $post
	 * @param string   $usage Can either be 'example' or 'frontend' (default)
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function rating_frontend( $post = null, $usage = 'frontend' ) {

		if ( 'example' == $usage ) {
			$posts = get_posts( array( 'numberposts' => 1 ) );

			// check if there are any posts. if not: stop here
			if ( ! isset( $posts[0] ) ) {
				return;
			}

			// get the post
			$post = $posts[0];

			// delete the posts array
			unset( $posts );


		}
		elseif ( ! is_null( $post ) ) {

			if ( ! is_object( $post ) ) {
				return '';
			}

		}
		else {

			global $post;
			if ( ! isset( $post ) ) {
				return '';
			}
			if ( ! is_object( $post ) ) {
				return '';
			}
		}

		$ratings            = $this->get_rating_percent( $post->ID );
		$rating_percent_ok  = $ratings['ok'];
		$rating_percent_bad = $ratings['bad'];

		ob_start();

		?>
		<aside itemscope="itemscope" itemtype="http://schema.org/CreativeWork">
			<meta itemprop="url" content="<?php echo get_permalink( $post->ID ); ?>" />
			<div class="wpbph-frontend" itemprop="aggregateRating" itemscope="itemscope" itemtype="http://schema.org/AggregateRating">
				<?php if ( 'example' == $usage ) {
					echo '<i class="icon icon-edit wpbph-icon-edit"></i>';
				} ?>
				<meta itemprop="worstRating" content="1" />
				<meta itemprop="bestRating" content="100" />
				<meta itemprop="ratingCount" content="<?php echo $this->count_ratings( $post->ID ); ?>" />
				<meta itemprop="ratingValue" content="<?php echo $rating_percent_ok; ?>" />
				<div class="wpbph-info">
					<div class="wpbph-info-cell">
						<h1 class="wpbph-headline" data-forid="#wpbph_headline"><?php echo $this->get_option( 'headline' ); ?></h1>

						<p class="wpbph-description" data-forid="#wpbph_description"><?php echo $this->get_option( 'description' ); ?></p>
						<button class="wpbph-button-more" data-forid="#wpbph_button_more" data-title="<?php echo $this->get_option( 'more_button_headline' ); ?>" data-content="<?php echo $this->get_option( 'more_button_description' ); ?>" data-placement="right" data-trigger="<?php echo( ( is_admin() ) ? 'click' : 'hover' ); ?>"><?php echo $this->get_option( 'more_button_label' ); ?></button>
					</div>
				</div>
				<!-- /wpbph-info -->

				<div class="wpbph-rating">
					<img class="wpbph-ajax-loader" style="display:none;" src="<?php echo $this->plugins_url( 'assets/img/ajax-loader.gif' ); ?>" border="0" alt="Ajax Loader" />

					<div class="wpbph-polaroid">
						<div class="wpbph-table">
							<div class="wpbph-table-tr" data-post_id="<?php echo $post->ID; ?>">
								<div class="wpbph-table-td wpbph-table-center wpbph-table-big-heart">
									<i title="<?php echo __( '+1', $this->get_textdomain() ); ?>" class="icon icon-heart wpbph-heart-big" data-current-icon="icon-heart"></i>
								</div>
								<div class="wpbph-table-td wpbph-table-values wpbph-value-right-column">
									<span class="wpbph-value"><span class="wpbph-value-inner"><?php echo $rating_percent_ok; ?></span>%</span>
									<span class="wpbph-bad-value"><i title="<?php echo __( '-1', $this->get_textdomain() ); ?>" class="icon icon-heart wpbph-heart-small"></i> <span class="wpbph-bad-value-inner"><?php echo $rating_percent_bad; ?></span>%</span>
								</div>
							</div>
							<div style="clear:both;"></div>
							<div class="wpbph-table-tr" data-post_id="<?php echo $post->ID; ?>">
								<div class="wpbph-table-td wpbph-table-center">
									<button class="wpbph-button-ok" title="<?php echo __( '+1', $this->get_textdomain() ); ?>">
										<i class="icon icon-chevron-up"></i></button>
								</div>
								<div class="wpbph-table-td wpbph-button-right-column">
									<button class="wpbph-button-bad" title="<?php echo __( '-1', $this->get_textdomain() ); ?>">
										<i class="icon icon-chevron-down"></i></button>
								</div>
							</div>
							<div style="clear:both;"></div>
						</div>
						<!-- /wpbph-table -->
					</div>
					<!-- /wpbph-polaroid -->

					<div class="wpbph-copyright-info">
						<a href="http://wp-buddy.com/products/plugins/purple-heart-rating-wordpress-plugin/" target="_blank"><?php echo __( 'WordPress Rating Plugin by WPBuddy', $this->_plugin_textdomain ); ?></a>
					</div>
				</div>
				<!-- /wpbph-rating -->
			</div>
			<div style="clear:both;"></div>
			<!-- /wpbph-frontend -->
		</aside>

		<?php
		$content = ob_get_clean();
		// remove new lines, etc.
		$content = trim( preg_replace( '/\s\s+/', ' ', $content ) );
		return $content;
	}


	/**
	 * The ajax function which performs the rating
	 * @since 1.0
	 * @global wpdb $wpdb
	 * @uses  WPB_Purple_Heart_Rating_Free_Db::log_rating
	 * @uses  WPB_Purple_Heart_Rating_Free_Db::has_rated
	 *
	 * @return bool|unknown
	 */
	public function ajax_rate() {

		// check if the post_id was set
		if ( ! isset( $_REQUEST['post_id'] ) OR ! isset( $_REQUEST['button_pressed'] ) ) {
			$this->print_json_headers();
			die( json_encode( array( 'error' => 1, 'message' => __( 'Sorry, we cannot proceed your rating.', $this->_plugin_textdomain ) ) ) );
		}

		$post_id = intval( $_REQUEST['post_id'] );

		global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
			return false;
		}

		// check if the post exists
		$post_exists = (bool) $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE ID = '" . $post_id . "' LIMIT 1" );

		if ( ! $post_exists ) {
			$this->print_json_headers();
			die( json_encode( array( 'error' => 1, 'message' => __( 'Sorry, the post you are trying to rate does not exist.', $this->_plugin_textdomain ) ) ) );
		}

		// check if the users IP address has already rated
		// OR check if the post id is in the users cookie
		if ( WPB_Purple_Heart_Rating_Free_Db::has_rated( $post_id ) OR $this->is_post_in_cookie( $post_id ) ) {
			// refresh cookie
			$this->set_cookie( $post_id );

			// print headers
			$this->print_json_headers();

			// print error message
			die( json_encode( array( 'error' => 1, 'message' => __( 'You have already rated this post. Thank you!', $this->_plugin_textdomain ) ) ) );
		}

		$ratings = $this->get_rating( $post_id );

		// count the ratings
		// if button was pressed
		if ( 'wpbph-button-ok' == $_REQUEST['button_pressed'] ) {
			$ratings['ok'] += 1;
		}

		// if icon was pressed
		if ( false != strstr( $_REQUEST['button_pressed'], 'wpbph-heart-big' ) ) {
			$ratings['ok'] += 1;
		}

		// if negative button was pressed
		if ( 'wpbph-button-bad' == $_REQUEST['button_pressed'] ) {
			$ratings['bad'] += 1;
		}

		// if the small negative button was pressed
		if ( false != strstr( $_REQUEST['button_pressed'], 'wpbph-heart-small' ) ) {
			$ratings['bad'] += 1;
		}

		// save the ratings
		if ( $this->set_ratings( $post_id, $ratings ) ) {
			// 1. log the ip address of the user
			WPB_Purple_Heart_Rating_Free_Db::log_rating( $post_id );

			// 2. set the cookie
			$this->set_cookie( $post_id );
		}

		// the rating in percent
		$rating_percent = $this->calculate_rating_percent( $ratings );

		$this->print_json_headers();

		die( json_encode( array(
			'error'      => 0,
			'rating_ok'  => $rating_percent['ok'],
			'rating_bad' => $rating_percent['bad']
		) ) );
	}

	/**
	 * Gets the current rating of the post defined with $post_id
	 *
	 * @param int    $post_id
	 * @param string $return What to return. Can either be 'ok', 'bad' or 'both' (which is default)
	 *
	 * @since 1.0
	 *
	 * @return array Has the form array( 'ok' => int, 'bad' => int ) when $return = 'both' is set
	 */
	public static function get_rating( $post_id, $return = 'both' ) {
		$ratings = get_post_meta( $post_id, 'wpbph_ratings', true );
		if ( is_serialized( $ratings ) ) {
			$ratings = unserialize( $ratings );
		}

		// create the rating array if it does not exist
		if ( ! is_array( $ratings ) OR ! isset( $ratings['ok'] ) OR ! isset( $ratings['bad'] ) ) {
			$ratings = array( 'ok' => 0, 'bad' => 0 );
		}

		if ( 'ok' == $return ) {
			return $ratings['ok'];
		}
		if ( 'bad' == $return ) {
			return $ratings['bad'];
		}

		return $ratings;
	}

	/**
	 * Gets the rating in percent
	 *
	 * @param int $post_id
	 *
	 * @since 1.0
	 *
	 * @return array Has the form array( 'ok' => int, 'bad' => int )
	 */
	public function get_rating_percent( $post_id ) {
		$ratings = $this->get_rating( $post_id );

		// don't to division by zero
		if ( 0 == $ratings['ok'] && 0 == $ratings['bad'] ) {
			return array( 'ok' => 50, 'bad' => 50 );
		}

		// do not PHP_ROUND_HALF_UP on PHP Versions lower than 5.3.0
		if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
			$rating_ok_percent = round( $ratings['ok'] * 100 / ( $ratings['ok'] + $ratings['bad'] ), 0 );
		}
		else {
			$rating_ok_percent = round( $ratings['ok'] * 100 / ( $ratings['ok'] + $ratings['bad'] ), 0, PHP_ROUND_HALF_UP );
		}


		$rating_bad_percent = 100 - $rating_ok_percent;

		return array( 'ok' => $rating_ok_percent, 'bad' => $rating_bad_percent );
	}

	/**
	 * Updates the rating of a post
	 *
	 * @param int   $post_id Should have the form array( 'bad' => int, 'ok' => int )
	 * @param array $ratings
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public static function set_ratings( $post_id, $ratings ) {
		return update_post_meta( $post_id, 'wpbph_ratings', serialize( $ratings ) );
	}

	/**
	 * Summing up the good and bad ratings of a posts rating
	 *
	 * @param ind $post_id
	 *
	 * @since 1.0
	 *
	 * @return int
	 */
	private function count_ratings( $post_id ) {
		$ratings = $this->get_rating( $post_id );
		return $ratings['ok'] + $ratings['bad'];
	}


	/**
	 * Activate the plugin
	 * @uses  WPB_Purple_Heart_Rating_Free_Db::create_db_tables
	 * @since 1.0
	 */
	public function on_activation() {

		// create db tables
		WPB_Purple_Heart_Rating_Free_Db::create_db_tables();

		// set "show popup" to true
		$options = get_option( 'wpbph' );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$options['show_tracking_popup'] = 1;

		update_option( 'wpbph', $options );

		$this->check_for_pro_version();
	}

	/**
	 * Deactivate the plugin
	 * @since 1.0
	 */
	public function on_deactivation() {
		if ( ! is_plugin_active( 'purple-heart-rating/purple-heart-rating.php' ) ) {
			// only remove the database when the non-free version is not yet installed
			WPB_Purple_Heart_Rating_Free_Db::remove_db_tables();
		}
		$this->track( array( 'plugin_deactivated' => date( 'r' ) ) );
	}


	/**
	 * Gets the IP address of the user and anonymize it
	 *
	 * @param bool $hashed Set to true if the IP should be returned by using MD5
	 *
	 * @return string
	 * @since 1.0
	 */
	public static function get_user_ip_addr( $hashed = true ) {
		$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
		if ( ! empty( $_SERVER['X_FORWARDED_FOR'] ) ) {
			$X_FORWARDED_FOR = explode( ',', $_SERVER['X_FORWARDED_FOR'] );
			if ( ! empty( $X_FORWARDED_FOR ) ) {
				$REMOTE_ADDR = trim( $X_FORWARDED_FOR[0] );
			}
		}
		/*
		* Some php environments will use the $_SERVER['HTTP_X_FORWARDED_FOR']
		* variable to capture visitor address information.
		*/
		elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$HTTP_X_FORWARDED_FOR = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			if ( ! empty( $HTTP_X_FORWARDED_FOR ) ) {
				$REMOTE_ADDR = trim( $HTTP_X_FORWARDED_FOR[0] );
			}
		}
		$ip = preg_replace( '/[^0-9a-f:\., ]/si', '', $REMOTE_ADDR );

		if ( $hashed ) {
			return md5( $ip );
		}

		return $ip;
	}


	/**
	 * Gets the User Agent of the user
	 * @since 1.0
	 * @return string
	 */
	public static function get_user_agent() {
		return substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 );
	}


	/**
	 * Set a cookie for a page, post or custom post type.
	 * It does not really matter whether the cookie is already set. If it is set, it will be refreshed with the new data
	 *
	 * @param int    $post_id
	 *
	 * @param string $post_type
	 *
	 * @return void
	 * @since 1.0
	 */
	private function set_cookie( $post_id, $post_type = 'post' ) {
		$time_back = time() + WPB_Purple_Heart_Rating_Free_Db::get_time_back();

		if ( isset( $_COOKIE['wpbph_rating_cook'] ) ) {

			$cookie = urldecode( $_COOKIE['wpbph_rating_cook'] );

			if ( is_serialized( $cookie ) ) {
				$cookie = @unserialize( $cookie );
			}

			if ( false == $cookie ) {
				$cookie = array();
			}

			if ( ! is_array( $cookie ) ) {
				$cookie = array();
			}
		}
		else {
			$cookie = array();
		}

		// set the new value
		$cookie[$post_type][$post_id] = 1;

		// set the cookie
		setcookie( 'wpbph_rating_cook', urlencode( serialize( $cookie ) ), $time_back, COOKIEPATH, COOKIE_DOMAIN, false );
	}

	/**
	 * Just prints some headers to return json later on the current request
	 * @since 1.0
	 */
	private function print_json_headers() {

		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-type: application/json' );
	}

	/**
	 * Checks if a post id is already in the users cookie
	 *
	 * @param int    $post_id
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 * @since 1.0
	 */
	private function is_post_in_cookie( $post_id, $post_type = 'post' ) {
		// return false if cookie is not set
		if ( ! isset( $_COOKIE['wpbph_rating_cook'] ) ) {
			return false;
		}

		$cookie = urldecode( $_COOKIE['wpbph_rating_cook'] );

		// return false if cookie is not serialized
		if ( ! is_serialized( $cookie ) ) {
			return false;
		}

		// get the cookie
		$cookie = @unserialize( $cookie );

		if ( false == $cookie ) {
			return false;
		}

		// return false if this is not an array
		if ( ! isset( $cookie[$post_type][$post_id] ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Adds Javascript to the footer which displays the WPPointer Tooltip
	 * @since 1.0
	 */
	public function wp_pointer_message() {

		if ( ! (bool) $this->get_option( 'show_tracking_popup' ) ) {
			return;
		}

		$popup_content = '<h3>' . __( 'Start customizing your purple heart rating plugin', $this->_plugin_textdomain ) . '</h3>';
		$popup_content .= '<p>' . __( 'Please help to improve this plugin and allow tracking.', $this->_plugin_textdomain ) . '</p>';
		?>
		<script type="text/javascript">
			//<![CDATA[

			(function ($) {
				var wpbph_pointer_options, setup, button;

				wpbph_pointer_options = $.extend(wpbph_pointer_options, {
					'position': {'edge': 'left', 'align': 'center'},
					'content' : '<?php echo $popup_content; ?>',
					buttons   : function (event, t) {
						button = jQuery('<a id="pointer-close" style="margin-right:5px" class="button-secondary"><?php echo __( 'Not now!', $this->_plugin_textdomain ); ?></a>');
						button.bind('click.pointer', function () {
							t.element.pointer('close');
						});
						return button;
					},
					close     : function () {
					}
				});


				setup = function () {
					jQuery('#toplevel_page_wpbph-settings').pointer(wpbph_pointer_options).pointer('open');

					jQuery('#pointer-close').before('<a id="pointer-primary" class="button button-primary">' + '<?php echo __( 'Okay!', $this->_plugin_textdomain ); ?>' + '</a>');
					jQuery('#pointer-primary').click(function () {
						jQuery.post(ajaxurl, { 'action': 'wpbph_ajax_tracking', 'tracking': 1 }, function (response) {
							if (1 == response.error) {
								alert(response.message);
							} else {
								jQuery('#toplevel_page_wpbph-settings').pointer('close');
								window.location = '<?php echo admin_url( 'admin.php?page=wpbph-settings' ); ?>';
							}
						}, 'json');
					});
					jQuery('#pointer-close').click(function () {
						jQuery.post(ajaxurl, { 'action': 'wpbph_ajax_tracking', 'tracking': 0 }, function (response) {
							if (1 == response.error) {
								alert(response.message);
							} else {
								jQuery('#toplevel_page_wpbph-settings').pointer('close');
								window.location = '<?php echo admin_url( 'admin.php?page=wpbph-settings' ); ?>';
							}
						}, 'json');
					});

				};

				jQuery(document).ready(setup);
			})(jQuery);

		</script>

	<?php

	}

	/**
	 * Proceeds the form post to allow / disallow tracking
	 * @since 1.0
	 */
	public function ajax_tracking() {
		if ( ! isset( $_POST['tracking'] ) ) {
			$this->print_json_headers();
			die( json_encode( array( 'error' => 1, 'message' => __( 'Sorry, we cannot proceed this action.', $this->_plugin_textdomain ) ) ) );
		}

		$options = get_option( 'wpbph' );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		// update the option
		$options['tracking']            = intval( $_POST['tracking'] );
		$options['show_tracking_popup'] = 0;

		// save option
		update_option( 'wpbph', $options );

		$this->print_json_headers();

		die( json_encode( array(
			'error'   => 0,
			'message' => __( 'Options updated!', $this->_plugin_textdomain )
		) ) );
	}


	/**
	 * Adds links to the plugins menu (where the plugins are listed)
	 *
	 * @param array $links
	 *
	 * @since 1.0
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="' . get_admin_url( null, 'admin.php?page=wpbph-settings' ) . '">' . __( 'Settings', $this->get_textdomain() ) . '</a>';
		$links[] = '<a href="http://wp-buddy.com/products/" target="_blank">' . __( 'More Plugins by WPBuddy', $this->get_textdomain() ) . '</a>';
		return $links;
	}


	/**
	 * Add Font Awesome IE Styles
	 * @since 1.0
	 */
	public function wp_head() {
		?>
		<!--[if IE 7]>
		<link href="//netdna.bootstrapcdn.com/font-awesome/3.0.2/css/font-awesome-ie7.css" rel="stylesheet" />
		<style type="text/css">
			.wpbph-info, .wpbph-table-td {
				float: left;
			}

			.wpbph-table-td {
				width: 45%;
			}

			.wpbph-value {
				margin: 20px 0 0 10px;
			}

			.wpbph-bad-value {
				margin: 0 0 0 10px;
			}

			.wpbph-table-tr {
				display: block;
			}

		</style>
		<![endif]-->
	<?php
	}


	/**
	 * Imports data from the GD Star Rating Plugin
	 * @global wpdb $wpdb
	 * @since 1.0
	 */
	private function import_gd() {
		if ( ! isset ( $_GET['action'] ) ) {
			return;
		}
		if ( 'import_gd' != $_GET['action'] ) {
			return;
		}

		$table_exists = WPB_Purple_Heart_Rating_Free_Db::table_exists( 'gdsr_data_article' );

		if ( ! $table_exists ) {
			echo '<div class="error"><p><strong>' . __( 'Could not find GD Star Rating table!', $this->_plugin_textdomain ) . '</strong></p></div>';
			return;
		}

		//import post ratings

		if ( WPB_Purple_Heart_Rating_Free_Db::import_from_gd_rating() ) {
			// import comment ratings
			WPB_Purple_Heart_Rating_Free_Db::import_comments_from_gd_rating();

			// deactivate GD Star rating
			deactivate_plugins( '/gd-star-rating/gd-star-rating.php' );

			echo '<div class="error updated"><p><strong>' . __( 'Successfully imported from GD Star rating! GD Star Rating has been deactivated.', $this->_plugin_textdomain ) . '</strong></p></div>';
		}
		else {
			echo '<div class="error"><p><strong>' . __( 'Could not import your data from the GD Star Rating plugin.', $this->_plugin_textdomain ) . '</strong></p></div>';
		}

	}


	/**
	 * Returns if tracking is on
	 * @since 1.0
	 * @return bool
	 */
	public function is_tracking() {
		return (bool) $this->get_option( 'tracking' );
	}


	/**
	 * When Caching is active the ratings has to be reloaded
	 * @since 1.2
	 */
	public function ajax_refresh_post_ratings() {
		if ( ! isset( $_REQUEST['post_id'] ) ) {
			$this->print_json_headers();
			die( json_encode( array( 'error' => 1, 'message' => __( 'Error while refreshing the current ratings.', $this->_plugin_textdomain ) ) ) );
		}

		$return_array = array( 'error' => 0, 'comments' => array() );

		$post_id = intval( $_REQUEST['post_id'] );

		$ratings = $this->get_rating( $post_id );

		// the rating in percent
		$ratings_percent = $this->calculate_rating_percent( $ratings );

		$return_array['rating_ok']  = $ratings_percent['ok'];
		$return_array['rating_bad'] = $ratings_percent['bad'];

		$this->print_json_headers();

		die( json_encode( $return_array ) );
	}


	/**
	 * Calculates the rating of the post in percentage
	 * @since    1.2
	 *
	 * @param string|int|array $oks  A number of oks or an array of key value pairs (like: ok => 5, bad = 10)
	 * @param int              $bads
	 *
	 * @return array in the form of (ex.) ok => 5, bad => 10
	 */
	private function calculate_rating_percent( $oks, $bads = 0 ) {
		if ( isset( $oks['bad'] ) ) {
			$bads = $oks['bad'];
		}
		if ( isset( $oks['ok'] ) ) {
			$oks = $oks['ok'];
		}

		// don't to division by zero
		if ( 0 == $oks && 0 == $bads ) {
			$rating_ok_percent  = 50;
			$rating_bad_percent = 50;
		}
		else {
			if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
				$rating_ok_percent = round( $oks * 100 / ( $oks + $bads ), 0 );
			}
			else {
				$rating_ok_percent = round( $oks * 100 / ( $oks + $bads ), 0, PHP_ROUND_HALF_UP );
			}

			$rating_bad_percent = 100 - $rating_ok_percent;
		}

		return array( 'ok' => $rating_ok_percent, 'bad' => $rating_bad_percent );
	}


	/**
	 * Checks if a notice should be thrown
	 * also adds css to the head
	 * @since 1.1
	 */
	public function upgrade_notices() {

		$now          = current_time( 'timestamp' );
		$upgrade_time = $this->get_option( 'free_version_upgraded_time' );

		$upgrade_time_future = $upgrade_time + 60 * 60 * 24 * 30; // 30 days

		if ( $now <= $upgrade_time_future ) {
			return;
		}

		if ( isset( $_GET['wpbph_remove_upgrade_notice'] ) && 1 == $_GET['wpbph_remove_upgrade_notice'] ) {
			$options                               = get_option( 'wpbph' );
			$options['free_version_upgraded_time'] = current_time( 'timestamp' );
			update_option( 'wpbph', $options );
			return;
		}

		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				var ph_notice_ratio = 4.05;
				var ph_notice_width = jQuery('#phpr_upgrade_notice').width();
				jQuery("#phpr_upgrade_notice").css('height', (ph_notice_width / ph_notice_ratio));

				var ph_notice_font_ratio = 65;
				var ph_notice_line_ratio = 57;
				jQuery('.phpr_upgrade_notice_t').css('fontSize', (ph_notice_width / ph_notice_font_ratio)).css('lineHeight', (ph_notice_width / ph_notice_line_ratio) + 'px');

				var ph_notice_smallfont_ratio = 120;
				jQuery('.phpr_upgrade_notice_tb').css('fontSize', (ph_notice_width / ph_notice_smallfont_ratio));


				jQuery(window).resize(function () {
					var ph_notice_ratio = 4.05;
					var ph_notice_width = jQuery('#phpr_upgrade_notice').width();
					jQuery("#phpr_upgrade_notice").css('height', (ph_notice_width / ph_notice_ratio));

					var ph_notice_font_ratio = 65;
					var ph_notice_line_ratio = 57;
					jQuery('.phpr_upgrade_notice_t').css('fontSize', (ph_notice_width / ph_notice_font_ratio)).css('lineHeight', (ph_notice_width / ph_notice_line_ratio) + 'px');

					var ph_notice_smallfont_ratio = 100;
					jQuery('.phpr_upgrade_notice_tb').css('fontSize', (ph_notice_width / ph_notice_smallfont_ratio));
				});

			});
		</script>

		<style type="text/css">
			#phpr_upgrade_notice {
				background-image: url('http://wp-buddy.com/wp-content/uploads/2013/08/purple-heart-rating-upgrade-theaser.jpg');
				background-repeat: no-repeat;
				background-size: contain;
				border: 0px none;
				background-color: #fb4354;
				color: #ffffff;
				font-size: 20px;
				height: 353px;
				position: relative;
				background-position: left 10px;
			}

			#phpr_upgrade_notice p {
				color: #ffffff;
			}

			.phpr_upgrade_notice_t {
				float: left;
				font-size: 16px;
				line-height: 20px;
				margin-top: 17px;
				padding: 0 4%;
				width: 25%;
			}

			.phpr_upgrade_notice_tb {
				position: absolute;
				bottom: 0;
				display: block;
			}

			#phpr_upgrade_notice_t1b {
				left: 10%;
			}

			#phpr_upgrade_notice_t2b {
				left: 33%;
			}

			#phpr_upgrade_notice_t3b {
				left: 55%;
			}

			#phpr_upgrade_notice_close {
				float: right;
				color: #ffffff;
				font-weight: bold;
				margin-top: 10px;
				position: absolute;
				right: 10px;;
			}

			#phpr_upgrade_notice_btn {
				background-color: #5C00B7;
				border: 2px solid #FFFFFF;
				border-radius: 10px 10px 10px 10px;
				color: #FFFFFF;
				font-size: 25px;
				line-height: 30px;
				padding: 20px;
				position: absolute;
				right: 1%;
				text-align: center;
				bottom: 16%;
				width: 20%;
				font-weight: bold;
			}

			@media (max-width: 1200px) {
				#phpr_upgrade_notice_btn {
					font-size: 20px;
					line-height: 25px;
					padding: 5px;

				}
			}

		</style>
		<?php

		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
	}


	/**
	 * Shows a message on the plugin-screen after some time to remember a user to upgrade to purple heart pro
	 * @since 1.1
	 */
	public function admin_notices() {

		global $current_user;
		get_currentuserinfo();
		$name = $current_user->user_firstname;
		if ( empty( $name ) ) {
			$name = $current_user->display_name;
		}

		?>
		<div id="phpr_upgrade_notice" class="updated">
			<div>
				<a id="phpr_upgrade_notice_close" href="<?php echo admin_url( 'plugins.php?wpbph_remove_upgrade_notice=1' ); ?>">x</a>
			</div>
			<p>
				<span id="phpr_upgrade_notice_t1" class="phpr_upgrade_notice_t"><?php echo sprintf( __( '&ldquo;Servus %s! You have been using the free version of the Purple Heart Rating Plugin.&rdquo;', $this->get_textdomain() ), $name ); ?></span>
				<span id="phpr_upgrade_notice_t2" class="phpr_upgrade_notice_t"><?php echo __( '&ldquo;Maybe it\'s a good time to upgrade now to the pro-version?&rdquo;', $this->get_textdomain() ); ?></span>
				<span id="phpr_upgrade_notice_t3" class="phpr_upgrade_notice_t"><?php echo __( '&ldquo;Join many others who are benefitting from the full list of features for their online success!&rdquo;', $this->get_textdomain() ); ?></span>
			</p>

			<p>
				<span id="phpr_upgrade_notice_t1b" class="phpr_upgrade_notice_t phpr_upgrade_notice_tb"><?php echo __( 'Dave - Marketing-Buddy', $this->get_textdomain() ); ?></span>
				<span id="phpr_upgrade_notice_t2b" class="phpr_upgrade_notice_t phpr_upgrade_notice_tb"><?php echo __( 'Flow - Code-Buddy', $this->get_textdomain() ); ?></span>
				<span id="phpr_upgrade_notice_t3b" class="phpr_upgrade_notice_t phpr_upgrade_notice_tb"><?php echo __( 'Duke - Design-Buddy', $this->get_textdomain() ); ?></span>
			</p>

			<a id="phpr_upgrade_notice_btn" href="http://wp-buddy.com/products/plugins/purple-heart-rating-wordpress-plugin/"  target="_blank"><?php echo __( 'COMPARE free & pro', $this->get_textdomain() ); ?></a>
		</div>
	<?php
	}

	/**
	 * @since 1.1
	 */
	function on_upgrade() {
		$options                               = get_option( 'wpbph' );
		$options['free_version_upgraded_time'] = current_time( 'timestamp' );
		update_option( 'wpbph', $options );
	}

}


