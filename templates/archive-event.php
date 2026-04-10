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
			<div class="ke-layout-sidebar">
				
				<!-- Main Content Area -->
				<div class="ke-main-col">
					<?php 
					// Render the premium discovery interface via the shortcode method
					echo KE_Shortcodes::get_instance()->render_events_archive( array(
						'columns' => 3,
						'limit'   => 12,
						'preset'  => 'classic'
					) ); 
					?>
				</div>

				<!-- Sidebar Area -->
				<?php if ( is_active_sidebar( 'ke-events-sidebar' ) ) : ?>
					<div class="ke-sidebar-col">
						<div class="ke-sidebar-inner">
							<?php dynamic_sidebar( 'ke-events-sidebar' ); ?>
						</div>
					</div>
				<?php else : ?>
					<!-- Fallback to theme sidebar if no specific event widgets -->
					<div class="ke-sidebar-col">
						<div class="ke-sidebar-inner">
							<?php get_sidebar(); ?>
						</div>
					</div>
				<?php endif; ?>

			</div>
		</div>
	</div>
</div>

<?php 
get_footer(); 
