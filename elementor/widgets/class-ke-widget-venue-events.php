<?php
/**
 * KE Venue Events Widget - v5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Venue_Events extends KE_Widget_Base {

	public function get_name() { return 'ke_venue_events'; }
	public function get_title() { return 'Events by Venue v2'; }
	public function get_icon() { return 'eicon-archive'; }

	protected function register_controls() {
		// 1. Layout
		$this->start_controls_section( 'section_layout', [ 'label' => 'Layout Settings' ] );

		$this->add_control(
			'source_mode',
			[
				'label' => 'Source',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'manual',
				'options' => [
					'manual' => 'Select Manually',
					'current' => 'Current Venue (Dynamic)',
				],
			]
		);

		$this->add_control(
			'venue_id',
			[
				'label' => 'Select Venue',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_post_options( 'venue' ),
				'multiple' => false,
				'condition' => [ 'source_mode' => 'manual' ],
			]
		);

		$this->add_control(
			'layout_preset',
			[
				'label' => 'Display Preset',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid_1',
				'options' => KE_Elementor_Options::get_event_layout_presets(),
			]
		);

		$this->add_responsive_control( 'columns', [ 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3 ] );

		$this->end_controls_section();

		// 2. Query
		$this->register_query_section();

		// 2.5 Pagination
		$this->register_pagination_section();

		// 2.7 Carousel Mode
		$this->register_carousel_section();

		// 3. Entry Meta (from Base)
		$this->register_entry_meta_controls();

		// 4. Boxed (from Base)
		$this->register_box_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$venue_id = 0;
		if ( 'current' === $settings['source_mode'] ) {
			$venue_id = get_the_ID();
			if ( get_post_type($venue_id) !== 'venue' ) {
				if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					echo '<div class="elementor-alert elementor-alert-info">Please use this widget on a Single Venue page or select a venue manually.</div>';
				}
				return;
			}
		} else {
			$venue_id = $settings['venue_id'];
		}

		if ( ! $venue_id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-warning">Select a venue to display events.</div>';
			}
			return;
		}

		$settings['venue_id'] = $venue_id;
		$settings['is_widget'] = true;
		
		$query = KE_Query::get_instance()->get_events( $settings );

		echo '<div class="ke-elementor-widget ke-venue-events-widget">';
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
		echo '</div>';
	}
}
