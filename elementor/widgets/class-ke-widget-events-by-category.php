<?php
/**
 * KE Events by Category Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Events_By_Category extends \Elementor\Widget_Base {

	public function get_name() {
		return 'ke_events_by_category';
	}

	public function get_title() {
		return 'Events by Category';
	}

	public function get_icon() {
		return 'eicon-folder';
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
			'event_category',
			[
				'label' => 'Category',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_tax_options( 'event_category' ),
				'multiple' => false,
				'description' => 'Select a category to display events from.',
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
			'status',
			[
				'label' => 'Status',
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_display',
			[
				'label' => 'Display Settings',
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => 'Columns',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 6,
				'default' => 3,
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

		if ( empty( $settings['event_category'] ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">Please select a category to display events.</div>';
			}
			return;
		}

		$query = KE_Query::get_instance()->get_events( $settings );

		echo '<div class="ke-elementor-widget ke-events-by-category-widget">';
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
