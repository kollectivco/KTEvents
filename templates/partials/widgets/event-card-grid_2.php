<?php
/**
 * Grid 2 (Boxed) – Bordered card with shadow, white bg, and inner padding.
 */

$event_id   = get_the_ID();
$categories = get_the_terms( $event_id, 'event_category' );
$cat_name   = ! empty( $categories ) ? $categories[0]->name : '';
$venue_id   = ke_get_event_meta( $event_id, 'venue_id' );
$venue_name = $venue_id ? get_the_title( $venue_id ) : '';
$date       = ke_get_event_date_display( $event_id );
?>
<div class="ke-card ke-card-grid-2">
	<div class="ke-card-image">
		<a href="<?php the_permalink(); ?>" class="ke-card-link-mask" aria-label="<?php the_title_attribute(); ?>"></a>
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium_large' ); ?>
		<?php else : ?>
			<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
		<?php endif; ?>
	</div>
	<div class="ke-card-content">
		<?php if ( $cat_name ) : ?>
			<div class="ke-card-category-badge"><?php echo esc_html( $cat_name ); ?></div>
		<?php endif; ?>
		<h3 class="ke-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<div class="ke-card-meta">
			<?php if ( $date ) : ?>
				<span class="ke-card-date"><?php echo esc_html( $date ); ?></span>
			<?php endif; ?>
			<?php if ( $venue_name ) : ?>
				<span class="ke-card-venue"><?php echo esc_html( $venue_name ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</div>
