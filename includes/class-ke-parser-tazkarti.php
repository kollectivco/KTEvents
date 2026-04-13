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
		if ( preg_match( '/\/(?:e|event-details|event|entertainment\/events)\/(\d+)/i', $url, $matches ) ) {
			$event_id = intval( $matches[1] );
		}

		if ( ! $event_id ) {
			$result['warnings'][] = 'Could not find a valid Tazkarti Event ID in the URL. Please ensure it is a single event URL.';
			return $result;
		}

		// 2. Fetch from Tazkarti API with REQUIRED headers
		$api_url = "https://www.tazkarti.com/bookenter/Entertainment/events/{$event_id}";
		
		$args = [
			'timeout' => 15,
			'headers' => [
				'Referer'    => 'https://www.tazkarti.com/',
				'Origin'     => 'https://www.tazkarti.com',
				'Accept'     => 'application/json, text/plain, */*',
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
			]
		];

		$response = wp_remote_get( $api_url, $args );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$code = wp_remote_retrieve_response_code( $response );
			$result['warnings'][] = "Tazkarti API request failed (Code: $code). Access might be restricted.";
			// Fallback to basic HTML
			return $generic->parse( $html, $url );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		if ( empty( $data ) || ! is_array( $data ) ) {
			$result['warnings'][] = 'Tazkarti API returned invalid JSON.';
			return $result;
		}

		// 3. Map Fields
		// Tazkarti API returns the object directly or wrapped in data
		$event_data = $data;
		if ( isset( $data['data'] ) ) {
			$event_data = $data['data'];
		}

		$result['fields']['title'] = $event_data['EventName_En'] ?? ( $event_data['EventName_Ar'] ?? '' );
		$result['fields']['description'] = $event_data['EventSummary_En'] ?? ( $event_data['EventSummary_Ar'] ?? '' );
		
		// Dates
		if ( ! empty( $event_data['EventStartDate'] ) ) {
			$result['fields']['event_date'] = date( 'Y-m-d', strtotime( $event_data['EventStartDate'] ) );
		}
		
		if ( ! empty( $event_data['EventStartTime'] ) ) {
			$result['fields']['event_time'] = $event_data['EventStartTime'];
		}

		// Venue
		$result['fields']['venue_name'] = $event_data['LocationName_En'] ?? ( $event_data['LocationName_Ar'] ?? '' );
		if ( ! empty( $event_data['Latitude'] ) && ! empty( $event_data['Logitude'] ) ) {
			$result['fields']['latitude'] = $event_data['Latitude'];
			$result['fields']['longitude'] = $event_data['Logitude'];
		}

		// Image
		if ( ! empty( $event_data['EventImg'] ) ) {
			$path = ltrim( $event_data['EventImg'], '/' );
			$result['fields']['image_url'] = "https://www.tazkarti.com/{$path}";
		}

		// Price
		if ( ! empty( $event_data['MiniClassPrice'] ) ) {
			$result['fields']['price'] = floatval( $event_data['MiniClassPrice'] );
		}

		// Confidence
		if ( ! empty( $result['fields']['title'] ) ) $result['parser_confidence'] += 40;
		if ( ! empty( $result['fields']['event_date'] ) ) $result['parser_confidence'] += 30;
		if ( ! empty( $result['fields']['venue_name'] ) ) $result['parser_confidence'] += 30;

		return $result;
	}
}
