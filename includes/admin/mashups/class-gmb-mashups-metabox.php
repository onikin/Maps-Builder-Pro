<?php

/**
 * Google Maps Mashups
 *
 * Adds mashup metaboxes to user selected post types with Maps Builder Pro
 *
 * @package   Google_Maps_Builder_Admin
 * @author    WordImpress
 * @license   GPL-2.0+
 * @link      http://wordimpress.com
 * @copyright 2015 WordImpress
 */
class Google_Maps_Builder_Mashups_Metabox {

	public $enabled_post_types;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     2.0
	 */
	public function __construct() {

		$this->plugin_slug = Google_Maps_Builder()->get_plugin_slug();

		//Add metaboxes and fields to CPT
		add_action( 'cmb2_init', array( $this, 'mashup_metabox_fields' ) );
		add_action( 'cmb2_render_google_mashup_geocoder', array( $this, 'cmb2_render_google_mashup_geocoder' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_mashup_scripts' ) );

	}


	/**
	 * Mashup Metabox Field
	 *
	 * Defines the Google Places CPT metabox and field configuration
	 *
	 * @since  2.0
	 * @return array
	 */
	public function mashup_metabox_fields() {

		$this->enabled_post_types = gmb_get_option( 'gmb_mashup_metabox' );

		$prefix = '_gmb_';

		//Output metabox on appropriate CPTs
		$preview_box = cmb2_get_metabox( array(
			'id'           => 'google_maps_mashup_metabox',
			'title'        => __( 'Maps Builder Pro Mashup', $this->plugin_slug ),
			'object_types' => $this->enabled_post_types, // post type
			'context'      => 'normal', //  'normal', 'advanced', or 'side'
			'priority'     => 'default', //  'high', 'core', 'default' or 'low'
			'show_names'   => true, // Show field names on the left
		) );
		$preview_box->add_field( array(
			'name'    => __( 'Add Location', $this->plugin_slug ),
			'id'      => $prefix . 'mashup_autocomplete',
			'type'    => 'google_mashup_geocoder',
			'default' => '',
		) );

		$preview_box->add_field( array(
			'name' => __( 'Marker Latitude', $this->plugin_slug ),
			'id'   => $prefix . 'lat',
			'type' => 'text',
		) );
		$preview_box->add_field( array(
			'name' => __( 'Marker Longitude', $this->plugin_slug ),
			'id'   => $prefix . 'lng',
			'type' => 'text',
		) );
		$preview_box->add_field( array(
			'name' => __( 'Address', $this->plugin_slug ),
			'id'   => $prefix . 'address',
			'type' => 'text',
		) );

		$preview_box->add_field( array(
			'name' => __( 'Marker Place ID', $this->plugin_slug ),
			'id'   => $prefix . 'place_id',
			'type' => 'text',
		) );
	}

	/**
	 * Enqueue Mashup Scripts
	 */
	public function enqueue_mashup_scripts() {

		if ( $this->is_mashup_metabox_enabled() == false ) {
			return false;
		}

		Google_Maps_Builder()->scripts->check_for_multiple_google_maps_api_calls();

		wp_register_script( $this->plugin_slug . '-admin-mashups-scripts', GMB_PLUGIN_URL . 'assets/js/admin/admin-maps-mashup-metabox.js', array(
			'jquery',
			$this->plugin_slug . '-gmaps'
		) );
		wp_enqueue_script( $this->plugin_slug . '-admin-mashups-scripts' );


	}

	/**
	 * Is Mashup Metabox Enabled
	 *
	 * Conditional
	 * @return bool
	 */
	public function is_mashup_metabox_enabled() {

		$current_screen = get_current_screen();

		//False if not enabled or array (sanity check)
		if ( empty( $this->enabled_post_types ) || ! is_array( $this->enabled_post_types ) ) {
			return false;
		}

		//False if not enabled on this post type
		if ( ! isset( $current_screen->post_type ) || ! in_array( $current_screen->post_type, $this->enabled_post_types ) ) {
			return false;
		}

		//Bail if this isn't the post type in should be enabled on either
		return true;

	}


	/**
	 * Custom Google Autocomplete for Mashup
	 *
	 * @since  2.0
	 *
	 * @param $field
	 * @param $meta
	 *
	 * @return array
	 */
	function cmb2_render_google_mashup_geocoder( $field, $meta ) {

		$meta = wp_parse_args(
			$meta, array(
				'geocode' => '',
			)
		);

		$output = '<div class="autocomplete-wrap">';
		$output .= '<input type="text" name="' . $field->args( 'id' ) . '[geocode]" id="' . $field->args( 'id' ) . '" value="" class="search-autocomplete" />';
		$output .= '<p class="autocomplete-description"> ' . __( 'Enter the name of a point of interest, address, or establishment above or manually set the fields below.', $this->plugin_slug ) . '</p>';
		$output .= '</div>';

		echo $output;

	}


} //end class

new Google_Maps_Builder_Mashups_Metabox();