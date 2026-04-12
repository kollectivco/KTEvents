<?php
/**
 * Single Event Template - Compact Integrated Design with Sidebar
 */

get_header();

$event_id    = get_the_ID();
$event_date  = get_post_meta( $event_id, 'KE_event_date', true );
$event_time  = get_post_meta( $event_id, 'KE_event_time', true );
$venue_id    = get_post_meta( $event_id, 'KE_event_venue_id', true );
$phone       = $venue_id ? get_post_meta( $venue_id, 'KE_venue_phone', true ) : 'N/A';
$venue_name  = $venue_id ? get_the_title( $venue_id ) : 'TBA';
$venue_addr  = $venue_id ? get_post_meta( $venue_id, 'KE_venue_address', true ) : '';

$categories = get_the_terms( $event_id, 'event_category' );
$cat_name = ! empty( $categories ) ? $categories[0]->name : 'Event';

// Standardized Icons
$icon_calendar = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
$icon_clock = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
$icon_venue = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;"><path d="M3 21h18"></path><path d="M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7H3z"></path><path d="M5 21V7"></path><path d="M19 21V7"></path><path d="M9 21v-4a2 2 0 0 1 4 0v4"></path></svg>';
$icon_phone = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
?>

<div class="ke-frontend-main ke-integrated-layout" style="background:#fff; padding: 40px 0;">
	<div class="rb-container">
		<div class="rb-section">
			<div class="ke-layout-sidebar" style="display: flex; gap: 40px; flex-wrap: wrap;">
				
				<!-- Main Content Column -->
				<div class="ke-main-col" style="flex: 2; min-width: 300px;">
					<div class="ke-foxiz-aware">

						<!-- Layout Header -->
						<div style="text-transform: uppercase; font-size: 12px; font-weight: 800; letter-spacing: 0.1em; color: #111827; margin-bottom: 15px;">
							<?php echo esc_html($cat_name); ?>
						</div>
						<h1 style="font-size: 42px; font-weight: 900; margin: 0 0 40px 0; letter-spacing: -0.01em; line-height: 1.1; color: #111827;">
							<?php the_title(); ?>
						</h1>

						<!-- Split Info Row -->
						<div style="display: flex; gap: 40px; align-items: flex-start; margin-bottom: 50px; flex-wrap: wrap;">
							
							<!-- Poster Image (Compact) -->
							<div style="flex: 1.1; min-width: 280px;">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'large', [ 'style' => 'width:100%; height:auto; border-radius: 16px; box-shadow: 0 15px 30px rgba(0,0,0,0.08); display:block;' ] ); ?>
								<?php else : ?>
									<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>" style="width:100%; height:auto; border-radius:16px; box-shadow:0 15px 30px rgba(0,0,0,0.08);">
								<?php endif; ?>
							</div>

							<!-- Details Side (Compact) -->
							<div style="flex: 1; min-width: 250px; display: flex; flex-direction: column; gap: 20px; padding-top: 5px;">
								
								<div style="display: flex; gap: 15px; align-items: center;">
									<div style="width: 44px; height: 44px; background: #f9fafb; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6b7280; flex-shrink: 0;">
										<?php echo $icon_calendar; ?>
									</div>
									<div>
										<div style="font-size: 13px; font-weight: 800; color: #111827; text-transform: uppercase; letter-spacing: 0.05em;">Date</div>
										<div style="font-size: 15px; color: #4b5563; font-weight: 500;"><?php echo $event_date ? date_i18n( 'd F Y', strtotime( $event_date ) ) : 'TBA'; ?></div>
									</div>
								</div>

								<div style="display: flex; gap: 15px; align-items: center;">
									<div style="width: 44px; height: 44px; background: #f9fafb; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6b7280; flex-shrink: 0;">
										<?php echo $icon_clock; ?>
									</div>
									<div>
										<div style="font-size: 13px; font-weight: 800; color: #111827; text-transform: uppercase; letter-spacing: 0.05em;">Time</div>
										<div style="font-size: 15px; color: #4b5563; font-weight: 500;"><?php echo $event_time ?: 'TBA'; ?></div>
									</div>
								</div>

								<div style="display: flex; gap: 15px; align-items: center;">
									<div style="width: 44px; height: 44px; background: #f9fafb; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6b7280; flex-shrink: 0;">
										<?php echo $icon_venue; ?>
									</div>
									<div>
										<div style="font-size: 13px; font-weight: 800; color: #111827; text-transform: uppercase; letter-spacing: 0.05em;">Venue</div>
										<div style="font-size: 15px; color: #4b5563; font-weight: 600;"><?php echo esc_html($venue_name); ?></div>
										<?php if ( $venue_addr ) : ?>
											<div style="font-size: 13px; color: #9ca3af;"><?php echo esc_html($venue_addr); ?></div>
										<?php endif; ?>
									</div>
								</div>

								<div style="display: flex; gap: 15px; align-items: center;">
									<div style="width: 44px; height: 44px; background: #f9fafb; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6b7280; flex-shrink: 0;">
										<?php echo $icon_phone; ?>
									</div>
									<div>
										<div style="font-size: 13px; font-weight: 800; color: #111827; text-transform: uppercase; letter-spacing: 0.05em;">Phone</div>
										<div style="font-size: 15px; color: #4b5563; font-weight: 500;"><?php echo esc_html($phone); ?></div>
									</div>
								</div>

							</div>
						</div>

						<!-- About Section -->
						<div class="ke-about-block" style="margin-top: 50px;">
							<h2 style="font-size: 24px; font-weight: 800; margin-bottom: 20px; border-bottom: 1px solid #f3f4f6; padding-bottom: 10px;">About the Event</h2>
							<div class="ke-main-description-rich">
								<?php 
								if ( have_posts() ) {
									while ( have_posts() ) {
										the_post();
										the_content();
									}
								}
								?>
							</div>
						</div>

						<!-- Related / Recommended (AJAX Lazy) -->
						<div id="ke-related-venue-section" class="ke-lazy-section" data-type="venue" data-venue-id="<?php echo esc_attr($venue_id); ?>" data-exclude="<?php echo esc_attr($event_id); ?>"></div>
						<div id="ke-related-category-section" class="ke-lazy-section" data-type="category" data-cat-id="<?php echo esc_attr($cat_id); ?>" data-exclude="<?php echo esc_attr($event_id); ?>"></div>
						<div id="ke-recommended-section" class="ke-lazy-section" data-type="recommended" data-exclude="<?php echo esc_attr($event_id); ?>"></div>

					</div>
				</div>

				<!-- Sidebar Column (Dedicated Events Sidebar Only) -->
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

<?php get_footer(); ?>
