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
			if ( 'application/ld+json' === $script->getAttribute( 'type' ) ) {
				$json = json_decode( $script->nodeValue, true );
				if ( $json ) {
					$this->extract_from_json_ld( $json, $result );
				}
			}
		}

		// Basic normalization
		$result['fields']['title'] = $this->clean_text( $result['fields']['title'] );
		$result['fields']['description'] = $this->clean_text( $result['fields']['description'] );
		
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
			if ( isset( $item['@type'] ) && 'Event' === $item['@type'] ) {
				$result['fields']['title'] = $item['name'] ?? $result['fields']['title'];
				$result['fields']['description'] = $item['description'] ?? $result['fields']['description'];
				$result['fields']['event_date'] = isset( $item['startDate'] ) ? gmdate( 'Y-m-d', strtotime( $item['startDate'] ) ) : $result['fields']['event_date'];
				$result['fields']['event_time'] = isset( $item['startDate'] ) ? gmdate( 'H:i', strtotime( $item['startDate'] ) ) : $result['fields']['event_time'];
				$result['fields']['event_end_date'] = isset( $item['endDate'] ) ? gmdate( 'Y-m-d', strtotime( $item['endDate'] ) ) : $result['fields']['event_end_date'];
				$result['fields']['event_end_time'] = isset( $item['endDate'] ) ? gmdate( 'H:i', strtotime( $item['endDate'] ) ) : $result['fields']['event_end_time'];

				if ( isset( $item['location'] ) ) {
					$result['fields']['venue_name'] = $item['location']['name'] ?? '';
					if ( isset( $item['location']['address'] ) ) {
						if ( is_array( $item['location']['address'] ) ) {
							$result['fields']['address'] = $item['location']['address']['streetAddress'] ?? '';
						} else {
							$result['fields']['address'] = $item['location']['address'];
						}
					}
				}

				if ( isset( $item['image'] ) ) {
					$image = is_array( $item['image'] ) ? $item['image'][0] : $item['image'];
					$result['fields']['image_url'] = is_string( $image ) ? $image : ( $image['url'] ?? '' );
				}

				$result['parser_confidence'] = 80; // High confidence if schema found
			}
		}
	}

	private function clean_text( $text ) {
		if ( ! is_string( $text ) ) return '';
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
		$text = preg_replace( '/\s+/', ' ', $text );
		return trim( $text );
	}
}
