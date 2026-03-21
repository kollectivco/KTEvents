<?php
/**
 * Kontentainment Events Generic Parser (Fallback)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Parser_Generic implements KE_Parser_Interface {

	public function get_name() {
		return 'generic';
	}

	public function can_handle( $url ) {
		return true; // Fallback for all URLs
	}

	/**
	 * Parse HTML and extract metadata
	 */
	public function parse( $html, $url ) {
		$result = array(
			'success'           => false,
			'parser_name'       => $this->get_name(),
			'parser_confidence' => 0,
			'source_url'        => $url,
			'canonical_url'     => '',
			'source_name'       => '',
			'fields'            => array(
				'title'          => '',
				'description'    => '',
				'image_url'      => '',
				'event_date'     => '',
				'event_end_date' => '',
				'event_time'     => '',
				'event_end_time' => '',
				'venue_name'     => '',
				'organizer_name' => '',
				'address'        => '',
				'phone'          => '',
				'official_url'   => '',
				'category'       => '',
				'city'           => '',
				'area'           => '',
				'raw_date_text'  => '',
				'raw_location_text' => ''
			),
			'warnings'          => array(),
			'errors'            => array(),
		);

		if ( empty( $html ) ) {
			$result['errors'][] = 'Empty HTML provided.';
			return $result;
		}

		// Use PHP's built-in DOMDocument for basic parsing
		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		$xpath = new DOMXPath( $dom );

		// 1. Extract Title
		$title_node = $dom->getElementsByTagName( 'title' )->item( 0 );
		if ( $title_node ) {
			$result['fields']['title'] = trim( $title_node->nodeValue );
		}

		// 2. Extract Open Graph / Twitter Meta Tags
		$meta_tags = $dom->getElementsByTagName( 'meta' );
		foreach ( $meta_tags as $meta ) {
			$property = $meta->getAttribute( 'property' );
			$name     = $meta->getAttribute( 'name' );
			$content  = $meta->getAttribute( 'content' );

			// Title
			if ( 'og:title' === $property || 'twitter:title' === $name ) {
				$result['fields']['title'] = $content ?: $result['fields']['title'];
			}

			// Description
			if ( 'og:description' === $property || 'twitter:description' === $name || 'description' === $name ) {
				$result['fields']['description'] = $content ?: $result['fields']['description'];
			}

			// Image
			if ( 'og:image' === $property || 'twitter:image' === $name ) {
				$result['fields']['image_url'] = $content ?: $result['fields']['image_url'];
			}

			// Canonical URL
			if ( 'og:url' === $property ) {
				$result['canonical_url'] = $content ?: $result['canonical_url'];
			}

			// Site name
			if ( 'og:site_name' === $property ) {
				$result['source_name'] = $content ?: $result['source_name'];
			}
		}

		// 3. Extract JSON-LD (Schema.org)
		$scripts = $dom->getElementsByTagName( 'script' );
		foreach ( $scripts as $script ) {
			if ( strpos( $script->getAttribute( 'type' ), 'application/ld+json' ) !== false ) {
				$script_content = $script->nodeValue;
				if ( ! empty( $script_content ) ) {
					$json = json_decode( $script_content, true );
					if ( $json ) {
						$this->extract_from_json_ld( $json, $result );
					}
				}
			}
		}

		// Basic normalization
		$result['fields']['title'] = $this->clean_text( $result['fields']['title'] );
		$result['fields']['description'] = $this->clean_text( $result['fields']['description'] );
		$result['fields']['raw_date_text'] = $this->clean_text( $result['fields']['raw_date_text'] );
		$result['fields']['venue_name'] = $this->clean_text( $result['fields']['venue_name'] );
		
		if ( ! empty( $result['fields']['title'] ) ) {
			$result['success'] = true;
			$result['parser_confidence'] = 40; // Basic confidence for generic tags
		}

		return $result;
	}

	/**
	 * Attempt to extract from JSON-LD
	 */
	private function extract_from_json_ld( $json, &$result ) {
		// Event or Event Graph context
		$items = isset( $json['@type'] ) ? array( $json ) : ( isset( $json['@graph'] ) ? $json['@graph'] : array() );

		foreach ( $items as $item ) {
			if ( isset( $item['@type'] ) && ( $item['@type'] === 'Event' || $item['@type'] === 'MusicEvent' || $item['@type'] === 'Festival' ) ) {
				$result['fields']['title'] = $this->clean_text( $item['name'] ?? $result['fields']['title'] );
				$result['fields']['description'] = $this->clean_text( $item['description'] ?? $result['fields']['description'] );
				
				if ( ! empty( $item['startDate'] ) ) {
					$ts = strtotime( $item['startDate'] );
					if ( $ts && $ts > 100000 ) { // Avoid 1970
						$result['fields']['event_date'] = date( 'Y-m-d', $ts );
						$result['fields']['event_time'] = date( 'H:i', $ts );
						
						// Provide a clean raw string baseline from the structured data
						if ( empty( $result['fields']['raw_date_text'] ) ) {
							$result['fields']['raw_date_text'] = date( 'l, j F Y', $ts ) . ' at ' . date( 'g:i A', $ts );
						}
					}
				}

				if ( ! empty( $item['endDate'] ) ) {
					$ts = strtotime( $item['endDate'] );
					if ( $ts && $ts > 100000 ) {
						$result['fields']['event_end_date'] = date( 'Y-m-d', $ts );
						$result['fields']['event_end_time'] = date( 'H:i', $ts );
					}
				}

				if ( isset( $item['location'] ) ) {
					$result['fields']['venue_name'] = $this->clean_text( $item['location']['name'] ?? $result['fields']['venue_name'] );
					$address_obj = $item['location']['address'] ?? null;
					if ( $address_obj ) {
						if ( is_array( $address_obj ) ) {
							$parts = [];
							if ( ! empty( $address_obj['streetAddress'] ) ) $parts[] = $address_obj['streetAddress'];
							if ( ! empty( $address_obj['addressLocality'] ) ) $parts[] = $address_obj['addressLocality'];
							if ( ! empty( $address_obj['addressRegion'] ) ) $parts[] = $address_obj['addressRegion'];
							$result['fields']['address'] = implode( ', ', $parts );
						} else {
							$result['fields']['address'] = $this->clean_text( $address_obj );
						}
					}
				}

				if ( isset( $item['organizer'] ) ) {
					$result['fields']['organizer_name'] = $this->clean_text( $item['organizer']['name'] ?? $result['fields']['organizer_name'] );
				}

				if ( isset( $item['image'] ) ) {
					$image = is_array( $item['image'] ) ? $item['image'][0] : $item['image'];
					$result['fields']['image_url'] = is_string( $image ) ? $image : ( $image['url'] ?? '' );
				}

				if ( isset( $item['url'] ) ) {
					$result['fields']['official_url'] = esc_url_raw( $item['url'] );
				}

				$result['parser_confidence'] = 90; // High confidence if structured schema found
			}
		}
	}

	public function clean_text( $text ) {
		if ( ! is_string( $text ) ) return '';
		
		// 1. Force strip script and style blocks
		$text = preg_replace( array( '/<script\b[^>]*>([\s\S]*?)<\/script>/i', '/<style\b[^>]*>([\s\S]*?)<\/style>/i' ), '', $text );
		
		// 2. Strip standard HTML tags
		$text = wp_strip_all_tags( $text );
		
		// 3. Decode entities
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );

		// 4. Reject if it looks like code garbage
		if ( $this->is_code_garbage( $text ) ) {
			return '';
		}

		// 5. Normalize whitespace
		$text = preg_replace( '/\s+/', ' ', $text );
		
		return trim( $text );
	}

	/**
	 * Detect if a string looks like JavaScript or JSON garbage
	 */
	public function is_code_garbage( $text ) {
		if ( empty( $text ) ) return false;
		
		$patterns = array(
			'/\{[\s\S]*?\}/',             // JSON blocks
			'/\(function\b/',             // Anonymous functions
			'/\.ready\(/',                // jQuery ready
			'/\.on\(/',                   // jQuery on
			'/\.click\(/',                // jQuery click
			'/window\./',                 // Window object
			'/document\./',               // Document object
			'/console\.log/',             // Console
			'/var\s+[a-z0-9_]+\s*=/i',    // Var declaration
			'/const\s+[a-z0-9_]+\s*=/i',  // Const declaration
			'/let\s+[a-z0-9_]+\s*=/i',    // Let declaration
			'/\$[^\s]+\(/',               // jQuery $ selector
			'/fbq\(/',                    // Facebook pixel
			'/ga\(/',                     // Google analytics
			'/gtag\(/',                   // Gtag
			'/import\s+\{/',              // ES imports
			'/module\.exports/',          // Node exports
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $text ) ) {
				return true;
			}
		}

		// Length check: dates are rarely > 200 chars
		if ( strlen( $text ) > 500 ) {
			return true;
		}

		return false;
	}
}
