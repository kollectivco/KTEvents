<?php
/**
 * Loop Event Card Partial
 *
 * @var WP_Post $post
 * @var array $display (Optional display settings)
 */

if ( ! isset( $post ) ) {
	return;
}

$post_id = $post->ID;
$venue_id = ke_get_event_meta( $post_id, 'venue_id' );
$event_date = ke_get_event_date_display( $post_id );
$event_time = ke_get_event_meta( $post_id, 'time' );
$is_featured = ke_get_event_meta( $post_id, 'featured' );
$status = ke_get_event_meta( $post_id, 'status' );

// Display settings with defaults
$show_image   = $display['show_image'] ?? 'yes';
$show_excerpt = $display['show_excerpt'] ?? 'yes';
$show_date    = $display['show_date'] ?? 'yes';
$show_time    = $display['show_time'] ?? 'yes';
$show_venue   = $display['show_venue'] ?? 'yes';
$show_meta    = $display['show_meta'] ?? 'yes';
$show_badge   = $display['show_badge'] ?? 'yes';
$excerpt_len  = $display['excerpt_length'] ?? 20;

?>

<article class="ke-card ke-event-card <?php echo $is_featured ? 'ke-is-featured' : ''; ?> <?php echo ( 'yes' !== $show_image ) ? 'ke-no-image' : ''; ?>" data-id="<?php echo $post_id; ?>">
	<?php if ( $is_featured && 'yes' === $show_badge ) : ?>
		<span class="ke-badge ke-badge-featured">Featured</span>
	<?php endif; ?>

	<?php if ( 'yes' === $show_image ) : ?>
		<div class="ke-card-image">
			<a href="<?php the_permalink(); ?>">
				<?php if ( has_post_thumbnail( $post_id ) ) : ?>
					<?php echo get_the_post_thumbnail( $post_id, 'medium_large' ); ?>
				<?php else : ?>
					<div class="ke-placeholder"></div>
				<?php endif; ?>
			</a>
		</div>
	<?php endif; ?>

	<div class="ke-card-content">
		<header class="ke-card-header">
			<?php if ( 'yes' === $show_date || 'yes' === $show_time ) : ?>
				<div class="ke-card-meta">
					<?php if ( 'yes' === $show_date ) : ?>
						<span class="ke-date"><?php echo esc_html( $event_date ); ?></span>
					<?php endif; ?>
					<?php if ( 'yes' === $show_time && $event_time ) : ?>
						<span class="ke-time"><?php echo esc_html( $event_time ); ?></span>
					<?php endif; ?>
					<span class="ke-status-dot ke-status-<?php echo esc_attr( $status ?: 'upcoming' ); ?>" title="<?php echo esc_attr( ucfirst( $status ?: 'upcoming' ) ); ?>"></span>
				</div>
			<?php endif; ?>
			<h3 class="ke-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		</header>

		<?php if ( 'yes' === $show_excerpt ) : ?>
			<div class="ke-card-excerpt">
				<?php 
				$excerpt = get_the_excerpt();
				echo wp_trim_words( $excerpt, $excerpt_len );
				?>
			</div>
		<?php endif; ?>

		<?php if ( 'yes' === $show_venue || 'yes' === $show_meta ) : ?>
			<footer class="ke-card-footer">
				<?php if ( $venue_id && 'yes' === $show_venue ) : ?>
					<div class="ke-venue-name">
						<span class="dashicons dashicons-location"></span> <?php echo esc_html( get_the_title( $venue_id ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $show_meta ) : 
					$cities = get_the_term_list( $post_id, 'event_city', '', ', ', '' );
					$areas = get_the_term_list( $post_id, 'event_area', '', ', ', '' );
					if ( $cities || $areas ) : ?>
						<div class="ke-location">
							<?php echo $cities ?: ''; ?> <?php echo $areas ? ' / ' . $areas : ''; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</footer>
		<?php endif; ?>
	</div>
</article>
