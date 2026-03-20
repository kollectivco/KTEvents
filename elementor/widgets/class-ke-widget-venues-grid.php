<?php
/**
 * KE Venues Grid Widget - v5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Venues_Grid extends KE_Widget_Base {

	public function get_name() { return 'ke_venues_grid'; }
	public function get_title() { return 'Venues Grid v2'; }
	public function get_icon() { return 'eicon-map-pin'; }

	protected function register_controls() {
		// 1. Layout
		$this->start_controls_section( 'section_layout', [ 'label' => 'Layout Settings' ] );

		$this->add_control(
			'layout_preset',
			[
				'label' => 'Display Preset',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid' => 'Grid',
					'list' => 'List',
					'boxed' => 'Boxed Card',
				],
			]
		);

		$this->add_responsive_control( 'columns', [ 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3 ] );

		$this->end_controls_section();

		// 2. Query
		$this->start_controls_section( 'section_query', [ 'label' => 'Query Settings' ] );
		$this->add_control( 'posts_per_page', [ 'label' => 'Count', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 6 ] );
		$this->add_control( 'orderby_custom', [ 'label' => 'Order By', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'title_asc', 'options' => KE_Elementor_Options::get_sort_options() ] );
		$this->add_control( 'event_city', [ 'label' => 'City', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => $this->get_tax_options( 'event_city' ) ] );
		$this->end_controls_section();

		// 3. Entry Meta (Venue specific)
		$this->start_controls_section( 'section_display', [ 'label' => 'Display Settings' ] );
		$this->add_control( 'show_image', [ 'label' => 'Show Image', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'show_excerpt', [ 'label' => 'Show Excerpt', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'show_phone', [ 'label' => 'Show Phone', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'show_count', [ 'label' => 'Show Event Count', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->end_controls_section();

		// 4. Boxed (from Base)
		$this->register_box_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$settings['is_widget'] = true;
		
		$query = KE_Query::get_instance()->get_venues( $settings );

		echo '<div class="ke-elementor-widget ke-venues-grid-widget">';
		echo KE_Query::get_instance()->render_venues_loop( $query, $settings );
		echo '</div>';
	}
}
