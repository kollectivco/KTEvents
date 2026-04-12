<?php
/**
 * Kontentainment Events Native Archive Template
 * Automatically used for the /events/ URL
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Optimized 2-column layout matching the single page integration
?>
<div class="ke-frontend-main ke-archive-main" style="background:#fff; padding: 40px 0;">
	<div class="rb-container">
		<div class="rb-section">
			<div class="ke-layout-sidebar" style="display: flex; gap: 40px; flex-wrap: wrap;">
				
				<!-- Main Content Column -->
				<div class="ke-main-col" style="flex: 2; min-width: 300px;">
					<?php 
					// Render the premium discovery interface
					echo KE_Shortcodes::get_instance()->render_events_archive( array(
						'columns' => 3,
						'limit'   => 12,
						'preset'  => 'classic'
					) ); 
					?>
				</div>

				<!-- Sidebar Column -->
				<div class="ke-sidebar-col" style="flex: 0.8; min-width: 280px;">
					<aside class="ke-sidebar-inner-integrated">
						<?php 
						if ( is_active_sidebar( 'ke-events-sidebar' ) ) {
							dynamic_sidebar( 'ke-events-sidebar' );
						} else {
							echo '<div class="ke-sidebar-empty-placeholder" style="color:#ccc; font-size:12px; text-align:center; padding:20px; border:1px dashed #eee; border-radius:10px;">Events Sidebar is empty. Add widgets in Admin > Appearance > Widgets.</div>';
						}
						?>
					</aside>
				</div>

			</div>
		</div>
	</div>
</div>

<?php 
get_footer(); 
