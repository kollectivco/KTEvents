<?php
/**
 * Event Card - Classic Preset
 * @var array $display
 */

$post_id     = get_the_ID();
$is_featured = ke_get_event_meta( $post_id, 'featured' );
$event_date  = ke_get_event_date_display( $post_id );
$event_time  = ke_get_event_meta( $post_id, 'time' );
$venue_id    = ke_get_event_meta( $post_id, 'venue_id' );

$show_image = $display['show_image'] ?? 'yes';
$ratio      = $display['image_ratio'] ?? '16-9';

?>

<article class="ke-card ke-event-card-classic ke-ratio-<?php echo esc_attr($ratio); ?>" data-id="<?php echo $post_id; ?>">
	<?php if ( 'yes' === $show_image && has_post_thumbnail() ) : ?>
		<div class="ke-card-image">
			<a href="<?php the_permalink(); ?>" class="ke-img-link">
				<?php the_post_thumbnail( 'medium_large' ); ?>
			</a>
		</div>
	<?php endif; ?>

	<div class="ke-card-content">
		<header class="ke-card-header">
			<?php if ( 'yes' === ($display['show_meta_date'] ?? 'yes') || 'yes' === ($display['show_meta_time'] ?? 'yes') ) : ?>
				<div class="ke-card-meta">
					<?php if ( 'yes' === ($display['show_meta_date'] ?? 'yes') ) : ?>
						<span class="ke-date"><?php echo esc_html( $event_date ); ?></span>
					<?php endif; ?>
					<?php if ( 'yes' === ($display['show_meta_time'] ?? 'yes') && $event_time ) : ?>
						<span class="ke-time"><?php echo esc_html( $event_time ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<h3 class="ke-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		</header>

		<?php if ( 'yes' === ($display['show_meta_venue'] ?? 'yes') && $venue_id ) : ?>
			<div class="ke-card-footer">
				<span class="ke-venue"><?php echo esc_html( get_the_title( $venue_id ) ); ?></span>
			</div>
		<?php endif; ?>
	</div>
</article>
