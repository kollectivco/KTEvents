<?php
/**
 * Single Venue Template
 */

get_header(); ?>

<?php while ( have_posts() ) : the_post(); 
$post_id = get_the_ID();
$arabic_name = ke_get_venue_meta( $post_id, 'arabic_name' );
$english_name = ke_get_venue_meta( $post_id, 'english_name' );
$address = ke_get_venue_meta( $post_id, 'address' );
$phone = ke_get_venue_meta( $post_id, 'phone' );
$website = ke_get_venue_meta( $post_id, 'website' );
$instagram = ke_get_venue_meta( $post_id, 'instagram' );
$map_url = ke_get_venue_meta( $post_id, 'map_url' );
$short_desc = ke_get_venue_meta( $post_id, 'short_description' );
?>

<div class="ke-container ke-single-venue">
	<div class="ke-single-grid">
		<div class="ke-single-content">
			<header class="ke-single-header">
				<div class="ke-single-meta">
					<?php echo get_the_term_list( $post_id, 'event_city', '', ' ', '' ); ?>
					<?php echo get_the_term_list( $post_id, 'event_area', '', ' ', '' ); ?>
				</div>
				<h1 class="ke-single-title"><?php the_title(); ?></h1>
				
				<?php if ( $arabic_name || $english_name ) : ?>
					<div class="ke-alt-names">
						<?php if ( $arabic_name ) : ?><span class="ke-arabic-name"><?php echo esc_html( $arabic_name ); ?></span><?php endif; ?>
						<?php if ( $english_name ) : ?><span class="ke-english-name"><?php echo esc_html( $english_name ); ?></span><?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $short_desc ) : ?>
					<div class="ke-single-excerpt">
						<?php echo wpautop( esc_html( $short_desc ) ); ?>
					</div>
				<?php endif; ?>
			</header>

			<div class="ke-single-image">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'large' ); ?>
				<?php endif; ?>
			</div>

			<div class="ke-single-description">
				<h2 class="ke-section-title">About this Venue</h2>
				<?php the_content(); ?>
			</div>

			<!-- Upcoming Events at this Venue -->
			<section class="ke-venue-events">
				<h2 class="ke-section-title">Upcoming Events at this Venue</h2>
				<div class="ke-grid ke-grid-small">
					<?php 
					$upcoming_events = KE_Query::get_instance()->get_venue_events( $post_id, 'upcoming', -1 );
					if ( $upcoming_events->have_posts() ) :
						while ( $upcoming_events->have_posts() ) : $upcoming_events->the_post(); ?>
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
						<p>No upcoming events found.</p>
					<?php endif; ?>
				</div>
			</section>

			<!-- Past Events at this Venue -->
			<section class="ke-venue-events ke-past-events">
				<h2 class="ke-section-title">Past Events at this Venue</h2>
				<div class="ke-grid ke-grid-small">
					<?php 
					$past_events = KE_Query::get_instance()->get_venue_events( $post_id, 'past', 6 );
					if ( $past_events->have_posts() ) :
						while ( $past_events->have_posts() ) : $past_events->the_post(); ?>
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
						<p>No past events found.</p>
					<?php endif; ?>
				</div>
			</section>
		</div>

		<aside class="ke-single-sidebar">
			<div class="ke-sidebar-block ke-venue-info">
				<h3 class="ke-sidebar-title">Venue Details</h3>
				<ul class="ke-info-list">
					<?php if ( $address ) : ?>
						<li><strong>Address:</strong> <?php echo esc_html( $address ); ?></li>
					<?php endif; ?>

					<?php if ( $phone ) : ?>
						<li><strong>Phone:</strong> <?php echo esc_html( $phone ); ?></li>
					<?php endif; ?>

					<?php if ( $website ) : ?>
						<li><strong>Website:</strong> <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="nofollow">Visit Official Website</a></li>
					<?php endif; ?>

					<?php if ( $instagram ) : ?>
						<li><strong>Instagram:</strong> <a href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="nofollow">Follow on Instagram</a></li>
					<?php endif; ?>

					<?php if ( $map_url ) : ?>
						<li><strong>Location:</strong> <a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="nofollow">View on Google Maps</a></li>
					<?php endif; ?>
				</ul>
			</div>
		</aside>
	</div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>
