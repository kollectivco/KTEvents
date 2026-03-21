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
		// Use generic as baseline (handles JSON-LD)
		$generic = new KE_Parser_Generic();
		$result = $generic->parse( $html, $url );
		
		$result['parser_name'] = $this->get_name();
		$result['source_name'] = 'Cairo Jazz Club';
		
		// Default Venue if none found
		if ( empty( $result['fields']['venue_name'] ) ) {
			$result['fields']['venue_name'] = 'Cairo Jazz Club';
		}

		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		$xpath = new DOMXPath( $dom );

		// 1. Enhanced Title (Fallback)
		if ( empty( $result['fields']['title'] ) || strlen( $result['fields']['title'] ) < 5 ) {
			$title_nodes = $xpath->query( "//div[contains(@class, 'event-title')] | //h2[contains(@class, 'title')]" );
			if ( $title_nodes->length > 0 ) {
				$result['fields']['title'] = $generic->clean_text( $title_nodes->item(0)->nodeValue );
			}
		}

		// 2. Event Date (Fallback)
		if ( empty( $result['fields']['event_date'] ) ) {
			$date_nodes = $xpath->query( "//div[contains(@class, 'event-date')] | //span[contains(@class, 'date')]" );
			if ( $date_nodes->length > 0 ) {
				$raw_val = $date_nodes->item(0)->nodeValue;
				$clean_val = $generic->clean_text( $raw_val );
				
				if ( ! empty( $clean_val ) && ! $generic->is_code_garbage( $clean_val ) ) {
					$result['fields']['raw_date_text'] = $clean_val;
					$timestamp = strtotime( $clean_val );
					if ( $timestamp && $timestamp > 100000 ) {
						$result['fields']['event_date'] = date( 'Y-m-d', $timestamp );
					}
				}
			}
		}

		// 3. Official URL / Booking CTA (Fallback)
		if ( empty( $result['fields']['official_url'] ) ) {
			$cta_nodes = $xpath->query( "//a[contains(text(), 'Book Now') or contains(@class, 'btn-book')]" );
			if ( $cta_nodes->length > 0 ) {
				$cta_url = $cta_nodes->item(0)->getAttribute( 'href' );
				if ( $cta_url && strpos( $cta_url, 'http' ) !== false ) {
					$result['fields']['official_url'] = esc_url_raw( $cta_url );
				}
			}
		}

		// 4. Description (Fallback)
		if ( empty( $result['fields']['description'] ) ) {
			$desc_nodes = $xpath->query( "//div[contains(@class, 'event-description')] | //div[contains(@class, 'description-content')]" );
			if ( $desc_nodes->length > 0 ) {
				$result['fields']['description'] = $generic->clean_text( $desc_nodes->item(0)->nodeValue );
			}
		}

		// Confidence Logic
		$confidence = $result['parser_confidence'] > 50 ? $result['parser_confidence'] : 50;
		if ( ! empty( $result['fields']['event_date'] ) ) $confidence += 20;
		if ( ! empty( $result['fields']['title'] ) ) $confidence += 10;
		
		$result['parser_confidence'] = min( 100, $confidence );

		// Warnings
		if ( empty( $result['fields']['event_date'] ) ) {
			$result['warnings'][] = 'Critical: No event date found. Please review.';
		}

		return $result;
	}
}
