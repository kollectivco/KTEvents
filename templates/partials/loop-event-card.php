<?php
/**
 * Modern Editorial Event Card Partial
 */

$event_id = get_the_ID();
$categories = get_the_terms( $event_id, 'event_category' );
$cat_name = ! empty( $categories ) ? $categories[0]->name : 'Event';
$venue_id = ke_get_event_meta( $event_id, 'venue_id' );
$venue_name = $venue_id ? get_the_title( $venue_id ) : '';
$date = ke_get_event_date_display( $event_id );
$time = ke_get_event_meta( $event_id, 'time' );
$status = ke_get_event_status_label( $event_id );
?>

<div class="ke-card ke-status-<?php echo esc_attr( strtolower( $status ) ); ?>">
	<div class="ke-card-image">
		<a href="<?php the_permalink(); ?>">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'medium_large' ); ?>
			<?php else : ?>
				<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
			<?php endif; ?>
		</a>
		<div class="ke-card-badge ke-status-tag"><?php echo esc_html( $status ); ?></div>
	</div>

	<div class="ke-card-content">
		<div class="ke-card-cat"><?php echo esc_html( $cat_name ); ?></div>
		<h3 class="ke-card-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>
		
		<div class="ke-card-meta">
			<div class="ke-card-date"><?php echo esc_html( $date ); ?></div>
			<?php if ( $venue_name ) : ?>
				<div class="ke-card-venue"><?php echo esc_html( $venue_name ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>
