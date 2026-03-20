<?php
/**
 * Kontentainment Events Cairo Jazz Club Parser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Parser_CairoJazzClub implements KE_Parser_Interface {

	public function get_name() {
		return 'cairo_jazz_club';
	}

	/**
	 * Matches URLs like www.cairojazzclub.com/events/view-event/...
	 */
	public function can_handle( $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		$path = wp_parse_url( $url, PHP_URL_PATH );
		
		return ( strpos( $host, 'cairojazzclub.com' ) !== false && strpos( $path, '/events/view-event/' ) !== false );
	}

	/**
	 * Parse Cairo Jazz Club HTML
	 */
	public function parse( $html, $url ) {
		// Use generic as baseline
		$generic = new KE_Parser_Generic();
		$result = $generic->parse( $html, $url );
		
		$result['parser_name'] = $this->get_name();
		$result['source_name'] = 'Cairo Jazz Club';
		
		// 1. Default Venue
		$result['fields']['venue_name'] = 'Cairo Jazz Club';

		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		$xpath = new DOMXPath( $dom );

		// Cairo Jazz Club Specific Extractions
		
		// 1. Title (often in a specific event-title class or h2)
		$title_nodes = $xpath->query( "//div[contains(@class, 'event-title')] | //h2[contains(@class, 'title')]" );
		if ( $title_nodes->length > 0 ) {
			$result['fields']['title'] = trim( $title_nodes->item(0)->nodeValue );
		}

		// 2. Event Date
		$date_nodes = $xpath->query( "//div[contains(@class, 'event-date')] | //span[contains(@class, 'date')]" );
		if ( $date_nodes->length > 0 ) {
			$date_text = trim( $date_nodes->item(0)->nodeValue );
			$result['fields']['raw_date_text'] = $date_text;
			$timestamp = strtotime( $date_text );
			if ( $timestamp ) {
				$result['fields']['event_date'] = date( 'Y-m-d', $timestamp );
			}
		}

		// 3. Official URL / Booking CTA
		$cta_nodes = $xpath->query( "//a[contains(text(), 'Book Now') or contains(@class, 'btn-book')]" );
		if ( $cta_nodes->length > 0 ) {
			$cta_url = $cta_nodes->item(0)->getAttribute( 'href' );
			if ( $cta_url && strpos( $cta_url, 'http' ) !== false ) {
				$result['fields']['official_url'] = $cta_url;
			}
		}

		// 4. Description (Look for description text or modal body)
		$desc_nodes = $xpath->query( "//div[contains(@class, 'event-description')] | //div[contains(@class, 'description-content')]" );
		if ( $desc_nodes->length > 0 ) {
			$result['fields']['description'] = trim( $desc_nodes->item(0)->nodeValue );
		}

		// Confidence Logic
		$confidence = 50; // Higher baseline for source-specific
		if ( ! empty( $result['fields']['title'] ) ) $confidence += 20;
		if ( ! empty( $result['fields']['event_date'] ) ) $confidence += 20;
		if ( ! empty( $result['fields']['image_url'] ) ) $confidence += 10;
		
		$result['parser_confidence'] = min( 100, $confidence );

		// Warnings
		if ( empty( $result['fields']['event_date'] ) ) {
			$result['warnings'][] = 'No event date found on page. Please review.';
		}

		return $result;
	}
}
