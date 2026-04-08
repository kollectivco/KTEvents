<?php
/**
 * KE Events Mobile Webview Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Events_Mobile_Webview extends KE_Widget_Base {

	public function get_name() { return 'ke_events_mobile_webview'; }
	public function get_title() { return 'Events App Feed'; }
	public function get_icon() { return 'eicon-device-mobile'; }

	protected function register_controls() {
		// 1. Mobile App Settings
		$this->start_controls_section( 'section_mobile_settings', [ 'label' => 'App Feed Layout' ] );

		$this->add_control(
			'webview_mode',
			[
				'label' => 'Webview Visibility',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'mobile_only',
				'options' => [
					'mobile_only' => 'Mobile Only (Hidden on Desktop/Tablet)',
					'always_show' => 'Always Show (Preview/Testing)',
				],
			]
		);

		$this->add_control(
			'mobile_layout',
			[
				'label' => 'Feed Format',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'horizontal_slider',
				'options' => [
					'horizontal_slider' => 'Horizontal Card Slider',
					'stacked_cards'     => 'Compact Stacked Cards',
					'mini_carousel'     => 'Mini Editorial Carousel',
				],
			]
		);

		$this->add_control(
			'mobile_spacing',
			[
				'label' => 'Spacing',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'compact',
				'options' => [
					'compact'     => 'Compact (App-like narrow)',
					'comfortable' => 'Comfortable',
				],
			]
		);

		$this->add_control(
			'mobile_gap',
			[
				'label' => 'Item Gap (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 16,
			]
		);

		$this->add_control(
			'mobile_cards_per_view',
			[
				'label' => 'Cards per view (Mobile)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 2,
				'min' => 1,
				'max' => 5,
				'step' => 0.1,
				'condition' => [ 'mobile_layout' => [ 'horizontal_slider', 'mini_carousel' ] ],
			]
		);

		$this->end_controls_section();

		// 2. Query Controls (Simplified)
		$this->start_controls_section( 'section_query', [ 'label' => 'Query Settings' ] );
		
		$this->add_control(
			'posts_per_page',
			[
				'label' => 'Item Count',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
			]
		);

		$this->add_control(
			'orderby_custom',
			[
				'label' => 'Order By',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'upcoming',
				'options' => KE_Elementor_Options::get_sort_options(),
			]
		);

		$this->add_control(
			'event_category',
			[
				'label' => 'Categories',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_tax_options( 'event_category' ),
				'multiple' => true,
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
			]
		);

		$this->add_control(
			'featured',
			[
				'label' => 'Featured Only',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->end_controls_section();

		// 3. Style / Image - Match Events Grid Carousel
		$this->start_controls_section( 'section_image_style', [ 'label' => 'Featured Image', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_control(
			'image_ratio',
			[
				'label' => 'Ratio',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '16-9',
				'options' => KE_Elementor_Options::get_image_ratios(),
			]
		);

		$this->add_control(
			'image_radius',
			[ 'label' => 'Rounded Corners', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => [ '{{WRAPPER}} .ke-card-image img' => 'border-radius: {{SIZE}}{{UNIT}};' ] ]
		);

		$this->end_controls_section();

		// 4. Style: Dark Mode & Boxed (Standardized)
		$this->register_box_style_controls();
		$this->register_dark_mode_overrides();

		// 5. Card Metadata
		$this->register_entry_meta_styling();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Map to query variables
		$settings['is_widget'] = true;
		$settings['query_mode'] = 'standard';
		
		// ALWAYS use "classic" to match Events Grid Carousel design system
		$settings['layout_preset'] = 'classic';

		// Determine layout/carousel
		if ( in_array($settings['mobile_layout'], ['horizontal_slider', 'mini_carousel']) ) {
			$settings['carousel'] = 'yes';
			
			// Setup for 2 cards side-by-side on mobile
			$per_view = $settings['mobile_cards_per_view'] ?: 2;
			
			$settings['carousel_items'] = $per_view;
			$settings['carousel_items_mobile'] = $per_view;
			$settings['carousel_items_tablet'] = $per_view;
			
			$settings['carousel_gap'] = $settings['mobile_gap'];
			$settings['carousel_arrows'] = ''; // Touch-friendly
			$settings['carousel_dots'] = '';
			$settings['carousel_loop'] = ''; 
		} else {
			$settings['carousel'] = '';
			$settings['columns'] = 2;
			$settings['columns_mobile'] = 2;
			$settings['columns_tablet'] = 2;
		}

		// Spacing integration
		if ( $settings['mobile_spacing'] === 'compact' ) {
            $settings['gap'] = 'small';
            $settings['gap_preset'] = 'small';
        } else {
            $settings['gap'] = 'medium';
            $settings['gap_preset'] = 'default';
        }

		$query = KE_Query::get_instance()->get_events( $settings );

		$classes = [
			'ke-elementor-widget',
			'ke-events-mobile-webview',
			'ke-mobile-2-up' // Identifier for CSS refinements
		];

		if ( $settings['webview_mode'] === 'mobile_only' ) {
			$classes[] = 'elementor-hidden-desktop';
			$classes[] = 'elementor-hidden-tablet';
            $classes[] = 'ke-mobile-only-enforced';
		}

		echo '<div class="' . implode(' ', $classes) . '">';
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
		echo '</div>';
	}
}
