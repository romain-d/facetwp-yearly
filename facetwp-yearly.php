<?php
/*
Plugin Name: FacetWP - Yearly
Plugin URI: https://github.com/petitphp/facetwp-yearly
Description: Filter your posts by yearly archive
Version: 1.0.0
Author: Romain DORR
Author URI: https://github.com/romain-d
*/

/**
 * Based On : https://github.com/petitphp/facetwp-monthly
 */

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

define( 'FWP_YEARLY_VER', '1.0.0' );
define( 'FWP_YEARLY_URL', plugin_dir_url( __FILE__ ) );
define( 'FWP_YEARLY_DIR', plugin_dir_path( __FILE__ ) );

class FWP_YEARLY {

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}


	/**
	 * Intialize.
	 */
	function init() {
		add_filter( 'facetwp_facet_types', array( $this, 'register_facet_type' ) );
	}

	/**
	 * Register the "yearly" facet type.
	 *
	 * @param array $facet_types list of registered facets
	 *
	 * @return array
	 */
	function register_facet_type( $facet_types ) {
		include( dirname( __FILE__ ) . '/classes/yearly.php' );
		$facet_types['monthly'] = new FacetWP_Facet_Yearly();

		return $facet_types;
	}
}

$fwp_p2p = new FWP_YEARLY();
