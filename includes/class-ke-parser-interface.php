<?php
/**
 * Kontentainment Events Parser Interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface KE_Parser_Interface {

	/**
	 * Get the unique name of the parser
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Check if this parser can handle the given URL
	 *
	 * @param string $url
	 * @return bool
	 */
	public function can_handle( $url );

	/**
	 * Parse the remote HTML and return normalized data
	 *
	 * @param string $html
	 * @param string $url
	 * @return array
	 */
	public function parse( $html, $url );
}
