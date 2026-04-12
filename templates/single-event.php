<?php
/**
 * Single Event Template - Integrated Premium Design
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

// Icons
$icon_calendar = '<svg class="ke-new-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
$icon_clock = '<svg class="ke-new-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
$icon_venue = '<svg class="ke-new-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"></path><path d="M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7H3z"></path><path d="M5 21V7"></path><path d="M19 21V7"></path><path d="M9 21v-4a2 2 0 0 1 4 0v4"></path></svg>';
$icon_phone = '<svg class="ke-new-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
?>

<div class="ke-frontend-main ke-single-event-page ke-integrated-mode">
	<div class="rb-container">
		<div class="rb-section">
			<div class="ke-layout-sidebar">
				
				<!-- Main Content Column -->
				<div class="ke-main-col">
					<div class="ke-foxiz-aware">

						<!-- Header Section -->
						<div class="ke-eyebrow-new"><?php echo esc_html($cat_name); ?></div>
						<h1 class="ke-title-new"><?php the_title(); ?></h1>

						<!-- Main Grid -->
						<div class="ke-hero-split-new">
							
							<!-- Left: Image -->
							<div class="ke-poster-image-wrap-new">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'large' ); ?>
								<?php else : ?>
									<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
								<?php endif; ?>
							</div>

							<!-- Right: Details -->
							<div class="ke-details-side-new">
								
								<!-- Date -->
								<div class="ke-meta-node-new">
									<div class="ke-meta-icon-new"><?php echo $icon_calendar; ?></div>
									<div class="ke-meta-info-new">
										<div class="ke-meta-label-new">Date</div>
										<div class="ke-meta-value-new"><?php echo $event_date ? date_i18n( 'd F Y', strtotime( $event_date ) ) : 'TBA'; ?></div>
									</div>
								</div>

								<!-- Time -->
								<div class="ke-meta-node-new">
									<div class="ke-meta-icon-new"><?php echo $icon_clock; ?></div>
									<div class="ke-meta-info-new">
										<div class="ke-meta-label-new">Time</div>
										<div class="ke-meta-value-new"><?php echo $event_time ?: 'TBA'; ?></div>
									</div>
								</div>

								<!-- Venue -->
								<div class="ke-meta-node-new">
									<div class="ke-meta-icon-new"><?php echo $icon_venue; ?></div>
									<div class="ke-meta-info-new">
										<div class="ke-meta-label-new">Venue</div>
										<div class="ke-meta-value-new"><strong><?php echo esc_html($venue_name); ?></strong></div>
										<?php if ( $venue_addr ) : ?>
											<div class="ke-meta-value-new" style="font-size: 14px; opacity: 0.7;"><?php echo esc_html($venue_addr); ?></div>
										<?php endif; ?>
									</div>
								</div>

								<!-- Phone -->
								<div class="ke-meta-node-new">
									<div class="ke-meta-icon-new"><?php echo $icon_phone; ?></div>
									<div class="ke-meta-info-new">
										<div class="ke-meta-label-new">Phone</div>
										<div class="ke-meta-value-new"><?php echo esc_html($phone); ?></div>
									</div>
								</div>

							</div>

						</div>

						<!-- Description Section -->
						<?php if ( get_the_content() ) : ?>
							<div class="ke-description-section-new">
								<h2 class="ke-section-title-new">About the Event</h2>
								<div class="ke-content-rich-integrated">
									<?php 
									while ( have_posts() ) {
										the_post();
										the_content();
									}
									?>
								</div>
							</div>
						<?php endif; ?>

						<!-- RELATED SECTIONS (LAZY LOAD) -->
						<div id="ke-related-venue-section" class="ke-lazy-section" data-type="venue" data-venue-id="<?php echo esc_attr($venue_id); ?>" data-exclude="<?php echo esc_attr($event_id); ?>"></div>
						<div id="ke-related-category-section" class="ke-lazy-section" data-type="category" data-cat-id="<?php echo esc_attr($cat_id); ?>" data-exclude="<?php echo esc_attr($event_id); ?>"></div>
						<div id="ke-recommended-section" class="ke-lazy-section" data-type="recommended" data-exclude="<?php echo esc_attr($event_id); ?>"></div>

					</div>
				</div>

				<!-- Sidebar Column (Integrated) -->
				<?php if ( is_active_sidebar( 'ke-events-sidebar' ) ) : ?>
					<div class="ke-sidebar-col">
						<div class="ke-sidebar-inner">
							<?php dynamic_sidebar( 'ke-events-sidebar' ); ?>
						</div>
					</div>
				<?php else : ?>
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

<?php get_footer(); ?>
