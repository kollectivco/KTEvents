<?php
/**
 * Modern Editorial Classic Card Preset for Elementor
 */

$event_id   = get_the_ID();
$categories = get_the_terms( $event_id, 'event_category' );
$cat_name   = ! empty( $categories ) ? $categories[0]->name : 'Event';
$venue_id   = ke_get_event_meta( $event_id, 'venue_id' );
$venue_name = $venue_id ? get_the_title( $venue_id ) : '';
$date       = ke_get_event_date_display( $event_id );
$ratio      = $settings['image_ratio'] ?? '16-9';
?>

<div class="ke-card ke-ratio-<?php echo esc_attr($ratio); ?>">
	<div class="ke-card-image">
		<a href="<?php the_permalink(); ?>">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'medium_large' ); ?>
			<?php else : ?>
				<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
			<?php endif; ?>
		</a>
		<div class="ke-card-badge ke-status-tag"><?php echo ke_get_event_status_label(); ?></div>
	</div>

	<div class="ke-card-content">
		<?php if ( 'yes' === ($settings['meta_category'] ?? 'yes') ) : ?>
			<div class="ke-card-cat"><?php echo esc_html( $cat_name ); ?></div>
		<?php endif; ?>

		<h3 class="ke-card-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>
		
		<div class="ke-card-meta">
			<?php if ( 'yes' === ($settings['meta_date'] ?? 'yes') ) : ?>
				<div class="ke-card-date"><?php echo esc_html( $date ); ?></div>
			<?php endif; ?>
			<?php if ( $venue_name && 'yes' === ($settings['meta_venue'] ?? 'yes') ) : ?>
				<div class="ke-card-venue"><?php echo esc_html( $venue_name ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>
