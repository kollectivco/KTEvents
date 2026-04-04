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
				'label' => 'Cards per view (Slider mode)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1.2,
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
				'default' => 4,
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
				'label' => 'Filter Category',
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

		// 3. Card Style / Image
		$this->start_controls_section( 'section_image_style', [ 'label' => 'Card Presentation', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_control(
			'card_style',
			[
				'label' => 'Card Style',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'minimal',
				'options' => [
					'minimal' => 'Minimal (No border)',
					'boxed'   => 'Boxed (Subtle border/shadow)',
					'dark'    => 'Dark Card (High Impact)',
				],
			]
		);

		$this->add_control(
			'image_ratio',
			[
				'label' => 'Image Ratio',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '4-3',
				'options' => KE_Elementor_Options::get_image_ratios(),
			]
		);

		$this->end_controls_section();

		// 4. Meta Toggles
		$this->start_controls_section( 'section_entry_meta', [ 'label' => 'Card Metadata', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		$this->add_control( 'meta_venue', [ 'label' => 'Show Venue', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'meta_date', [ 'label' => 'Show Date', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'meta_time', [ 'label' => 'Show Time', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => '' ] );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Map to query variables
		$settings['is_widget'] = true;
		$settings['query_mode'] = 'standard';
		
		// Determine layout/carousel
		if ( in_array($settings['mobile_layout'], ['horizontal_slider', 'mini_carousel']) ) {
			$settings['carousel'] = 'yes';
			$settings['carousel_items'] = $settings['mobile_cards_per_view'];
			$settings['carousel_items_mobile'] = $settings['mobile_cards_per_view'];
			$settings['carousel_items_tablet'] = $settings['mobile_cards_per_view']; // If forcing visible
			$settings['carousel_gap'] = $settings['mobile_gap'];
			$settings['carousel_arrows'] = ''; // Touch-friendly, no arrows for webview
			$settings['carousel_dots'] = '';
			$settings['carousel_loop'] = ''; // Native feel without layout jumps
			$settings['layout_preset'] = ($settings['mobile_layout'] === 'mini_carousel') ? 'editorial_grid' : 'minimal_grid';
		} else {
			$settings['carousel'] = '';
			$settings['layout_preset'] = 'list_2'; // Stacked mapping
			$settings['columns'] = 1;
			$settings['columns_mobile'] = 1;
			$settings['columns_tablet'] = 1;
		}

		// Enforce gaps natively if we have a gap option
		if ( $settings['mobile_spacing'] === 'compact' ) {
            $settings['gap_preset'] = 'small';
        } else {
            $settings['gap_preset'] = 'default';
        }

		$query = KE_Query::get_instance()->get_events( $settings );

		$classes = [
			'ke-elementor-widget',
			'ke-events-mobile-webview'
		];

		if ( $settings['webview_mode'] === 'mobile_only' ) {
			// Elementor native responsive hidden classes
			$classes[] = 'elementor-hidden-desktop';
			$classes[] = 'elementor-hidden-tablet';
            $classes[] = 'ke-mobile-only-enforced';
		}

		$classes[] = 'ke-card-style-' . esc_attr($settings['card_style']);

		echo '<div class="' . implode(' ', $classes) . '">';
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
		echo '</div>';
	}
}
