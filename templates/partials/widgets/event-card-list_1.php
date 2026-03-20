<?php
/**
 * Event Card - List 1 Preset (Horizontal)
 */

$post_id     = get_the_ID();
$is_featured = ke_get_event_meta( $post_id, 'featured' );
$event_date  = ke_get_event_date_display( $post_id );
$venue_id    = ke_get_event_meta( $post_id, 'venue_id' );

$show_image = $display['show_image'] ?? 'yes';
$ratio      = $display['image_ratio'] ?? '4-3';

?>

<article class="ke-card ke-event-card-list-1 ke-ratio-<?php echo esc_attr($ratio); ?>" data-id="<?php echo $post_id; ?>">
	<?php if ( 'yes' === $show_image && has_post_thumbnail() ) : ?>
		<div class="ke-card-image">
			<a href="<?php the_permalink(); ?>" class="ke-img-link">
				<?php the_post_thumbnail( 'medium' ); ?>
			</a>
		</div>
	<?php endif; ?>

	<div class="ke-card-content">
		<header class="ke-card-header">
			<?php if ( 'yes' === ($display['show_meta_date'] ?? 'yes') ) : ?>
				<div class="ke-card-meta">
					<span class="ke-date"><?php echo esc_html( $event_date ); ?></span>
				</div>
			<?php endif; ?>
			<h3 class="ke-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		</header>

		<?php if ( 'yes' === ($display['show_meta_venue'] ?? 'yes') && $venue_id ) : ?>
			<div class="ke-card-footer">
				<span class="ke-venue"><?php echo esc_html( get_the_title( $venue_id ) ); ?></span>
			</div>
		<?php endif; ?>

		<div class="ke-card-excerpt">
			<?php echo wp_trim_words( get_the_excerpt(), 15 ); ?>
		</div>
	</div>
</article>
<style>
.ke-event-card-list-1 {
    display: flex;
    gap: 20px;
    align-items: center;
}
.ke-event-card-list-1 .ke-card-image {
    width: 200px;
    flex-shrink: 0;
}
</style>
