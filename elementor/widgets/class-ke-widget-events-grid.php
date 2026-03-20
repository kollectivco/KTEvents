<?php
/**
 * KE Events Grid Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Events_Grid extends \Elementor\Widget_Base {

	public function get_name() {
		return 'ke_events_grid';
	}

	public function get_title() {
		return 'Events Grid';
	}

	public function get_icon() {
		return 'eicon-post-grid';
	}

	public function get_categories() {
		return [ 'ke-events' ];
	}

	protected function register_controls() {
		// Content / Query Section
		$this->start_controls_section(
			'section_query',
			[
				'label' => 'Query',
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => 'Posts Count',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
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
			'venue_id',
			[
				'label' => 'Specific Venue',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_post_options( 'venue' ),
				'multiple' => false,
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
					'ongoing' => 'Ongoing Only',
					'past' => 'Past Only',
				],
			]
		);

		$this->add_control(
			'featured',
			[
				'label' => 'Featured Only',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'orderby_custom',
			[
				'label' => 'Order By',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'upcoming',
				'options' => [
					'upcoming' => 'Nearest Upcoming First',
					'latest' => 'Latest Added',
					'date_asc' => 'Date (Asc)',
					'date_desc' => 'Date (Desc)',
					'title_asc' => 'Title A-Z',
					'title_desc' => 'Title Z-A',
				],
			]
		);

		$this->end_controls_section();

		// Display Section
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
				'tablet_default' => 2,
				'mobile_default' => 1,
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
			'show_date',
			[
				'label' => 'Show Date',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_time',
			[
				'label' => 'Show Time',
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
			'excerpt_length',
			[
				'label' => 'Excerpt Length (Words)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 20,
				'condition' => [ 'show_excerpt' => 'yes' ],
			]
		);

		$this->add_control(
			'show_venue',
			[
				'label' => 'Show Venue Name',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_meta',
			[
				'label' => 'Show City/Area',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_badge',
			[
				'label' => 'Show Featured Badge',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// Style Section
		$this->start_controls_section(
			'section_style',
			[
				'label' => 'Style',
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_bg',
			[
				'label' => 'Card Background',
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ke-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => 'Title Color',
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ke-card-title a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .ke-card-title a',
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label' => 'Meta Text Color',
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ke-card-meta, {{WRAPPER}} .ke-card-footer' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Columns mapping for responsive attributes
		$settings['columns'] = $settings['columns'] ?? 3;
		$settings['columns_tablet'] = $settings['columns_tablet'] ?? 2;
		$settings['columns_mobile'] = $settings['columns_mobile'] ?? 1;

		$query = KE_Query::get_instance()->get_events( $settings );

		echo '<div class="ke-elementor-widget ke-events-grid-widget">';
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
		echo '</div>';
	}

	/**
	 * Helpers
	 */
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

	private function get_post_options( $cpt ) {
		$posts = get_posts( [ 'post_type' => $cpt, 'numberposts' => -1 ] );
		$options = [];
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $p ) {
				$options[ $p->ID ] = $p->post_title;
			}
		}
		return $options;
	}
}
