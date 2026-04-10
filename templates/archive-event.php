<?php
/**
 * Kontentainment Events Native Archive Template
 * Automatically used for the /events/ URL
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// We use the shared layout wrappers to ensure theme consistency
?>
<div class="ke-frontend-main ke-archive-main">
	<div class="rb-container">
		<div class="rb-section">
			<div class="ke-main-col-full">
				<?php 
				// Render the premium discovery interface via the shortcode method
				echo KE_Shortcodes::get_instance()->render_events_archive( array(
					'columns' => 3,
					'limit'   => 12,
					'preset'  => 'classic'
				) ); 
				?>
			</div>
		</div>
	</div>
</div>

<?php 
get_footer(); 
