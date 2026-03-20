<?php
/**
 * Event Card - Flex Overlay Preset
 * Text on image
 */

$post_id     = get_the_ID();
$event_date  = ke_get_event_date_display( $post_id );
$venue_id    = ke_get_event_meta( $post_id, 'venue_id' );

$show_image = $display['show_image'] ?? 'yes';
$ratio      = $display['image_ratio'] ?? '1-1';

?>

<article class="ke-card ke-event-card-flex-overlay ke-ratio-<?php echo esc_attr($ratio); ?>" data-id="<?php echo $post_id; ?>">
	<?php if ( 'yes' === $show_image && has_post_thumbnail() ) : ?>
		<div class="ke-card-image">
			<a href="<?php the_permalink(); ?>" class="ke-img-link">
				<?php the_post_thumbnail( 'medium_large' ); ?>
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
	</div>
</article>
<style>
.ke-event-card-flex-overlay {
    position: relative;
    overflow: hidden;
    color: #fff;
}
.ke-event-card-flex-overlay .ke-card-image::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
}
.ke-event-card-flex-overlay .ke-card-content {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 20px;
}
.ke-event-card-flex-overlay .ke-card-title a {
    color: #fff;
}
</style>
