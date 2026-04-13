<?php
/**
 * Kontentainment Events Tazkarti Parser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Parser_Tazkarti implements KE_Parser_Interface {

	public function get_name() {
		return 'tazkarti';
	}

	/**
	 * Matches URLs like tazkarti.com/#/e/... or tazkarti.com/#/event-details/...
	 */
	public function can_handle( $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		return ( strpos( $host, 'tazkarti.com' ) !== false );
	}

	/**
	 * Parse Tazkarti Data
	 * Uses their public JSON API for maximum reliability as it's a SPA.
	 */
	public function parse( $html, $url ) {
		$generic = new KE_Parser_Generic();
		$result = [
			'fields' => [],
			'warnings' => [],
			'parser_name' => $this->get_name(),
			'source_name' => 'Tazkarti',
			'parser_confidence' => 0
		];

		// 1. Extract Event ID from URL
		// Formats: /#/e/1875 or /#/event-details/1875
		$event_id = 0;
		if ( preg_match( '/\/(?:e|event-details|event)\/(\d+)/i', $url, $matches ) ) {
			$event_id = intval( $matches[1] );
		}

		if ( !$event_id ) {
			// Try to find it in the HTML if it was rendered
			if ( preg_match( '/ID":\s*(\d+)/', $html, $matches ) ) {
				$event_id = intval( $matches[1] );
			}
		}

		if ( ! $event_id ) {
			$result['warnings'][] = 'Could not find a valid Tazkarti Event ID in the URL.';
			return $result;
		}

		// 2. Fetch from Tazkarti API
		$api_url = "https://www.tazkarti.com/bookenter/Entertainment/events/{$event_id}";
		$response = wp_remote_get( $api_url, [ 'timeout' => 15 ] );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$result['warnings'][] = 'Tazkarti API request failed. Attempting fallback scraping.';
			// Fallback to basic HTML (might not work well for SPA but better than nothing)
			return $generic->parse( $html, $url );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data ) || ! is_array( $data ) ) {
			$result['warnings'][] = 'Tazkarti API returned invalid JSON.';
			return $result;
		}

		// 3. Map Fields
		$event_data = $data; // Sometimes nested, but usually direct or in 'data'
		if ( isset( $data['data'] ) ) $event_data = $data['data'];

		$result['fields']['title'] = $event_data['EventName_En'] ?? ( $event_data['EventName_Ar'] ?? '' );
		$result['fields']['description'] = $event_data['EventSummary_En'] ?? ( $event_data['EventSummary_Ar'] ?? '' );
		
		// Dates
		if ( ! empty( $event_data['EventStartDate'] ) ) {
			$result['fields']['event_date'] = date( 'Y-m-d', strtotime( $event_data['EventStartDate'] ) );
		}

		// Venue
		$result['fields']['venue_name'] = $event_data['LocationName_En'] ?? '';
		if ( ! empty( $event_data['Latitude'] ) && ! empty( $event_data['Logitude'] ) ) {
			$result['fields']['latitude'] = $event_data['Latitude'];
			$result['fields']['longitude'] = $event_data['Logitude'];
		}

		// Image
		if ( ! empty( $event_data['EventImg'] ) ) {
			$path = ltrim( $event_data['EventImg'], '/' );
			$result['fields']['image_url'] = "https://www.tazkarti.com/{$path}";
		}

		// Confidence
		if ( ! empty( $result['fields']['title'] ) ) $result['parser_confidence'] += 40;
		if ( ! empty( $result['fields']['event_date'] ) ) $result['parser_confidence'] += 30;
		if ( ! empty( $result['fields']['venue_name'] ) ) $result['parser_confidence'] += 30;

		return $result;
	}
}
