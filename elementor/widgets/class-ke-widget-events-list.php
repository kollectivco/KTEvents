<?php
/**
 * KE Events List Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Widget_Events_List extends KE_Widget_Base {

	public function get_name() { return 'ke_events_list'; }
	public function get_title() { return 'Events List'; }
	public function get_icon() { return 'eicon-editor-list-ul'; }

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
				'default' => 'list_1',
				'options' => KE_Elementor_Options::get_list_layout_presets(),
			]
		);

		// No columns control for lists

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
		
		// 5. Style: Title & Meta (from Base)
		$this->register_title_styling();
		$this->register_entry_meta_styling();

		// 6. Style: Dark Mode & Boxed (from Base)
		$this->register_box_style_controls();
		$this->register_dark_mode_overrides();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$settings['is_widget'] = true;
		
		// Force list into single column
		$settings['columns']        = 1;
		$settings['columns_tablet'] = 1;
		$settings['columns_mobile'] = 1;
		$settings['carousel']       = ''; 
		$settings['extra_classes']  = 'ke-events-list-widget';

		$query = KE_Query::get_instance()->get_events( $settings );
		echo KE_Query::get_instance()->render_events_loop( $query, $settings );
	}
}
