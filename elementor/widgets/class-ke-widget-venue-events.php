<?php
/**
 * KE Venue Events Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Venue_Events extends \Elementor\Widget_Base {

	public function get_name() {
		return 'ke_venue_events';
	}

	public function get_title() {
		return 'Events by Venue';
	}

	public function get_icon() {
		return 'eicon-archive';
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

		$this->add_control(
			'orderby_custom',
			[
				'label' => 'Order By',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'upcoming',
				'options' => [
					'upcoming' => 'Nearest Upcoming First',
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

		$this->end_controls_section();
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
		$query = KE_Query::get_instance()->get_events( $settings );

		echo '<div class="ke-elementor-widget ke-venue-events-widget">';
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
		echo '</div>';
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
