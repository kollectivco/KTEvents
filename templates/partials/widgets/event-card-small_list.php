<?php
/**
 * Small List / Minimal – Very small thumbnail (square), one-liner title, date only.
 * Ideal for tight sidebar feeds or "next 5 events" blocks.
 */

$event_id   = get_the_ID();
$date       = ke_get_event_date_display( $event_id );
$venue_id   = ke_get_event_meta( $event_id, 'venue_id' );
$venue_name = $venue_id ? get_the_title( $venue_id ) : '';
?>
<div class="ke-card ke-card-small-list">
	<div class="ke-card-thumb-sq">
		<a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'thumbnail' ); ?>
			<?php else : ?>
				<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
			<?php endif; ?>
		</a>
	</div>
	<div class="ke-card-content">
		<h4 class="ke-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
		<div class="ke-card-meta">
			<?php if ( $date ) : ?><span class="ke-card-date"><?php echo esc_html( $date ); ?></span><?php endif; ?>
			<?php if ( $venue_name ) : ?><span class="ke-card-venue"><?php echo esc_html( $venue_name ); ?></span><?php endif; ?>
		</div>
	</div>
</div>
