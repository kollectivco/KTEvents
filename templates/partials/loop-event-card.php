<?php
/**
 * Foxiz-Inspired Premium Event Card Partial
 */

$event_id   = get_the_ID();
$categories = get_the_terms( $event_id, 'event_category' );
$cat_name   = ! empty( $categories ) ? $categories[0]->name : 'Event';
$venue_id   = ke_get_event_meta( $event_id, 'venue_id' );
$venue_name = $venue_id ? get_the_title( $venue_id ) : '';
$date       = ke_get_event_date_display( $event_id );
$time       = ke_get_event_meta( $event_id, 'time' );
$status     = ke_get_event_status_label( $event_id );

$icon_calendar = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>';
$icon_venue    = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>';
?>

<div class="ke-event-card-rb ke-status-<?php echo esc_attr( strtolower( $status ) ); ?>">
	<div class="ke-rb-img">
		<a href="<?php the_permalink(); ?>">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'medium_large' ); ?>
			<?php else : ?>
				<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
			<?php endif; ?>
		</a>
	</div>

	<div class="ke-rb-body">
		<div class="ke-card-cat"><?php echo esc_html( $cat_name ); ?></div>
		<h3 class="ke-card-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>
		
		<div class="ke-rb-meta-footer">
			<div class="ke-rb-meta-node">
				<?php echo $icon_calendar; ?>
				<span class="ke-card-date"><?php echo esc_html( $date ); ?></span>
			</div>
			<?php if ( $venue_name ) : ?>
				<div class="ke-rb-meta-node">
					<?php echo $icon_venue; ?>
					<span class="ke-card-venue"><?php echo esc_html( $venue_name ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
