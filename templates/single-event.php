<?php
/**
 * Single Event Template - Full Foxiz Theme Integration
 * Supports Rb-Container, RB-Flex, and Sidebar
 */

get_header();

$event_id    = get_the_ID();
$event_date  = ke_get_event_meta( $event_id, 'date' );
$event_time  = ke_get_event_meta( $event_id, 'time' );
$status      = ke_get_event_status_label( $event_id );
$venue_id    = ke_get_event_meta( $event_id, 'venue_id' );
$address     = $venue_id ? ke_get_venue_meta( $venue_id, 'address' ) : '';
$organizer   = ke_get_event_meta( $event_id, 'organizer' );
$official_url = ke_get_event_meta( $event_id, 'url' );

$categories = get_the_terms( $event_id, 'event_category' );
$cat_name = ! empty( $categories ) ? $categories[0]->name : 'Event';
$cat_id   = ! empty( $categories ) ? $categories[0]->term_id : 0;

$shown_ids = [ $event_id ];

// Icons
$icon_calendar = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>';
$icon_clock = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
$icon_venue = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>';
$icon_location = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>';
?>

<div class="rb-container">
	<div class="rb-section">
		<div class="ke-layout-sidebar">
			
			<!-- Main Content Column -->
			<div class="ke-main-col">
				<div class="ke-foxiz-aware">

					<!-- Hero: Split Layout with Editorial Alignment -->
					<header class="ke-single-hero-foxiz">
						
						<!-- LEFT: Poster - Starts Pushed Down to Align with Meta -->
						<div class="ke-poster-box">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'large' ); ?>
							<?php else : ?>
								<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
							<?php endif; ?>
						</div>

						<!-- RIGHT: Info area -->
						<div class="ke-info-box">
							<!-- 1. Category -->
							<?php if ( $cat_name ) : ?>
								<div class="ke-cat-chip"><?php echo esc_html($cat_name); ?></div>
							<?php endif; ?>

							<!-- 2. Title -->
							<h1 class="ke-title-large"><?php the_title(); ?></h1>

							<!-- 3. Divider -->
							<div class="ke-title-divider"></div>

							<!-- 4. Detailed Meta Block (Aligned with Image Top) -->
							<div class="ke-meta-stack">
								<div class="ke-meta-node">
									<?php echo $icon_calendar; ?>
									<div class="ke-meta-text">
										<label>Date</label>
										<span><?php echo ke_get_event_date_display(); ?></span>
									</div>
								</div>
								<?php if ( $event_time ) : ?>
								<div class="ke-meta-node">
									<?php echo $icon_clock; ?>
									<div class="ke-meta-text">
										<label>Time</label>
										<span><?php echo esc_html($event_time); ?></span>
									</div>
								</div>
								<?php endif; ?>
								<?php if ( $venue_id ) : ?>
								<div class="ke-meta-node">
									<?php echo $icon_venue; ?>
									<div class="ke-meta-text">
										<label>Venue</label>
										<span><a href="<?php echo get_permalink($venue_id); ?>"><?php echo get_the_title($venue_id); ?></a></span>
									</div>
								</div>
								<?php endif; ?>
								<?php if ( $address ) : ?>
								<div class="ke-meta-node">
									<?php echo $icon_location; ?>
									<div class="ke-meta-text">
										<label>Location</label>
										<span><?php echo esc_html($address); ?></span>
									</div>
								</div>
								<?php endif; ?>

								<?php if ( $official_url ) : ?>
								<div class="ke-actions-bt" style="margin-top: 20px;">
									<a href="<?php echo esc_url($official_url); ?>" target="_blank" class="button button-primary is-full-width">Register / Visit Website</a>
								</div>
								<?php endif; ?>
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

					<!-- Supporting Grids -->
					<?php if ( $venue_id ) : ?>
					<div class="ke-supporting-block">
						<h2 class="ke-foxiz-section-title">More at this Venue</h2>
						<?php
						$venue_query = new WP_Query([
							'post_type' => 'event',
							'posts_per_page' => 4,
							'post__not_in' => $shown_ids,
							'meta_query' => [ [ 'key' => 'KE_event_venue_id', 'value' => $venue_id ] ]
						]);
						if ( $venue_query->have_posts() ) :
							echo '<div class="ke-card-grid">';
							while ( $venue_query->have_posts() ) : $venue_query->the_post();
								$shown_ids[] = get_the_ID();
								include KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
							endwhile;
							echo '</div>';
						endif;
						wp_reset_postdata();
						?>
					</div>
					<?php endif; ?>

					<?php if ( $cat_id ) : ?>
					<div class="ke-supporting-block">
						<h2 class="ke-foxiz-section-title">More in this Category</h2>
						<?php
						$cat_query = new WP_Query([
							'post_type' => 'event',
							'posts_per_page' => 4,
							'post__not_in' => $shown_ids,
							'tax_query' => [ [ 'taxonomy' => 'event_category', 'terms' => $cat_id ] ]
						]);
						if ( $cat_query->have_posts() ) :
							echo '<div class="ke-card-grid">';
							while ( $cat_query->have_posts() ) : $cat_query->the_post();
								$shown_ids[] = get_the_ID();
								include KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
							endwhile;
							echo '</div>';
						endif;
						wp_reset_postdata();
						?>
					</div>
					<?php endif; ?>

					<div class="ke-supporting-block">
						<h2 class="ke-foxiz-section-title">Recommended Events</h2>
						<?php
						$rec_query = new WP_Query([
							'post_type' => 'event',
							'posts_per_page' => 4,
							'post__not_in' => $shown_ids,
							'orderby' => 'rand'
						]);
						if ( $rec_query->have_posts() ) :
							echo '<div class="ke-card-grid">';
							while ( $rec_query->have_posts() ) : $rec_query->the_post();
								include KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
							endwhile;
							echo '</div>';
						endif;
						wp_reset_postdata();
						?>
					</div>

				</div>
			</div>

			<!-- Sidebar Column -->
			<div class="ke-sidebar-col">
				<?php get_sidebar(); ?>
			</div>

		</div>
	</div>
</div>

<?php get_footer(); ?>
