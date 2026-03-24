<?php
/**
 * KE Events Grid Carousel Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Events_Grid_Carousel extends KE_Widget_Base {

	public function get_name() { return 'ke_events_grid_carousel'; }
	public function get_title() { return 'Events Grid Carousel'; }
	public function get_icon() { return 'eicon-carousel'; }

	protected function register_controls() {
		// 1. Layout Preset Family
		$this->start_controls_section(
			'section_layout', [ 'label' => 'Layout Settings' ]
		);

		$this->add_control(
			'layout_preset',
			[
				'label' => 'Display Preset',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => [
					'classic'        => 'Classic Grid',
					'grid_1'         => 'Grid 1 (Clean)',
					'grid_2'         => 'Grid 2 (Boxed)',
					'minimal_grid'   => 'Minimal Grid',
					'editorial_grid' => 'Editorial Grid',
					'flex_overlay'   => 'Flex Overlay',
					'highlight'      => 'Highlight / Hero',
				],
			]
		);

		$this->end_controls_section();

		// 2. Advanced Query (from Base)
		$this->register_advanced_query_section();

		// 3. Carousel Mode (from Base)
		$this->register_carousel_section();

		// 4. Style: Featured Image
		$this->start_controls_section(
			'section_image_style', [ 'label' => 'Featured Image', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
		);

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

		// 5. Style: Entry Meta (from Base)
		$this->register_entry_meta_styling();

		// 6. Style: Dark Mode & Boxed (from Base)
		$this->register_box_style_controls();
		$this->register_dark_mode_overrides();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$settings['is_widget'] = true;
		
		// Force Carousel Mode internally
		$settings['carousel'] = 'yes';

		$query = KE_Query::get_instance()->get_events( $settings );

		$classes = [
			'ke-elementor-widget',
			'ke-events-grid-carousel-widget',
		];

		if ( ! empty($settings['color_scheme']) ) $classes[] = 'ke-scheme-' . esc_attr($settings['color_scheme']);

		echo '<div class="' . implode(' ', $classes) . '">';
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
		echo '</div>';
	}
}
