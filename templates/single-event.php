<?php
/**
 * Single Event Template - Premium Editorial Layout
 */

get_header();

$event_id    = get_the_ID();
$event_date  = ke_get_event_meta( $event_id, 'date' );
$event_time  = ke_get_event_meta( $event_id, 'time' );
$status      = ke_get_event_status_label( $event_id );
$venue_id    = ke_get_event_meta( $event_id, 'venue_id' );
$address     = $venue_id ? ke_get_venue_meta( $venue_id, 'address' ) : '';
$official_url = ke_get_event_meta( $event_id, 'url' );

$categories = get_the_terms( $event_id, 'event_category' );
$cat_name = ! empty( $categories ) ? $categories[0]->name : 'Event';
$cat_id   = ! empty( $categories ) ? $categories[0]->term_id : 0;

$shown_ids = [ $event_id ];

$phone       = $venue_id ? ke_get_venue_meta( $venue_id, 'phone' ) : '';

// Icons
$icon_calendar = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>';
$icon_clock = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
$icon_venue = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>';
$icon_phone = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>';
?>

<div class="ke-frontend-main ke-single-event-page" style="padding-top: 60px; margin-top: 0;">
	<div class="rb-container">
		<div class="rb-section">
			<div class="ke-layout-sidebar">
				
				<!-- Main Content Column -->
				<div class="ke-main-col">
					<div class="ke-foxiz-aware">

						<!-- Target Hero Section -->
						<header class="ke-hero-section">
							
							<!-- Row 1: Category & Title -->
							<div class="ke-hero-upper">
								<?php if ( $cat_name ) : ?>
									<div class="ke-hero-category"><?php echo esc_html($cat_name); ?></div>
								<?php endif; ?>
								<h1 class="ke-hero-title"><?php the_title(); ?></h1>
							</div>

							<!-- Row 2: Image & Meta Split -->
							<div class="ke-hero-split">
								
								<!-- Main Column: Poster Image -->
								<div class="ke-hero-poster-col">
									<?php if ( has_post_thumbnail() ) : ?>
										<?php the_post_thumbnail( 'large', [ 'class' => 'ke-poster-image', 'loading' => 'lazy' ] ); ?>
									<?php else : ?>
										<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>" class="ke-poster-image" loading="lazy">
									<?php endif; ?>
								</div>

								<!-- Details Column: Meta Stack -->
								<div class="ke-hero-meta-col">
									
									<div class="ke-meta-stack">
										<!-- 1. DATE -->
										<div class="ke-meta-block">
											<div class="ke-meta-icon-box"><?php echo $icon_calendar; ?></div>
											<div class="ke-meta-info">
												<label>Date</label>
												<strong><?php echo ke_get_event_date_display(); ?></strong>
											</div>
										</div>

										<!-- 2. TIME -->
										<?php if ( $event_time ) : ?>
										<div class="ke-meta-block">
											<div class="ke-meta-icon-box"><?php echo $icon_clock; ?></div>
											<div class="ke-meta-info">
												<label>Time</label>
												<strong><?php echo esc_html($event_time); ?></strong>
											</div>
										</div>
										<?php endif; ?>

										<!-- 3. VENUE & ADDRESS -->
										<?php if ( $venue_id ) : ?>
										<div class="ke-meta-block">
											<div class="ke-meta-icon-box"><?php echo $icon_venue; ?></div>
											<div class="ke-meta-info">
												<label>Venue</label>
												<strong><?php echo esc_html(get_the_title($venue_id)); ?></strong>
												<?php if ( $address ) : ?>
													<span class="ke-venue-address"><?php echo esc_html($address); ?></span>
												<?php endif; ?>
											</div>
										</div>
										<?php endif; ?>

										<!-- 4. PHONE -->
										<?php if ( $phone ) : ?>
										<div class="ke-meta-block">
											<div class="ke-meta-icon-box"><?php echo $icon_phone; ?></div>
											<div class="ke-meta-info">
												<label>Phone</label>
												<strong><?php echo esc_html($phone); ?></strong>
											</div>
										</div>
										<?php endif; ?>

										<?php if ( $official_url ) : ?>
										<div class="ke-hero-actions" style="margin-top: 30px;">
											<a href="<?php echo esc_url($official_url); ?>" target="_blank" class="ke-register-btn">Register / Visit Website</a>
										</div>
										<?php endif; ?>
									</div>

								</div>
							</div>
						</header>

						<!-- Description -->
						<?php if ( get_the_content() ) : ?>
						<div class="ke-content-body">
							<h2 class="ke-foxiz-section-title">About the Event</h2>
							<div class="entry-content">
								<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
							</div>
						</div>
						<?php endif; ?>

						<!-- Related Sections (AJAX Lazy Loaded) -->
						<div id="ke-related-venue-section" class="ke-lazy-section" data-type="venue" data-venue-id="<?php echo esc_attr($venue_id); ?>" data-exclude="<?php echo esc_attr($event_id); ?>"></div>
						
						<div id="ke-related-category-section" class="ke-lazy-section" data-type="category" data-cat-id="<?php echo esc_attr($cat_id); ?>" data-exclude="<?php echo esc_attr($event_id); ?>"></div>
						
						<div id="ke-recommended-section" class="ke-lazy-section" data-type="recommended" data-exclude="<?php echo esc_attr($event_id); ?>"></div>

					</div>
				</div>

				<!-- Sidebar Column -->
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
