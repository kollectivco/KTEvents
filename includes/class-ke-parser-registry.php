<?php
/**
 * Kontentainment Events Parser Registry
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Parser_Registry {

	protected static $instance = null;
	private $parsers = array();

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Register default parsers
		$this->register_parser( new KE_Parser_Generic() );
		$this->register_parser( new KE_Parser_SceneNow() );
		$this->register_parser( new KE_Parser_CairoJazzClub() );
	}

	/**
	 * Register a new parser
	 */
	public function register_parser( KE_Parser_Interface $parser ) {
		$this->parsers[ $parser->get_name() ] = $parser;
	}

	/**
	 * Select the best parser for the given URL
	 */
	public function get_parser_for_url( $url ) {
		// Iterate over registered parsers and pick the first one that can handle the URL
		foreach ( $this->parsers as $parser ) {
			if ( 'generic' !== $parser->get_name() && $parser->can_handle( $url ) ) {
				return $parser;
			}
		}

		// Fallback to generic
		return isset( $this->parsers['generic'] ) ? $this->parsers['generic'] : null;
	}

	/**
	 * Get all registered parsers
	 */
	public function get_all_parsers() {
		return $this->parsers;
	}
}
KE_Parser_Registry::get_instance();
