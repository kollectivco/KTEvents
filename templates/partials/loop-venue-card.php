<?php
/**
 * Loop Venue Card Partial
 *
 * @var WP_Post $post
 * @var array $display (Optional display settings)
 */

if ( ! isset( $post ) ) {
	return;
}

$post_id = $post->ID;
$short_desc = ke_get_venue_meta( $post_id, 'short_description' );
$phone = ke_get_venue_meta( $post_id, 'phone' );
$upcoming_count = ke_count_venue_upcoming_events( $post_id );

$show_image   = $display['show_image'] ?? 'yes';
$show_excerpt = $display['show_excerpt'] ?? 'yes';
$show_meta    = $display['show_meta'] ?? 'yes';
$show_phone   = $display['show_phone'] ?? 'yes';
$show_count   = $display['show_count'] ?? 'yes';

?>

<article class="ke-card ke-venue-card <?php echo ( 'yes' !== $show_image ) ? 'ke-no-image' : ''; ?>" data-id="<?php echo $post_id; ?>">
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
			<h3 class="ke-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		</header>

		<?php if ( 'yes' === $show_excerpt ) : ?>
			<div class="ke-card-excerpt">
				<?php echo wp_trim_words( $short_desc ?: get_the_excerpt(), 20 ); ?>
			</div>
		<?php endif; ?>

		<?php if ( 'yes' === $show_meta || 'yes' === $show_phone || 'yes' === $show_count ) : ?>
			<footer class="ke-card-footer">
				<?php if ( 'yes' === $show_meta ) : 
					$cities = get_the_term_list( $post_id, 'event_city', '', ', ', '' );
					$areas = get_the_term_list( $post_id, 'event_area', '', ', ', '' );
					if ( $cities || $areas ) : ?>
						<div class="ke-location">
							<span class="dashicons dashicons-location"></span> <?php echo $cities ?: ''; ?> <?php echo $areas ? ' / ' . $areas : ''; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( $phone && 'yes' === $show_phone ) : ?>
					<div class="ke-phone">
						<span class="dashicons dashicons-phone"></span> <?php echo esc_html( $phone ); ?>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $show_count ) : ?>
					<div class="ke-upcoming-count">
						<span class="ke-count"><?php echo $upcoming_count; ?></span> فعاليات قادمة
					</div>
				<?php endif; ?>
			</footer>
		<?php endif; ?>
	</div>
</article>
