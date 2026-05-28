<?php
/**
 * Archive Empty State Partial
 */
?>

<div class="ke-empty-state">
	<div class="ke-empty-icon">
		<span class="dashicons dashicons-calendar"></span>
	</div>
	<h3 class="ke-empty-title"><?php echo esc_html( __( 'No matching results', 'kontentainment-events' ) ); ?></h3>
	<p class="ke-empty-message"><?php echo esc_html( __( 'Try changing filters or search terms to find what you are looking for.', 'kontentainment-events' ) ); ?></p>
	<button type="button" class="ke-reset-btn" id="ke-reset-filters"><?php echo esc_html( __( 'Clear all filters', 'kontentainment-events' ) ); ?></button>
</div>
