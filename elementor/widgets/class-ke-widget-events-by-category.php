<?php
/**
 * KE Events by Category Widget - v5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Events_By_Category extends KE_Widget_Base {

	public function get_name() { return 'ke_events_by_category'; }
	public function get_title() { return 'Events by Category v2'; }
	public function get_icon() { return 'eicon-folder'; }

	protected function register_controls() {
		// 1. Layout
		$this->start_controls_section( 'section_layout', [ 'label' => 'Layout Settings' ] );

		$this->add_control(
			'event_category',
			[
				'label' => 'Select Category',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_tax_options( 'event_category' ),
				'multiple' => false,
			]
		);

		$this->add_control(
			'layout_preset',
			[
				'label' => 'Display Preset',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => KE_Elementor_Options::get_event_layout_presets(),
			]
		);

		$this->add_responsive_control( 'columns', [ 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3 ] );

		$this->end_controls_section();

		// 2. Query
		$this->register_query_section();

		// 2.5 Pagination
		$this->register_pagination_section();

		// 3. Entry Meta (from Base)
		$this->register_entry_meta_controls();

		// 4. Boxed Style (from Base)
		$this->register_box_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['event_category'] ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">Please select a category to display events.</div>';
			}
			return;
		}

		$settings['is_widget'] = true;
		$query = KE_Query::get_instance()->get_events( $settings );

		echo '<div class="ke-elementor-widget ke-events-by-category-widget">';
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
		echo '</div>';
	}
}
