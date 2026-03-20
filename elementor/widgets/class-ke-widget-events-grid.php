<?php
/**
 * KE Events Grid Widget - v5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Events_Grid extends KE_Widget_Base {

	public function get_name() { return 'ke_events_grid'; }
	public function get_title() { return 'Events Grid v2'; }
	public function get_icon() { return 'eicon-post-grid'; }

	protected function register_controls() {
		// 1. Layout Preset Family
		$this->start_controls_section(
			'section_layout', [ 'label' => 'Layout Settings' ]
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

		$this->add_responsive_control(
			'columns',
			[
				'label' => 'Columns',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
			]
		);

		$this->add_control(
			'gap_preset',
			[
				'label' => 'Column Gap',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => KE_Elementor_Options::get_gap_options(),
			]
		);

		$this->add_control(
			'horizontal_scroll',
			[
				'label' => 'Horizontal Scroll (Mobile)',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'description' => KE_Elementor_Descriptions::get('scroll_info'),
			]
		);

		$this->end_controls_section();

		// 2. Advanced Query (from Base)
		$this->register_advanced_query_section();

		// 3. Ajax Pagination (from Base)
		$this->register_pagination_section();

		// 4. Style: Featured Image
		$this->start_controls_section(
			'section_image_style', [ 'label' => 'Featured Image', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
		);

		$this->add_control(
			'image_ratio',
			[
				'label' => 'Ratio',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '16-9',
				'options' => KE_Elementor_Options::get_image_ratios(),
			]
		);

		$this->add_control(
			'image_radius',
			[ 'label' => 'Rounded Corners', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => [ '{{WRAPPER}} .ke-card-image img' => 'border-radius: {{SIZE}}{{UNIT}};' ] ]
		);

		$this->end_controls_section();

		// 5. Style: Entry Meta (from Base)
		$this->register_entry_meta_styling();

		// 6. Style: Dark Mode & Boxed (from Base)
		$this->register_box_style_controls();
		$this->register_dark_mode_overrides();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$settings['is_widget'] = true;

		$query = KE_Query::get_instance()->get_events( $settings );

		$classes = [
			'ke-elementor-widget',
			'ke-events-grid-widget',
			'ke-gap-' . esc_attr($settings['gap_preset'] ?? 'default'),
		];

		if ( ! empty($settings['color_scheme']) ) $classes[] = 'ke-scheme-' . esc_attr($settings['color_scheme']);

		echo '<div class="' . implode(' ', $classes) . '">';
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
		echo '</div>';
	}
}
