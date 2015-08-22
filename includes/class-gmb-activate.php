<?php

/**
 * Plugin Activation
 *
 * @package   Google_Maps_Builder
 * @author    Devin Walker <devin@wordimpress.com>
 * @license   GPL-2.0+
 * @link      http://wordimpress.com
 * @copyright 2015 WordImpress, Devin Walker
 */
class Google_Maps_Builder_Activate {


	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		$this->plugin_slug = Google_Maps_Builder()->get_plugin_slug();
		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		//Activation tooltips
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_pointer_script_style' ) );
	}


	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide       True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		//Remove Welcome Message Meta so User Sees it Again
		global $current_user;
		$user_id = $current_user->ID;
		delete_user_meta( $user_id, 'gmb_hide_pro_welcome' );

		//Display Tooltip
		$dismissed_pointers = explode( ',', get_user_meta( $user_id, 'dismissed_wp_pointers', true ) );

		// Check if our pointer is among dismissed ones and delete that mofo
		if ( in_array( 'gmb_welcome_pointer', $dismissed_pointers ) ) {
			$key = array_search( 'gmb_welcome_pointer', $dismissed_pointers );
			delete_user_meta( $user_id, 'dismissed_wp_pointers', $key['gmb_welcome_pointer'] );
		}


		//Multisite Checks
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide       True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}


	/**
	 * ADMIN: Activation Welcome Tooltip Scripts
	 *
	 * @param $hook
	 */
	function admin_enqueue_pointer_script_style( $hook ) {

		global $post;

		// Assume pointer shouldn't be shown
		$enqueue_pointer_script_style = false;

		//For testing ONLY!:
//		delete_user_meta( get_current_user_id(), 'dismissed_wp_pointers' );

		// Get array list of dismissed pointers for current user and convert it to array
		$dismissed_pointers = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		$key                = array_search( 'gmb_welcome_pointer', $dismissed_pointers ); // $key = 2;


		//Welcome Tooltip - Check if our pointer is not among dismissed ones
		if ( ! in_array( 'gmb_welcome_pointer', $dismissed_pointers ) ) {
			$enqueue_pointer_script_style = true;

			// Add footer scripts using callback function
			add_action( 'admin_print_footer_scripts', array( $this, 'welcome_pointer_print_scripts' ) );
		}

		// Map Customizer Tooltip - Check if our pointer is not among dismissed ones
		if ( ! in_array( 'gmb_customizer_pointer', $dismissed_pointers ) && isset($post->post_type) && $post->post_type === 'google_maps' ) {
			$enqueue_pointer_script_style = true;

			// Add footer scripts using callback function
			add_action( 'admin_print_footer_scripts', array( $this, 'maps_customizer_tooltip' ) );
		}

		// Enqueue pointer CSS and JS files, if needed
		if ( $enqueue_pointer_script_style ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
		}

	}

	/**
	 * Welcome Activation Message
	 */
	function welcome_pointer_print_scripts() {
		global $current_user;
		get_currentuserinfo();
		$pointer_content = '<h3>' . __( 'Welcome to Maps Builder Pro', $this->plugin_slug ) . '</h3>';
		$pointer_content .= '<p>' . __( 'Thank you for activating Maps Builder Pro for WordPress. To stay up to date on the latest plugin updates, enhancements, and news please sign up for our newsletter.', $this->plugin_slug ) . '</p>';
		$pointer_content .= '<div id="mc_embed_signup" style="padding: 0 15px;"><form action="http://wordimpress.us3.list-manage2.com/subscribe/post?u=3ccb75d68bda4381e2f45794c&amp;id=83609e2883" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate><div class="mc-field-group" style="margin: 0 0 10px;"><input type="email"  value="' . $current_user->user_email . '"  name="EMAIL" class="required email" id="mce-EMAIL" style="margin-right:5px;width:230px;" ><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div><div id="mce-responses" class="clear"><div class="response" id="mce-error-response" style="display:none"></div><div class="response" id="mce-success-response" style="display:none"></div></div><div style="position: absolute; left: -5000px;"><input type="text" name="b_3ccb75d68bda4381e2f45794c_83609e2883" value=""></div></form></div>';
		?>

		<script type="text/javascript">
			//<![CDATA[
			jQuery( document ).ready( function ( $ ) {
				$( '#menu-posts-google_maps' ).pointer( {
					content     : '<?php echo $pointer_content; ?>',
					position    : {
						edge : 'left', // arrow direction
						align: 'center' // vertical alignment
					},
					pointerWidth: 350,
					close       : function () {
						$.post( ajaxurl, {
							pointer: 'gmb_welcome_pointer', // pointer ID
							action : 'dismiss-wp-pointer'
						} );
					}
				} ).pointer( 'open' );
			} );
			//]]>
		</script>

		<?php
	}

	/**
	 * Maps Builder Customizer Tooltio
	 */
	function maps_customizer_tooltip() {
		global $current_user;
		get_currentuserinfo();

		$pointer_content = '<h3>' . __( 'Introducing the Map Builder', $this->plugin_slug ) . '</h3>';
		$pointer_content .= '<p>' . __( 'We have upgraded your map building experience! Click here to experience the new interface. All controls are within your reach and the map always stays in view. If you like it, you can enable the view by default within the plugin settings. We hope you enjoy it!', $this->plugin_slug ) . '</p>';
		?>

		<script type="text/javascript">
			//<![CDATA[
			jQuery( document ).ready( function ( $ ) {
				$( '#map-builder' ).pointer( {
					content     : '<?php echo $pointer_content; ?>',
					position    : {
						edge : 'right', // arrow direction
						align: 'center' // vertical alignment
					},
					pointerWidth: 350,
					close       : function () {
						$.post( ajaxurl, {
							pointer: 'gmb_customizer_pointer', // pointer ID
							action : 'dismiss-wp-pointer'
						} );
					}
				} ).pointer( 'open' );
			} );
			//]]>
		</script>

		<?php
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int $blog_id ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}


}