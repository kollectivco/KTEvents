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
		
		<div class="ke-card-category-badge"><?php echo esc_html( $cat_name ); ?></div>
	</div>

	<!-- Bottom Content Area -->
	<div class="ke-rb-body">
		
		<h3 class="ke-card-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>
		
		<div class="ke-rb-meta-footer">
			<div class="ke-rb-meta-node">
				<span class="ke-meta-icon"><?php ke_get_svg_icon('calendar'); ?></span>
				<span class="ke-card-date"><?php echo esc_html( $date ); ?></span>
			</div>
			
			<?php if ( $venue_name ) : ?>
				<div class="ke-rb-meta-node">
					<span class="ke-meta-icon"><?php ke_get_svg_icon('map-pin'); ?></span>
					<span class="ke-card-venue">
						<?php echo esc_html( $venue_name ); ?>
					</span>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
