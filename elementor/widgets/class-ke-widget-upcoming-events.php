<?php
/**
 * KE Upcoming Events Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Upcoming_Events extends \Elementor\Widget_Base {

	public function get_name() {
		return 'ke_upcoming_events';
	}

	public function get_title() {
		return 'Upcoming Events';
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'ke-events' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_query',
			[
				'label' => 'Query',
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => 'Count',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 4,
			]
		);

		$this->add_control(
			'event_category',
			[
				'label' => 'Category',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_tax_options( 'event_category' ),
				'multiple' => false,
			]
		);

		$this->add_control(
			'editor_pick',
			[
				'label' => 'Show Only Editor Picks',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_display',
			[
				'label' => 'Display',
			]
		);

		$this->add_control(
			'layout',
			[
				'label' => 'Layout',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'list',
				'options' => [
					'grid' => 'Grid',
					'list' => 'List (Compact)',
				],
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => 'Columns',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 6,
				'default' => 1,
				'condition' => [ 'layout' => 'grid' ],
			]
		);

		$this->add_control(
			'show_image',
			[
				'label' => 'Show Image',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$settings['status'] = 'upcoming'; // Explicitly upcoming
		$settings['orderby_custom'] = 'upcoming'; // Explicitly upcoming order
		
		$query = KE_Query::get_instance()->get_events( $settings );

		echo '<div class="ke-elementor-widget ke-upcoming-events-widget layout-' . esc_attr($settings['layout']) . '">';
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
		echo '</div>';
	}

	private function get_tax_options( $tax ) {
		$terms = get_terms( [ 'taxonomy' => $tax, 'hide_empty' => false ] );
		$options = [];
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = $term->name;
			}
		}
		return $options;
	}
}
