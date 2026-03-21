<?php
/**
 * Single Event Template - Modern Editorial Redesign
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
$cat_id = ! empty( $categories ) ? $categories[0]->term_id : 0;
$cat_name = ! empty( $categories ) ? $categories[0]->name : 'Event';

// Track shown IDs for duplicates
$shown_ids = [ $event_id ];
?>

<div class="ke-container ke-single-event-redesign">
	
	<!-- PART 1: TOP SECTION (Hero Info) -->
	<header class="ke-single-event-header">
		
		<div class="ke-single-event-poster">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large' ); ?>
			<?php else : ?>
				<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
			<?php endif; ?>
		</div>

		<div class="ke-single-event-info">
			<?php if ( $cat_name ) : ?>
				<div class="ke-single-event-cat"><?php echo esc_html($cat_name); ?></div>
			<?php endif; ?>

			<h1 class="ke-single-event-title"><?php the_title(); ?></h1>

			<div class="ke-single-event-meta-grid">
				<div class="ke-meta-item">
					<span class="ke-meta-label">Date & Time</span>
					<span class="ke-meta-value"><?php echo ke_get_event_date_display(); ?> <?php echo $event_time ? ' @ ' . esc_html($event_time) : ''; ?></span>
				</div>

				<?php if ( $venue_id ) : ?>
				<div class="ke-meta-item">
					<span class="ke-meta-label">Venue</span>
					<span class="ke-meta-value">
						<a href="<?php echo get_permalink($venue_id); ?>"><?php echo get_the_title($venue_id); ?></a>
					</span>
				</div>
				<?php endif; ?>

				<?php if ( $address ) : ?>
				<div class="ke-meta-item">
					<span class="ke-meta-label">Location</span>
					<span class="ke-meta-value"><?php echo esc_html($address); ?></span>
				</div>
				<?php endif; ?>

				<?php if ( $organizer ) : ?>
				<div class="ke-meta-item">
					<span class="ke-meta-label">Organizer</span>
					<span class="ke-meta-value"><?php echo esc_html($organizer); ?></span>
				</div>
				<?php endif; ?>
			</div>

			<?php if ( $official_url ) : ?>
			<div class="ke-single-event-actions">
				<a href="<?php echo esc_url($official_url); ?>" target="_blank" class="ke-cta-btn">View Official Website</a>
			</div>
			<?php endif; ?>
		</div>
	</header>

	<!-- PART 2: CONTENT SECTION -->
	<section class="ke-single-event-content">
		<h2 class="ke-section-title">About the Event</h2>
		<div class="ke-editorial-description">
			<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
		</div>
	</section>

	<hr class="ke-separator">

	<!-- PART 3: SUPPORTING SECTIONS -->

	<?php if ( $venue_id ) : ?>
	<section class="ke-supporting-section">
		<h2 class="ke-section-title">More at this Venue</h2>
		<?php
		$venue_query = new WP_Query([
			'post_type' => 'event',
			'posts_per_page' => 4,
			'post__not_in' => $shown_ids,
			'meta_query' => [ [ 'key' => 'KE_event_venue_id', 'value' => $venue_id, 'compare' => '=' ] ]
		]);
		if ( $venue_query->have_posts() ) :
			echo '<div class="ke-loop-wrapper">';
			while ( $venue_query->have_posts() ) : $venue_query->the_post();
				$shown_ids[] = get_the_ID();
				include KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
			endwhile;
			echo '</div>';
		else :
			echo '<p class="ke-empty-notice">No other upcoming events at this venue.</p>';
		endif;
		wp_reset_postdata();
		?>
	</section>
	<?php endif; ?>

	<?php if ( $cat_id ) : ?>
	<section class="ke-supporting-section">
		<h2 class="ke-section-title">More in this Category</h2>
		<?php
		$cat_query = new WP_Query([
			'post_type' => 'event',
			'posts_per_page' => 4,
			'post__not_in' => $shown_ids,
			'tax_query' => [ [ 'taxonomy' => 'event_category', 'field' => 'term_id', 'terms' => $cat_id ] ]
		]);
		if ( $cat_query->have_posts() ) :
			echo '<div class="ke-loop-wrapper">';
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

	<section class="ke-supporting-section">
		<h2 class="ke-section-title">Recommended Events</h2>
		<?php
		$rec_query = new WP_Query([
			'post_type' => 'event',
			'posts_per_page' => 4,
			'post__not_in' => $shown_ids,
			'meta_key' => 'KE_event_featured',
			'meta_value' => '1',
			'orderby' => 'rand'
		]);
		if ( ! $rec_query->have_posts() ) {
			// Fallback to latest upcoming
			$rec_query = new WP_Query([
				'post_type' => 'event',
				'posts_per_page' => 4,
				'post__not_in' => $shown_ids,
				'meta_key' => 'KE_event_date',
				'orderby' => 'meta_value',
				'order' => 'ASC'
			]);
		}
		if ( $rec_query->have_posts() ) :
			echo '<div class="ke-loop-wrapper">';
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
.ke-single-event-redesign { margin-top: 60px; margin-bottom: 80px; }
.ke-section-title { font-size: 24px; font-weight: 800; margin-bottom: 32px; color: var(--ke-primary); border-left: 4px solid var(--ke-accent); padding-left: 16px; }
.ke-editorial-description { font-size: 18px; line-height: 1.8; color: var(--ke-secondary); max-width: 800px; }
.ke-supporting-section { margin-top: 80px; }
.ke-separator { border: 0; border-top: 1px solid var(--ke-border); margin: 60px 0; }
.ke-empty-notice { color: var(--ke-secondary); font-style: italic; }
</style>

<?php get_footer(); ?>
