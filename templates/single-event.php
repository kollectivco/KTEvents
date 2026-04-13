<?php
/**
 * Single Event Template - High Performance Static Layout with Mobile 2-Card Grid
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
$cat_id   = ! empty( $categories ) ? $categories[0]->term_id : 0;

// Icons
$icon_calendar = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
$icon_clock = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
$icon_venue = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;"><path d="M3 21h18"></path><path d="M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7H3z"></path><path d="M5 21V7"></path><path d="M19 21V7"></path><path d="M9 21v-4a2 2 0 0 1 4 0v4"></path></svg>';
$icon_phone = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
?>

<div class="ke-single-entry-page">
	<div class="rb-container">
		<div class="ke-page-row">
			<div class="ke-col-main">
				<div class="ke-single-card">
					<div class="ke-eyebrow-text"><?php echo esc_html($cat_name); ?></div>
					<h1 class="ke-single-title"><?php the_title(); ?></h1>

					<div class="ke-hero-grid">
						<div class="ke-hero-poster">
							<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'large', [ 'class' => 'ke-main-img' ] ); else : ?>
								<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" class="ke-main-img">
							<?php endif; ?>
						</div>

						<div class="ke-hero-details">
							<div class="ke-detail-item">
								<div class="ke-detail-icon"><?php echo $icon_calendar; ?></div>
								<div class="ke-detail-content">
									<span class="ke-detail-label">Date</span>
									<span class="ke-detail-value"><?php echo $event_date ? date_i18n( 'd F Y', strtotime( $event_date ) ) : 'TBA'; ?></span>
								</div>
							</div>
							<div class="ke-detail-item">
								<div class="ke-detail-icon"><?php echo $icon_clock; ?></div>
								<div class="ke-detail-content">
									<span class="ke-detail-label">Time</span>
									<span class="ke-detail-value"><?php echo $event_time ?: 'TBA'; ?></span>
								</div>
							</div>
							<div class="ke-detail-item">
								<div class="ke-detail-icon"><?php echo $icon_venue; ?></div>
								<div class="ke-detail-content">
									<span class="ke-detail-label">Venue</span>
									<span class="ke-detail-value"><strong><?php echo esc_html($venue_name); ?></strong></span>
								</div>
							</div>
							<div class="ke-detail-item">
								<div class="ke-detail-icon"><?php echo $icon_phone; ?></div>
								<div class="ke-detail-content">
									<span class="ke-detail-label">Phone</span>
									<span class="ke-detail-value"><?php echo esc_html($phone); ?></span>
								</div>
							</div>
						</div>
					</div>

					<!-- Static Related Content -->
					<div class="ke-static-related-sections">
						<?php 
						$sections = [
							['type' => 'venue', 'id' => $venue_id, 'title' => 'More at this Venue', 'key' => 'venue_id'],
							['type' => 'category', 'id' => $cat_id, 'title' => 'More in this Category', 'key' => 'ke_category'],
							['type' => 'recommended', 'id' => 1, 'title' => 'Recommended Events', 'key' => 'ke_sort']
						];

						foreach ($sections as $sec) {
							if ( !$sec['id'] ) continue;

							$args = [ 'posts_per_page' => 9, 'post__not_in' => [$event_id], 'no_found_rows' => true ];
							if ($sec['type'] === 'recommended') $args['ke_sort'] = 'date_desc';
							else $args[$sec['key']] = $sec['id'];

							$query = KE_Query::get_instance()->get_events($args);
							if ( $query->have_posts() ) {
								echo '<div class="ke-supporting-block">';
								echo '<h2 class="ke-foxiz-section-title">' . esc_html($sec['title']) . '</h2>';
								// Render with Load More capability (initially showing 4, revealing rest)
								echo KE_Query::get_instance()->render_events_loop( $query, [ 'columns' => 3, 'columns_mobile' => 2, 'max_initial' => 3 ] );
								echo '</div>';
							}
						}
						?>
					</div>
				</div>
			</div>

			<div class="ke-col-sidebar">
				<aside class="ke-sidebar-wrapper">
					<?php if ( is_active_sidebar( 'ke-events-sidebar' ) ) dynamic_sidebar( 'ke-events-sidebar' ); ?>
				</aside>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>
