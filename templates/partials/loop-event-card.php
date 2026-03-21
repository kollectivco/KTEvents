<?php
/**
 * Kontentainment Premium Shared Event Card Partial
 * This is the master card template used across archives and related sections.
 */

$event_id   = get_the_ID();
$categories = get_the_terms( $event_id, 'event_category' );
$cat_name   = ! empty( $categories ) ? $categories[0]->name : 'Event';
$venue_id   = ke_get_event_meta( $event_id, 'venue_id' );
$venue_name = $venue_id ? get_the_title( $venue_id ) : '';
$date       = ke_get_event_date_display( $event_id );
$time       = ke_get_event_meta( $event_id, 'time' );
$status     = ke_get_event_status_label( $event_id );

// Modern Editorial Icons
$icon_calendar = '<svg class="ke-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>';
$icon_venue    = '<svg class="ke-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>';
?>

<div class="ke-event-card-rb ke-status-managed-<?php echo esc_attr( strtolower( $status ) ); ?>">
	
	<!-- Top Section: Visual Focus -->
	<div class="ke-rb-img">
		<a href="<?php the_permalink(); ?>" class="ke-card-link-mask"></a>
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium_large' ); ?>
		<?php else : ?>
			<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
		<?php endif; ?>
		
		<div class="ke-status-badge"><?php echo esc_html( $status ); ?></div>
	</div>

	<!-- Bottom Content Area -->
	<div class="ke-rb-body">
		<div class="ke-card-cat"><?php echo esc_html( $cat_name ); ?></div>
		
		<h3 class="ke-card-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>
		
		<div class="ke-rb-meta-footer">
			<div class="ke-rb-meta-node">
				<span class="ke-meta-icon"><?php echo $icon_calendar; ?></span>
				<span class="ke-card-date"><?php echo esc_html( $date ); ?></span>
			</div>
			
			<?php if ( $venue_name ) : ?>
				<div class="ke-rb-meta-node">
					<span class="ke-meta-icon"><?php echo $icon_venue; ?></span>
					<span class="ke-card-venue"><?php echo esc_html( $venue_name ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
