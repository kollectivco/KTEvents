<?php
/**
 * Flex Overlay – Full-bleed image with gradient overlay. 
 * Redesigned to ensure text is truly ON the image.
 */

$event_id   = get_the_ID();
$categories = get_the_terms( $event_id, 'event_category' );
$cat_name   = ! empty( $categories ) ? $categories[0]->name : '';
$date       = ke_get_event_date_display( $event_id );
$venue_id   = ke_get_event_meta( $event_id, 'venue_id' );
$venue_name = $venue_id ? get_the_title( $venue_id ) : '';
?>
<div class="ke-card ke-card-overlay">
	<a href="<?php the_permalink(); ?>" class="ke-card-link-mask" aria-label="<?php the_title_attribute(); ?>"></a>
	
	<div class="ke-card-bg-image">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'large' ); ?>
		<?php else : ?>
			<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
		<?php endif; ?>
	</div>
	
	<div class="ke-card-overlay-gradient"></div>

	<div class="ke-card-content">
		<?php if ( $cat_name ) : ?>
			<p class="ke-card-cat-eyebrow"><?php echo esc_html( strtoupper( $cat_name ) ); ?></p>
		<?php endif; ?>
		
		<h3 class="ke-card-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<div class="ke-card-meta">
			<?php if ( $date ) : ?>
				<div class="ke-rb-meta-node">
					<span class="ke-meta-icon"><?php ke_get_svg_icon('calendar'); ?></span>
					<span><?php echo esc_html( $date ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( $venue_name ) : ?>
				<div class="ke-rb-meta-node">
					<span class="ke-meta-icon"><?php ke_get_svg_icon('map-pin'); ?></span>
					<span><?php echo esc_html( $venue_name ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
