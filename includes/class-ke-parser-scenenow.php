<?php
/**
 * Kontentainment Events SceneNow Parser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Parser_SceneNow implements KE_Parser_Interface {

	public function get_name() {
		return 'scenenow';
	}

	/**
	 * Matches URLs like scenenow.com/Events/Detail/...
	 */
	public function can_handle( $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		$path = wp_parse_url( $url, PHP_URL_PATH );
		
		return ( strpos( $host, 'scenenow.com' ) !== false && strpos( $path, '/Events/Detail/' ) !== false );
	}

	/**
	 * Parse SceneNow HTML
	 */
	public function parse( $html, $url ) {
		// Start with generic parsing as baseline (handles JSON-LD)
		$generic = new KE_Parser_Generic();
		$result = $generic->parse( $html, $url );
		
		$result['parser_name'] = $this->get_name();
		$result['source_name'] = 'SceneNow';
		
		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		$xpath = new DOMXPath( $dom );

		// 1. Enhanced Title (Fallback)
		if ( empty( $result['fields']['title'] ) || strlen( $result['fields']['title'] ) < 5 ) {
			$title_nodes = $xpath->query( "//h1 | //div[contains(@class, 'event-title')] | //h2[contains(@class, 'title')]" );
			if ( $title_nodes->length > 0 ) {
				$result['fields']['title'] = trim( $title_nodes->item(0)->nodeValue );
			}
		}

		// 2. Date & Time (Fallback)
		if ( empty( $result['fields']['event_date'] ) ) {
			$date_val = $this->get_value_by_label( $xpath, 'Date' );
			if ( $date_val ) {
				$result['fields']['raw_date_text'] = $date_val;
				$timestamp = strtotime( $date_val );
				if ( $timestamp && $timestamp > 100000 ) {
					$result['fields']['event_date'] = date( 'Y-m-d', $timestamp );
				}
			}
		}

		if ( empty( $result['fields']['event_time'] ) ) {
			$time_val = $this->get_value_by_label( $xpath, 'Time' );
			if ( $time_val ) {
				$ts = strtotime( $time_val );
				if ( $ts ) $result['fields']['event_time'] = date( 'H:i', $ts );
			}
		}

		// 3. Venue & Location (Fallback)
		if ( empty( $result['fields']['venue_name'] ) ) {
			$venue_val = $this->get_value_by_label( $xpath, 'Venue' );
			if ( ! $venue_val ) $venue_val = $this->get_value_by_label( $xpath, 'Place' );
			if ( ! $venue_val ) $venue_val = $this->get_value_by_label( $xpath, 'Location' );
			
			if ( $venue_val ) {
				$result['fields']['venue_name'] = $venue_val;
			}
		}

		if ( empty( $result['fields']['address'] ) ) {
			$address_val = $this->get_value_by_label( $xpath, 'Address' );
			if ( $address_val ) {
				$result['fields']['address'] = $address_val;
			}
		}

		// 4. Contact (Fallback)
		if ( empty( $result['fields']['phone'] ) ) {
			$phone_val = $this->get_value_by_label( $xpath, 'Phone' );
			if ( ! $phone_val ) $phone_val = $this->get_value_by_label( $xpath, 'Telephone' );
			if ( $phone_val ) $result['fields']['phone'] = $phone_val;
		}

		// 5. Category (Fallback)
		if ( empty( $result['fields']['category'] ) ) {
			$cat_val = $this->get_value_by_label( $xpath, 'Category' );
			if ( $cat_val ) $result['fields']['category'] = $cat_val;
		}

		// Confidence Logic Enhancement
		$confidence = $result['parser_confidence'] > 50 ? $result['parser_confidence'] : 50;
		if ( ! empty( $result['fields']['event_date'] ) ) $confidence += 20;
		if ( ! empty( $result['fields']['venue_name'] ) ) $confidence += 20;

		$result['parser_confidence'] = min( 100, $confidence );

		// Warnings for critical missing data
		if ( empty( $result['fields']['event_date'] ) ) {
			$result['warnings'][] = 'Critical: Could not extract a valid event date. Please set it manually.';
		}
		if ( empty( $result['fields']['venue_name'] ) ) {
			$result['warnings'][] = 'Warning: Venue name missing. Automatic venue creation will not work.';
		}

		return $result;
	}

	/**
	 * Helper to find text after a label (e.g. <strong>Date:</strong> 25 May)
	 */
	private function get_value_by_label( $xpath, $label ) {
		$nodes = $xpath->query( "//*[contains(text(), '$label')]/following-sibling::*[1] | //*[contains(text(), '$label')]/parent::*[1]" );
		foreach ( $nodes as $node ) {
			$text = trim( $node->nodeValue );
			// Remove the label from the text if it's included in parent
			$text = str_ireplace( array( $label, ':', '：' ), '', $text );
			if ( ! empty( $text ) ) {
				return trim( $text );
			}
		}
		return null;
	}
}
