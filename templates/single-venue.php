<?php
/**
 * Single Venue Template - Premium Editorial Layout
 */

get_header();

$venue_id     = get_the_ID();
$arabic_name  = ke_get_venue_meta( $venue_id, 'arabic_name' );
$english_name = ke_get_venue_meta( $venue_id, 'english_name' );
$address      = ke_get_venue_meta( $venue_id, 'address' );
$phone        = ke_get_venue_meta( $venue_id, 'phone' );
$website      = ke_get_venue_meta( $venue_id, 'website' );
$instagram    = ke_get_venue_meta( $venue_id, 'instagram' );
$map_url      = ke_get_venue_meta( $venue_id, 'map_url' );
$short_desc   = ke_get_venue_meta( $venue_id, 'short_description' );

// Icons
$icon_location = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>';
$icon_phone    = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>';
$icon_web      = '<svg class="ke-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>';
?>

<div class="ke-frontend-main">
	<div class="rb-container">
		<div class="rb-section">
			<div class="ke-layout-sidebar">
				
				<!-- Main Content Column -->
				<div class="ke-main-col">
					<div class="ke-foxiz-aware">

						<header class="ke-hero-section">
							<div class="ke-hero-upper">
								<div class="ke-single-meta">
									<?php echo get_the_term_list( $venue_id, 'event_governorate', '', ' ', '' ); ?>
									<?php echo get_the_term_list( $venue_id, 'event_city', '', ' ', '' ); ?>
								</div>
								<h1 class="ke-hero-title"><?php the_title(); ?></h1>
								
								<?php if ( $arabic_name || $english_name ) : ?>
									<div class="ke-alt-names">
										<?php if ( $arabic_name ) : ?><span class="ke-arabic-name"><?php echo esc_html( $arabic_name ); ?></span><?php endif; ?>
										<?php if ( $english_name ) : ?><span class="ke-english-name"><?php echo esc_html( $english_name ); ?></span><?php endif; ?>
									</div>
								<?php endif; ?>
							</div>

							<div class="ke-hero-split">
								<div class="ke-hero-poster-col">
									<?php if ( has_post_thumbnail() ) : ?>
										<?php the_post_thumbnail( 'large', [ 'class' => 'ke-poster-image' ] ); ?>
									<?php endif; ?>
								</div>

								<div class="ke-hero-meta-col">
									<div class="ke-meta-stack">
										<?php if ( $address ) : ?>
										<div class="ke-meta-block">
											<div class="ke-meta-icon-box"><?php echo $icon_location; ?></div>
											<div class="ke-meta-info">
												<label><?php echo esc_html( __( 'Address', 'kontentainment-events' ) ); ?></label>
												<strong><?php echo esc_html($address); ?></strong>
											</div>
										</div>
										<?php endif; ?>

										<?php if ( $phone ) : ?>
										<div class="ke-meta-block">
											<div class="ke-meta-icon-box"><?php echo $icon_phone; ?></div>
											<div class="ke-meta-info">
												<label><?php echo esc_html( __( 'Phone', 'kontentainment-events' ) ); ?></label>
												<strong><?php echo esc_html($phone); ?></strong>
											</div>
										</div>
										<?php endif; ?>

										<?php if ( $website ) : ?>
										<div class="ke-meta-block">
											<div class="ke-meta-icon-box"><?php echo $icon_web; ?></div>
											<div class="ke-meta-info">
												<label><?php echo esc_html( __( 'Website', 'kontentainment-events' ) ); ?></label>
												<strong><a href="<?php echo esc_url($website); ?>" target="_blank" rel="nofollow"><?php echo esc_html( __( 'Official Website', 'kontentainment-events' ) ); ?></a></strong>
											</div>
										</div>
										<?php endif; ?>

										<?php if ( $map_url ) : ?>
										<div class="ke-hero-actions" style="margin-top: 20px;">
											<a href="<?php echo esc_url($map_url); ?>" target="_blank" class="ke-register-btn ke-map-btn"><?php echo esc_html( __( 'Get Directions', 'kontentainment-events' ) ); ?></a>
										</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</header>

						<div class="ke-content-body">
							<h2 class="ke-foxiz-section-title"><?php echo esc_html( __( 'About Venue', 'kontentainment-events' ) ); ?></h2>
							<div class="entry-content">
								<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
							</div>
						</div>

						<!-- Upcoming Events -->
						<div class="ke-supporting-block">
							<h2 class="ke-foxiz-section-title"><?php echo esc_html( __( 'Upcoming Events', 'kontentainment-events' ) ); ?></h2>
							<?php
							$upcoming_query = KE_Query::get_instance()->get_venue_events( $venue_id, 'upcoming', 12 );
							if ( $upcoming_query->have_posts() ) :
								echo KE_Query::get_instance()->render_events_loop( $upcoming_query, array(
									'columns'        => 3,
									'columns_tablet' => 2,
									'columns_mobile' => 1,
									'gap'            => 'medium'
								) );
							else :
								echo '<p class="ke-empty-text">' . esc_html( __( 'No upcoming events scheduled at this venue currently.', 'kontentainment-events' ) ) . '</p>';
							endif;
							wp_reset_postdata();
							?>
						</div>

						<!-- Past Events -->
						<div class="ke-supporting-block ke-past-section">
							<h2 class="ke-foxiz-section-title"><?php echo esc_html( __( 'Recent Past Events', 'kontentainment-events' ) ); ?></h2>
							<?php
							$past_query = KE_Query::get_instance()->get_venue_events( $venue_id, 'past', 6 );
							if ( $past_query->have_posts() ) :
								echo KE_Query::get_instance()->render_events_loop( $past_query, array(
									'columns'        => 3,
									'columns_tablet' => 2,
									'columns_mobile' => 1,
									'gap'            => 'medium'
								) );
							endif;
							wp_reset_postdata();
							?>
						</div>

					</div>
				</div>

				<!-- Sidebar -->
				<div class="ke-sidebar-col">
					<div class="ke-sidebar-inner">
						<?php if ( is_active_sidebar( 'ke-events-sidebar' ) ) : ?>
							<?php dynamic_sidebar( 'ke-events-sidebar' ); ?>
						<?php else : ?>
							<?php get_sidebar(); ?>
						<?php endif; ?>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>
