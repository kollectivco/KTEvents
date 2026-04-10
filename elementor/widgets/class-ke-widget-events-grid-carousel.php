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
				'options' => KE_Elementor_Options::get_grid_layout_presets(),
			]
		);

		$this->end_controls_section();

		// 2. Advanced Query (from Base)
		$this->register_advanced_query_section();

		// 3. Carousel Mode (from Base)
		$this->register_carousel_section( true );

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
		$settings['carousel']  = 'yes';
		$settings['extra_classes'] = 'ke-events-grid-carousel-widget';

		$query = KE_Query::get_instance()->get_events( $settings );
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
	}
}
