<?php
/**
 * KE Upcoming Events Widget - v5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Upcoming_Events extends KE_Widget_Base {

	public function get_name() { return 'ke_upcoming_events'; }
	public function get_title() { return 'Upcoming Events v2'; }
	public function get_icon() { return 'eicon-calendar'; }

	protected function register_controls() {
		$this->start_controls_section( 'section_layout', [ 'label' => 'Layout Settings' ] );
		$this->add_control( 'layout_preset', [ 'label' => 'Preset', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'list_1', 'options' => KE_Elementor_Options::get_event_layout_presets() ] );
		$this->add_responsive_control( 'columns', [ 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 1 ] );
		$this->end_controls_section();

		$this->register_advanced_query_section();
		$this->register_carousel_section();
		$this->register_pagination_section();
		$this->register_entry_meta_styling();
		$this->register_box_style_controls();
		$this->register_dark_mode_overrides();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$settings['status'] = 'upcoming'; // Hardcoded filter
		$settings['is_widget'] = true;
		
		$query = KE_Query::get_instance()->get_events( $settings );
		echo '<div class="ke-elementor-widget ke-upcoming-events-widget">';
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
		echo '</div>';
	}
}
