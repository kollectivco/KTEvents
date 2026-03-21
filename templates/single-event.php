<?php
/**
 * Single Event Template - Vertical Stacked Details & Sidebar Support
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

<div class="ke-single-wrapper">
	
	<!-- Hero Section: Vertical Stacked Details -->
	<header class="ke-single-hero">
		<div class="ke-single-poster">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large' ); ?>
			<?php else : ?>
				<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
			<?php endif; ?>
		</div>

		<div class="ke-single-details">
			<?php if ( $cat_name ) : ?>
				<div class="ke-single-cat"><?php echo esc_html($cat_name); ?></div>
			<?php endif; ?>

			<h1 class="ke-single-title"><?php the_title(); ?></h1>

			<div class="ke-meta-stack">

				<div class="ke-meta-group">
					<div class="ke-meta-row">
						<?php echo $icon_calendar; ?>
						<div class="ke-meta-body">
							<span class="ke-meta-label">Date</span>
							<span class="ke-meta-value"><?php echo ke_get_event_date_display(); ?></span>
						</div>
					</div>

					<?php if ( $event_time ) : ?>
					<div class="ke-meta-row">
						<?php echo $icon_clock; ?>
						<div class="ke-meta-body">
							<span class="ke-meta-label">Time</span>
							<span class="ke-meta-value"><?php echo esc_html($event_time); ?></span>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<?php if ( $venue_id ) : ?>
				<div class="ke-meta-row">
					<?php echo $icon_venue; ?>
					<div class="ke-meta-body">
						<span class="ke-meta-label">Venue</span>
						<span class="ke-meta-value">
							<a href="<?php echo get_permalink($venue_id); ?>"><?php echo get_the_title($venue_id); ?></a>
						</span>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $address ) : ?>
				<div class="ke-meta-row">
					<?php echo $icon_location; ?>
					<div class="ke-meta-body">
						<span class="ke-meta-label">Location</span>
						<span class="ke-meta-value"><?php echo esc_html($address); ?></span>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $official_url ) : ?>
				<div class="ke-meta-actions" style="margin-top: 15px;">
					<a href="<?php echo esc_url($official_url); ?>" target="_blank" class="button button-primary">Visit Official Site</a>
				</div>
				<?php endif; ?>

			</div>
		</div>
	</header>

	<!-- About Section -->
	<?php if ( get_the_content() ) : ?>
	<section class="ke-about-event">
		<div class="ke-section-header">
			<h2>About the Event</h2>
		</div>
		<div class="entry-content">
			<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Supporting Sections (Real Grids) -->

	<?php if ( $venue_id ) : ?>
	<section class="ke-venue-events">
		<div class="ke-section-header">
			<h2>More at this Venue</h2>
		</div>
		<?php
		$venue_query = new WP_Query([
			'post_type' => 'event',
			'posts_per_page' => 4,
			'post__not_in' => $shown_ids,
			'meta_query' => [ [ 'key' => 'KE_event_venue_id', 'value' => $venue_id ] ]
		]);
		if ( $venue_query->have_posts() ) :
			echo '<div class="ke-grid">';
			while ( $venue_query->have_posts() ) : $venue_query->the_post();
				$shown_ids[] = get_the_ID();
				include KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
			endwhile;
			echo '</div>';
		else :
			echo '<p class="ke-empty-small">No other events scheduled at this venue.</p>';
		endif;
		wp_reset_postdata();
		?>
	</section>
	<?php endif; ?>

	<?php if ( $cat_id ) : ?>
	<section class="ke-cat-events">
		<div class="ke-section-header">
			<h2>More in this Category</h2>
		</div>
		<?php
		$cat_query = new WP_Query([
			'post_type' => 'event',
			'posts_per_page' => 4,
			'post__not_in' => $shown_ids,
			'tax_query' => [ [ 'taxonomy' => 'event_category', 'terms' => $cat_id ] ]
		]);
		if ( $cat_query->have_posts() ) :
			echo '<div class="ke-grid">';
			while ( $cat_query->have_posts() ) : $cat_query->the_post();
				$shown_ids[] = get_the_ID();
				include KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
			endwhile;
			echo '</div>';
		endif;
		wp_reset_postdata();
		?>
	</section>
	<?php endif; ?>

	<section class="ke-recommended-events">
		<div class="ke-section-header">
			<h2>Recommended Events</h2>
		</div>
		<?php
		$rec_query = new WP_Query([
			'post_type' => 'event',
			'posts_per_page' => 4,
			'post__not_in' => $shown_ids,
			'orderby' => 'rand'
		]);
		if ( $rec_query->have_posts() ) :
			echo '<div class="ke-grid">';
			while ( $rec_query->have_posts() ) : $rec_query->the_post();
				include KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
			endwhile;
			echo '</div>';
		endif;
		wp_reset_postdata();
		?>
	</section>

</div>

<style>
.ke-empty-small { color: var(--ke-secondary); font-size: 14px; font-style: italic; margin-bottom: 40px; }
.entry-content { max-width: 800px; font-size: 17px; line-height: 1.7; color: #444; }
</style>

<?php get_footer(); ?>
