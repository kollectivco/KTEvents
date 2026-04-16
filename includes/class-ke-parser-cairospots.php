<?php
/**
 * Kontentainment Events CairoSpots Parser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Parser_CairoSpots implements KE_Parser_Interface {

	public function get_name() {
		return 'cairospots';
	}

	/**
	 * Matches URLs from cairospots.com
	 */
	public function can_handle( $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		return ( $host && strpos( $host, 'cairospots.com' ) !== false );
	}

	/**
	 * Parse CairoSpots HTML
	 */
	public function parse( $html, $url ) {
		// Start with generic parsing as baseline (handles JSON-LD which is present on CairoSpots)
		$generic = new KE_Parser_Generic();
		$result = $generic->parse( $html, $url );
		
		$result['parser_name'] = $this->get_name();
		$result['source_name'] = 'CairoSpots';
		
		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		$xpath = new DOMXPath( $dom );

		// 1. Enhanced Title (CairoSpots specific)
		if ( empty( $result['fields']['title'] ) || strlen( $result['fields']['title'] ) < 5 ) {
			$title_nodes = $xpath->query( "//h1[contains(@class, 'tribe-events-single-event-title')]" );
			if ( $title_nodes->length > 0 ) {
				$result['fields']['title'] = trim( $title_nodes->item(0)->nodeValue );
			}
		}

		// 2. Date & Time (The Events Calendar specific fallbacks)
		// Usually handled by JSON-LD, but just in case:
		if ( empty( $result['fields']['event_date'] ) ) {
			$start_date_node = $xpath->query( "//*[contains(@class, 'tribe-event-date-start')]" );
			if ( $start_date_node->length > 0 ) {
				$date_text = trim( $start_date_node->item(0)->nodeValue );
				$timestamp = strtotime( $date_text );
				if ( $timestamp ) {
					$result['fields']['event_date'] = date( 'Y-m-d', $timestamp );
					if ( empty( $result['fields']['event_time'] ) ) {
						$result['fields']['event_time'] = date( 'H:i', $timestamp );
					}
				}
			}
		}

		// 3. Venue (The Events Calendar specific fallbacks)
		if ( empty( $result['fields']['venue_name'] ) ) {
			$venue_node = $xpath->query( "//*[contains(@class, 'tribe-events-venue')]//a" );
			if ( $venue_node->length > 0 ) {
				$result['fields']['venue_name'] = trim( $venue_node->item(0)->nodeValue );
			}
		}

		if ( empty( $result['fields']['address'] ) ) {
			$address_node = $xpath->query( "//*[contains(@class, 'tribe-address')]" );
			if ( $address_node->length > 0 ) {
				$result['fields']['address'] = $generic->clean_text( $address_node->item(0)->nodeValue );
			}
		}

		// Increase confidence if we identified CairoSpots specific markers
		if ( $result['parser_confidence'] < 90 ) {
			$result['parser_confidence'] = 95;
		}

		return $result;
	}
}
