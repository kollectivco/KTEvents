<?php
/**
 * List 2 (Compact) – Very compact horizontal row. Date badge on the left. No image.
 * Great for sidebar widgets, upcoming event feeds.
 */

$event_id   = get_the_ID();
$date       = ke_get_event_date_display( $event_id );
$venue_id   = ke_get_event_meta( $event_id, 'venue_id' );
$venue_name = $venue_id ? get_the_title( $venue_id ) : '';
$categories = get_the_terms( $event_id, 'event_category' );
$cat_name   = ! empty( $categories ) ? $categories[0]->name : '';
?>
<div class="ke-card ke-card-list-2">
	<div class="ke-card-date-badge">
		<span class="ke-date-badge-text"><?php echo esc_html( $date ); ?></span>
	</div>
	<div class="ke-card-content">
		<h4 class="ke-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
		<?php if ( $venue_name || $cat_name ) : ?>
			<div class="ke-card-meta">
				<?php if ( $cat_name ) : ?><span class="ke-card-cat-label"><?php echo esc_html( $cat_name ); ?></span><?php endif; ?>
				<?php if ( $venue_name ) : ?><span class="ke-card-venue"><?php echo esc_html( $venue_name ); ?></span><?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
