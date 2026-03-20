<?php
/**
 * Single Event Template
 */

get_header(); ?>

<?php while ( have_posts() ) : the_post(); 
$post_id = get_the_ID();
$venue_id = ke_get_event_meta( $post_id, 'venue_id' );
$event_date = ke_get_event_date_display();
$event_end_date = ke_get_event_meta( $post_id, 'end_date' );
$event_time = ke_get_event_meta( $post_id, 'time' );
$event_end_time = ke_get_event_meta( $post_id, 'end_time' );
$status = ke_get_event_meta( $post_id, 'status' );
$organizer = ke_get_event_meta( $post_id, 'organizer_name' );
$address = ke_get_event_meta( $post_id, 'address' );
$phone = ke_get_event_meta( $post_id, 'phone' );
$official_url = ke_get_event_meta( $post_id, 'official_url' );
$source_url = ke_get_event_meta( $post_id, 'source_url' );
?>

<div class="ke-container ke-single-event">
	<div class="ke-single-grid">
		<div class="ke-single-content">
			<header class="ke-single-header">
				<div class="ke-single-meta">
					<span class="ke-badge ke-badge-status ke-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ke_get_event_status_label( $post_id ) ); ?></span>
					<span class="ke-date"><?php echo esc_html( $event_date ); ?><?php echo $event_end_date ? ' - ' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event_end_date ) ) ) : ''; ?></span>
					<?php if ( $event_time ) : ?>
						<span class="ke-time"><?php echo esc_html( $event_time ); ?><?php echo $event_end_time ? ' - ' . esc_html( $event_end_time ) : ''; ?></span>
					<?php endif; ?>
				</div>
				<h1 class="ke-single-title"><?php the_title(); ?></h1>
				
				<?php if ( has_excerpt() ) : ?>
					<div class="ke-single-excerpt">
						<?php the_excerpt(); ?>
					</div>
				<?php endif; ?>
			</header>

			<div class="ke-single-image">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'large' ); ?>
				<?php endif; ?>
			</div>

			<div class="ke-single-description">
				<h2 class="ke-section-title">About this Event</h2>
				<?php the_content(); ?>
			</div>

			<!-- Related Events -->
			<section class="ke-related-events">
				<h2 class="ke-section-title">Related Events</h2>
				<div class="ke-grid ke-grid-small">
					<?php 
					$related_query = KE_Query::get_instance()->get_related_events( $post_id, 3 );
					if ( $related_query->have_posts() ) :
						while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
							<article class="ke-card ke-event-card-small">
								<a href="<?php the_permalink(); ?>">
									<?php if ( has_post_thumbnail() ) : ?>
										<?php the_post_thumbnail( 'medium' ); ?>
									<?php endif; ?>
									<h4 class="ke-card-title"><?php the_title(); ?></h4>
									<div class="ke-card-meta"><?php echo esc_html( ke_get_event_date_display() ); ?></div>
								</a>
							</article>
						<?php endwhile;
						wp_reset_postdata();
					else : ?>
						<p>No related events found.</p>
					<?php endif; ?>
				</div>
			</section>

			<?php if ( $venue_id ) : ?>
				<!-- More events from same venue -->
				<section class="ke-venue-events">
					<h2 class="ke-section-title">More from <?php echo esc_html( get_the_title( $venue_id ) ); ?></h2>
					<div class="ke-grid ke-grid-small">
						<?php 
						$venue_events = KE_Query::get_instance()->get_venue_events( $venue_id, 'upcoming', 3 );
						if ( $venue_events->have_posts() ) :
							while ( $venue_events->have_posts() ) : $venue_events->the_post(); 
								if ( get_the_ID() === $post_id ) continue; ?>
								<article class="ke-card ke-event-card-small">
									<a href="<?php the_permalink(); ?>">
										<?php if ( has_post_thumbnail() ) : ?>
											<?php the_post_thumbnail( 'medium' ); ?>
										<?php endif; ?>
										<h4 class="ke-card-title"><?php the_title(); ?></h4>
										<div class="ke-card-meta"><?php echo esc_html( ke_get_event_date_display() ); ?></div>
									</a>
								</article>
							<?php endwhile;
							wp_reset_postdata();
						else : ?>
							<p>No other events found at this venue.</p>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>
		</div>

		<aside class="ke-single-sidebar">
			<div class="ke-sidebar-block ke-event-info">
				<h3 class="ke-sidebar-title">Event Info</h3>
				<ul class="ke-info-list">
					<?php if ( $venue_id ) : ?>
						<li>
							<strong>Venue:</strong> 
							<a href="<?php echo esc_url( get_the_permalink( $venue_id ) ); ?>"><?php echo esc_html( get_the_title( $venue_id ) ); ?></a>
						</li>
					<?php endif; ?>

					<?php if ( $organizer ) : ?>
						<li><strong>Organizer:</strong> <?php echo esc_html( $organizer ); ?></li>
					<?php endif; ?>

					<?php if ( $address ) : ?>
						<li><strong>Address:</strong> <?php echo esc_html( $address ); ?></li>
					<?php endif; ?>

					<?php if ( $phone ) : ?>
						<li><strong>Phone:</strong> <?php echo esc_html( $phone ); ?></li>
					<?php endif; ?>

					<?php if ( $official_url ) : ?>
						<li><strong>Official URL:</strong> <a href="<?php echo esc_url( $official_url ); ?>" target="_blank" rel="nofollow">Visit Official Website</a></li>
					<?php endif; ?>

					<?php if ( $source_url ) : ?>
						<li><strong>Source:</strong> <a href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="nofollow">View Source</a></li>
					<?php endif; ?>
				</ul>
			</div>

			<div class="ke-sidebar-block ke-event-categories">
				<h3 class="ke-sidebar-title">Categories</h3>
				<div class="ke-tag-list">
					<?php echo get_the_term_list( $post_id, 'event_category', '', ' ', '' ); ?>
				</div>
			</div>

			<div class="ke-sidebar-block ke-event-location">
				<h3 class="ke-sidebar-title">Location</h3>
				<div class="ke-tag-list">
					<?php echo get_the_term_list( $post_id, 'event_city', '', ' ', '' ); ?>
					<?php echo get_the_term_list( $post_id, 'event_area', '', ' ', '' ); ?>
				</div>
			</div>
		</aside>
	</div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>
