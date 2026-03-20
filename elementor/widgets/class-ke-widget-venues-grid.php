<?php
/**
 * KE Venues Grid Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Venues_Grid extends \Elementor\Widget_Base {

	public function get_name() {
		return 'ke_venues_grid';
	}

	public function get_title() {
		return 'Venues Grid';
	}

	public function get_icon() {
		return 'eicon-map-pin';
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
				'default' => 6,
			]
		);

		$this->add_control(
			'event_city',
			[
				'label' => 'City',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_tax_options( 'event_city' ),
				'multiple' => false,
			]
		);

		$this->add_control(
			'event_area',
			[
				'label' => 'Area',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_tax_options( 'event_area' ),
				'multiple' => false,
			]
		);

		$this->add_control(
			'orderby_custom',
			[
				'label' => 'Order By',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'title_asc',
				'options' => [
					'title_asc' => 'Title A-Z',
					'title_desc' => 'Title Z-A',
					'latest' => 'Latest Added',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_display',
			[
				'label' => 'Display',
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

		$this->add_control(
			'show_excerpt',
			[
				'label' => 'Show Excerpt',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_phone',
			[
				'label' => 'Show Phone',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_count',
			[
				'label' => 'Show Event Count',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$query = KE_Query::get_instance()->get_venues( $settings );

		echo '<div class="ke-elementor-widget ke-venues-grid-widget">';
		echo KE_Query::get_instance()->render_venues_loop( $query, $settings );
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
