<?php
/**
 * KE Featured Events Widget - v5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Featured_Events extends KE_Widget_Base {

	public function get_name() { return 'ke_featured_events'; }
	public function get_title() { return 'Featured Events v2'; }
	public function get_icon() { return 'eicon-star'; }

	protected function register_controls() {
		// 1. Layout
		$this->start_controls_section( 'section_layout', [ 'label' => 'Layout Settings' ] );
		$this->add_control( 'layout_preset', [ 'label' => 'Preset', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'classic', 'options' => KE_Elementor_Options::get_event_layout_presets() ] );
		$this->add_responsive_control( 'columns', [ 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3 ] );
		$this->end_controls_section();

		// 2. Advanced Query
		$this->register_advanced_query_section();

		// 2.5 Carousel Mode
		$this->register_carousel_section();

		// 2.7 Pagination
		$this->register_pagination_section();

		// 3. Style
		$this->register_entry_meta_styling();
		$this->register_box_style_controls();
		$this->register_dark_mode_overrides();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$settings['featured']  = true; 
		$settings['is_widget'] = true;
		$settings['extra_classes'] = 'ke-featured-events-widget';
		
		$query = KE_Query::get_instance()->get_events( $settings );
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
	}
}
