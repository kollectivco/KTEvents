<?php
/**
 * Kontentainment Events Elementor Base Widget - Standardized
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class KE_Widget_Base extends \Elementor\Widget_Base {

	public function get_categories() {
		$cats = [ 'ke-events' ];
		if ( did_action( 'foxiz_loaded' ) || did_action( 'foxiz_core_loaded' ) ) {
			$cats[] = 'foxiz';
			$cats[] = 'foxiz_flex';
		}
		return $cats;
	}

	/**
	 * Query Section - Standard Name
	 */
	protected function register_query_section( $args = [] ) {
		$this->register_advanced_query_section( $args );
	}

	/**
	 * Advanced Query Section
	 */
	protected function register_advanced_query_section( $args = [] ) {
		$this->start_controls_section(
			'section_query',
			[
				'label' => 'Query Settings',
			]
		);

		$this->add_control(
			'query_mode',
			[
				'label' => 'Query Mode',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'standard',
				'options' => KE_Elementor_Options::get_query_modes(),
				'description' => KE_Elementor_Descriptions::get('query_mode'),
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => 'Number of Posts',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
			]
		);

		// Standard Mode Filters
		$this->add_control(
			'offset',
			[
				'label' => 'Offset',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 0,
				'description' => KE_Elementor_Descriptions::get('offset'),
				'condition' => [ 'query_mode' => 'standard' ],
			]
		);

		$this->add_control(
			'include_ids',
			[
				'label' => 'Include IDs',
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '1, 2, 3',
				'label_block' => true,
				'condition' => [ 'query_mode' => 'standard' ],
			]
		);

		$this->add_control(
			'exclude_ids',
			[
				'label' => 'Exclude IDs',
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '4, 5, 6',
				'label_block' => true,
				'condition' => [ 'query_mode' => 'standard' ],
			]
		);

		$this->add_control(
			'orderby_custom',
			[
				'label' => 'Order By',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'upcoming',
				'options' => KE_Elementor_Options::get_sort_options(),
				'condition' => [ 'query_mode' => [ 'standard', 'advanced' ] ],
			]
		);

		$this->add_control(
			'status',
			[
				'label' => 'Event Status',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'all_exc_past',
				'options' => [
					'' => 'All',
					'all_exc_past' => 'All Except Past',
					'upcoming' => 'Upcoming Only',
					'past' => 'Past Only',
				],
				'condition' => [ 'query_mode' => 'standard' ],
			]
		);

		$this->add_control(
			'event_category',
			[
				'label' => 'Filter Category',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_tax_options( 'event_category' ),
				'multiple' => true,
				'condition' => [ 'query_mode' => 'standard' ],
			]
		);

		$this->add_control(
			'event_city',
			[
				'label' => 'Filter City',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_tax_options( 'event_city' ),
				'multiple' => true,
				'condition' => [ 'query_mode' => 'standard' ],
			]
		);

		$this->add_control(
			'event_area',
			[
				'label' => 'Filter Area',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_tax_options( 'event_area' ),
				'multiple' => true,
				'condition' => [ 'query_mode' => 'standard' ],
			]
		);

		$this->add_control(
			'venue_id',
			[
				'label' => 'Filter by Venue',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_post_options( 'venue' ),
				'multiple' => false,
				'condition' => [ 'query_mode' => 'standard' ],
			]
		);

		$this->add_control(
			'featured',
			[
				'label' => 'Featured Only',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'query_mode' => 'standard' ],
			]
		);

		$this->add_control(
			'editor_pick',
			[
				'label' => 'Editor Pick / Recommended',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'query_mode' => 'standard' ],
			]
		);

		$this->add_control(
			'unique_post',
			[
				'label' => 'Unique Posts',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'description' => KE_Elementor_Descriptions::get('unique_post'),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Pagination Section
	 */
	protected function register_pagination_section() {
		$this->start_controls_section(
			'section_pagination',
			[
				'label' => 'Ajax Pagination',
			]
		);

		$this->add_control(
			'pagination',
			[
				'label' => 'Type',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => 'Disable',
					'load_more' => 'Load More',
					'next_prev' => 'Next/Prev',
					'infinite' => 'Infinite Scroll',
				],
			]
		);

		$this->add_control(
			'pagination_style',
			[
				'label' => 'Button Style',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'standard',
				'options' => KE_Elementor_Options::get_pagination_styles(),
				'condition' => [ 'pagination' => 'load_more' ],
			]
		);

		$this->add_control(
			'pagination_label',
			[
				'label' => 'Label Text',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'LOAD MORE',
				'condition' => [ 'pagination' => 'load_more' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Carousel Settings Section
	 */
	protected function register_carousel_section() {
		$this->start_controls_section(
			'section_carousel',
			[
				'label' => 'Carousel Settings',
			]
		);

		$this->add_control(
			'carousel',
			[
				'label' => 'Enable Carousel Mode',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'description' => 'Converts the layout into a swipeable carousel.',
			]
		);

		$this->add_responsive_control(
			'carousel_items',
			[
				'label' => 'Slides Per View',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'condition' => [ 'carousel' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_gap',
			[
				'label' => 'Gap (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 24,
				'condition' => [ 'carousel' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_autoplay',
			[
				'label' => 'Autoplay',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'carousel' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_autoplay_speed',
			[
				'label' => 'Autoplay Speed (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 5000,
				'condition' => [ 'carousel' => 'yes', 'carousel_autoplay' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_speed',
			[
				'label' => 'Transition Speed (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 400,
				'condition' => [ 'carousel' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_loop',
			[
				'label' => 'Infinite Loop',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [ 'carousel' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_center',
			[
				'label' => 'Center Mode',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'carousel' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_pause_hover',
			[
				'label' => 'Pause on Hover',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [ 'carousel' => 'yes', 'carousel_autoplay' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_arrows',
			[
				'label' => 'Show Navigation Arrows',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [ 'carousel' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_hide_arrows_mobile',
			[
				'label' => 'Hide Arrows on Mobile',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [ 'carousel' => 'yes', 'carousel_arrows' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_dots',
			[
				'label' => 'Show Pagination Dots',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'carousel' => 'yes' ],
			]
		);

		$this->add_control(
			'carousel_free_scroll',
			[
				'label' => 'Free Scroll Mode',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'description' => 'Scroll freely without snapping to slides.',
				'condition' => [ 'carousel' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Alias for Entry Meta Styling
	 */
	protected function register_entry_meta_controls() {
		$this->register_entry_meta_styling();
	}

	/**
	 * Entry Meta Styling
	 */
	protected function register_entry_meta_styling() {
		$this->start_controls_section(
			'section_entry_meta_style',
			[
				'label' => 'Entry Meta / Labels',
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'meta_venue', [ 'label' => 'Show Venue', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]
		);
		$this->add_control(
			'meta_date', [ 'label' => 'Show Date', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]
		);
		$this->add_control(
			'meta_time', [ 'label' => 'Show Time', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]
		);

		$this->add_control(
			'meta_color', [ 'label' => 'Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .ke-card-meta' => 'color: {{VALUE}};' ] ]
		);
		
		$this->add_control(
			'dark_meta_color', [ 'label' => 'Dark Meta Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '.foxiz-dark-mode {{WRAPPER}} .ke-card-meta' => 'color: {{VALUE}};' ] ]
		);

		$this->end_controls_section();
	}

	/**
	 * Box Selection Style
	 */
	protected function register_box_style_controls() {
		$this->start_controls_section(
			'section_box_style',
			[
				'label' => 'Box Styling',
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'is_boxed',
			[
				'label' => 'Enable Boxed Style',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'item_bg',
			[
				'label' => 'Item Background',
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .ke-card' => 'background-color: {{VALUE}};' ],
				'condition' => [ 'is_boxed' => 'yes' ],
			]
		);

		$this->add_control(
			'item_padding',
			[
				'label' => 'Item Padding',
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [ '{{WRAPPER}} .ke-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
				'condition' => [ 'is_boxed' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Dark Mode Styling
	 */
	protected function register_dark_mode_overrides() {
		$this->start_controls_section(
			'section_dark_mode',
			[
				'label' => 'Dark Mode Overrides',
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'dark_item_bg',
			[
				'label' => 'Dark Item Background',
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '.foxiz-dark-mode {{WRAPPER}} .ke-card' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'dark_title_color',
			[
				'label' => 'Dark Title Color',
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '.foxiz-dark-mode {{WRAPPER}} .ke-card-title a' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'dark_border_color',
			[
				'label' => 'Dark Border Color',
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '.foxiz-dark-mode {{WRAPPER}} .ke-card' => 'border-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Shared Helpers
	 */
	protected function get_tax_options( $tax ) {
		$terms = get_terms( [ 'taxonomy' => $tax, 'hide_empty' => false ] );
		$options = [];
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) { $options[ $term->term_id ] = $term->name; }
		}
		return $options;
	}

	protected function get_post_options( $cpt ) {
		$posts = get_posts( [ 'post_type' => $cpt, 'numberposts' => -1, 'post_status' => 'publish' ] );
		$options = [];
		foreach ( $posts as $p ) { $options[ $p->ID ] = $p->post_title; }
		return $options;
	}
}
