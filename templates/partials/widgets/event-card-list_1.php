<?php
/**
 * Modern Editorial List 1 Card Preset for Elementor (Horizontal)
 */

$event_id   = get_the_ID();
$categories = get_the_terms( $event_id, 'event_category' );
$cat_name   = ! empty( $categories ) ? $categories[0]->name : 'Event';
$venue_id   = ke_get_event_meta( $event_id, 'venue_id' );
$venue_name = $venue_id ? get_the_title( $venue_id ) : '';
$date       = ke_get_event_date_display( $event_id );
?>

<div class="ke-card-list ke-horizontal ke-list-1">
	<div class="ke-card-image">
		<a href="<?php the_permalink(); ?>">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'medium' ); ?>
			<?php else : ?>
				<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
			<?php endif; ?>
		</a>
		<div class="ke-card-badge ke-status-tag-sm"><?php echo ke_get_event_status_label(); ?></div>
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

<style>
.ke-card-list.ke-horizontal { display: grid; grid-template-columns: 180px 1fr; gap: 24px; align-items: center; border-bottom: 1px solid var(--ke-border); padding-bottom: 24px; margin-bottom: 24px; }
.ke-card-list.ke-horizontal .ke-card-image { aspect-ratio: 4/3; border-radius: var(--ke-radius-md); overflow: hidden; }
.ke-card-list.ke-horizontal .ke-card-image img { width: 100%; height: 100%; object-fit: cover; }
.ke-card-list.ke-horizontal .ke-card-title { font-size: 18px; line-height: 1.3; }
.ke-card-list.ke-horizontal .ke-card-meta { border-top: 0; padding-top: 0; flex-direction: row; gap: 12px; }
@media (max-width: 768px) { .ke-card-list.ke-horizontal { grid-template-columns: 120px 1fr; gap: 16px; } }
</style>
