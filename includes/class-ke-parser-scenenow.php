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
		// Start with generic parsing as baseline
		$generic = new KE_Parser_Generic();
		$result = $generic->parse( $html, $url );
		
		$result['parser_name'] = $this->get_name();
		$result['source_name'] = 'SceneNow';
		
		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		$xpath = new DOMXPath( $dom );

		// SceneNow Specific Extractions (Hypothetical typical structures)
		
		// 1. Title (often in h1 or specific header class)
		$title_nodes = $xpath->query( "//h1 | //div[contains(@class, 'event-title')] | //h2[contains(@class, 'title')]" );
		if ( $title_nodes->length > 0 ) {
			$result['fields']['title'] = trim( $title_nodes->item(0)->nodeValue );
		}

		// 2. Date & Time
		// Looking for labels followed by values
		$date_val = $this->get_value_by_label( $xpath, 'Date' );
		if ( $date_val ) {
			$result['fields']['raw_date_text'] = $date_val;
			$timestamp = strtotime( $date_val );
			if ( $timestamp ) {
				$result['fields']['event_date'] = date( 'Y-m-d', $timestamp );
			}
		}

		$time_val = $this->get_value_by_label( $xpath, 'Time' );
		if ( $time_val ) {
			$result['fields']['event_time'] = date( 'H:i', strtotime( $time_val ) );
		}

		// 3. Venue & Location
		$venue_val = $this->get_value_by_label( $xpath, 'Venue' );
		if ( ! $venue_val ) $venue_val = $this->get_value_by_label( $xpath, 'Place' );
		if ( ! $venue_val ) $venue_val = $this->get_value_by_label( $xpath, 'Location' );
		
		if ( $venue_val ) {
			$result['fields']['venue_name'] = $venue_val;
		}

		$address_val = $this->get_value_by_label( $xpath, 'Address' );
		if ( $address_val ) {
			$result['fields']['address'] = $address_val;
		}

		// 4. Contact
		$phone_val = $this->get_value_by_label( $xpath, 'Phone' );
		if ( ! $phone_val ) $phone_val = $this->get_value_by_label( $xpath, 'Telephone' );
		if ( $phone_val ) {
			$result['fields']['phone'] = $phone_val;
		}

		// 5. Category
		$cat_val = $this->get_value_by_label( $xpath, 'Category' );
		if ( $cat_val ) {
			$result['fields']['category'] = $cat_val;
		}

		// Confidence Logic
		$confidence = 40;
		if ( ! empty( $result['fields']['title'] ) ) $confidence += 20;
		if ( ! empty( $result['fields']['event_date'] ) ) $confidence += 20;
		if ( ! empty( $result['fields']['venue_name'] ) ) $confidence += 15;
		if ( ! empty( $result['fields']['description'] ) ) $confidence += 5;

		$result['parser_confidence'] = min( 100, $confidence );

		// Warnings
		if ( empty( $result['fields']['event_date'] ) ) {
			$result['warnings'][] = 'Could not find a clear event date. Please verify manually.';
		}
		if ( empty( $result['fields']['venue_name'] ) ) {
			$result['warnings'][] = 'Venue name missing. Please check the page content.';
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
