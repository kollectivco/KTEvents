<?php
/**
 * Kontentainment Events TicketsMarche Parser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Parser_TicketsMarche implements KE_Parser_Interface {

	public function get_name() {
		return 'ticketsmarche';
	}

	/**
	 * Matches URLs like ticketsmarche.com/event/...
	 */
	public function can_handle( $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		return ( strpos( $host, 'ticketsmarche.com' ) !== false );
	}

	/**
	 * Parse TicketsMarche HTML
	 */
	public function parse( $html, $url ) {
		$generic = new KE_Parser_Generic();
		$result = $generic->parse( $html, $url );
		
		$result['parser_name'] = $this->get_name();
		$result['source_name'] = 'TicketsMarche';
		
		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		$xpath = new DOMXPath( $dom );

		// 1. Title
		$title_nodes = $xpath->query( "//h1" );
		if ( $title_nodes->length > 0 ) {
			$result['fields']['title'] = $generic->clean_text( $title_nodes->item(0)->nodeValue );
		}

		// 2. Date & Time (h1 + p)
		// Usually formatted as "May 01 | 02:00 PM"
		$dateTimeNode = $xpath->query( "//h1/following-sibling::p[1]" );
		if ( $dateTimeNode->length > 0 ) {
			$rawText = trim( $dateTimeNode->item(0)->nodeValue );
			$parts = explode( '|', $rawText );
			
			// Date part
			$date_str = trim( $parts[0] );
			if ( $date_str ) {
				$timestamp = strtotime( $date_str );
				if ( $timestamp ) {
					$result['fields']['event_date'] = date( 'Y-m-d', $timestamp );
				}
			}

			// Time part
			if ( isset( $parts[1] ) ) {
				$time_str = trim( $parts[1] );
				$extracted = $generic->extract_times_from_string( $time_str );
				$result['fields']['event_time'] = $extracted['start'] ?: $time_str;
			}
		}

		// 3. Venue (h1 + p + p)
		$venueNode = $xpath->query( "//h1/following-sibling::p[2]" );
		if ( $venueNode->length > 0 ) {
			$venue_raw = trim( $venueNode->item(0)->nodeValue );
			// Often contains "Venue, City"
			$venue_parts = explode( ',', $venue_raw );
			$result['fields']['venue_name'] = trim( $venue_parts[0] );
			if ( isset( $venue_parts[1] ) ) {
				$result['fields']['address'] = trim( $venue_raw );
			}
		}

		// 4. Featured Image
		$imageNode = $xpath->query( "//img[contains(@class, 'event_featured_image_bg')]" );
		if ( $imageNode->length > 0 ) {
			$result['fields']['image_url'] = $imageNode->item(0)->getAttribute( 'src' );
		}

		// 5. Description (.english and .arabic)
		$desc_nodes = $xpath->query( "//*[contains(@class, 'english')] | //*[contains(@class, 'arabic')]" );
		if ( $desc_nodes->length > 0 ) {
			$descriptions = [];
			foreach ( $desc_nodes as $node ) {
				$text = $generic->clean_text( $node->nodeValue );
				if ( $text && strlen($text) > 20 ) {
					$descriptions[] = $text;
				}
			}
			if ( ! empty( $descriptions ) ) {
				$result['fields']['description'] = implode( "\n\n", array_unique( $descriptions ) );
			}
		}

		// 6. Price
		$price_nodes = $xpath->query( "//div[contains(@class, 'row-tickets')]//span[contains(text(), 'EGP')]" );
		if ( $price_nodes->length > 0 ) {
			$prices = [];
			foreach ( $price_nodes as $node ) {
				if ( preg_match( '/([0-9\.]+)/', $node->nodeValue, $matches ) ) {
					$prices[] = floatval( $matches[1] );
				}
			}
			if ( ! empty( $prices ) ) {
				$result['fields']['price'] = min( $prices );
			}
		}

		// Validation
		if ( ! empty( $result['fields']['event_date'] ) ) $result['parser_confidence'] += 30;
		if ( ! empty( $result['fields']['venue_name'] ) ) $result['parser_confidence'] += 30;

		return $result;
	}
}
