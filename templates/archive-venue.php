<?php
/**
 * Archive Venues Template - Phase 2 with AJAX support
 */

get_header(); ?>

<div class="ke-container ke-archive-venue" id="ke-archive-container" data-post-type="venue">
	<header class="ke-archive-header">
		<h1 class="ke-archive-title"><?php post_type_archive_title(); ?></h1>
		
		<div class="ke-filters">
			<form id="ke-filter-form" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'venue' ) ); ?>">
				<div class="ke-filter-grid">
					<!-- Search -->
					<div class="ke-filter-item">
						<label for="ke_search">Search Venues</label>
						<input type="text" name="ke_search" id="ke_search" placeholder="Enter keywords..." value="<?php echo esc_attr( $_GET['ke_search'] ?? '' ); ?>">
					</div>

					<!-- City -->
					<div class="ke-filter-item">
						<label for="ke_city">City</label>
						<select name="ke_city" id="ke_city">
							<option value="">All Cities</option>
							<?php 
							$terms = get_terms( array( 'taxonomy' => 'event_city', 'hide_empty' => true ) );
							foreach ( $terms as $term ) : ?>
								<option value="<?php echo $term->slug; ?>" <?php selected( $_GET['ke_city'] ?? '', $term->slug ); ?>><?php echo $term->name; ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Area -->
					<div class="ke-filter-item">
						<label for="ke_area">Area</label>
						<select name="ke_area" id="ke_area">
							<option value="">All Areas</option>
							<?php 
							$terms = get_terms( array( 'taxonomy' => 'event_area', 'hide_empty' => true ) );
							foreach ( $terms as $term ) : ?>
								<option value="<?php echo $term->slug; ?>" <?php selected( $_GET['ke_area'] ?? '', $term->slug ); ?>><?php echo $term->name; ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Sort -->
					<div class="ke-filter-item">
						<label for="ke_sort">Sort By</label>
						<select name="ke_sort" id="ke_sort">
							<option value="title_asc" <?php selected( $_GET['ke_sort'] ?? '', 'title_asc' ); ?>>Title A-Z</option>
							<option value="title_desc" <?php selected( $_GET['ke_sort'] ?? '', 'title_desc' ); ?>>Title Z-A</option>
							<option value="latest" <?php selected( $_GET['ke_sort'] ?? '', 'latest' ); ?>>Latest Added</option>
						</select>
					</div>

					<!-- Buttons -->
					<div class="ke-filter-item ke-filter-actions">
						<div class="ke-buttons">
							<button type="submit" class="ke-submit-btn">Filter</button>
							<button type="button" class="ke-reset-btn" id="ke-reset-filters">Reset</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</header>

	<div id="ke-archive-loop" class="ke-grid">
		<?php
		$query = KE_Query::get_instance()->get_venues();
		echo KE_Query::get_instance()->render_venues_loop( $query );
		?>
	</div>

	<!-- Pagination/Load More -->
	<div class="ke-archive-footer">
		<?php if ( $query->max_num_pages > 1 ) : ?>
			<button type="button" id="ke-load-more" 
					data-current-page="<?php echo $query->query_vars['paged']; ?>" 
					data-max-pages="<?php echo $query->max_num_pages; ?>"
					class="ke-load-more-btn">
				Load More Venues
			</button>
		<?php endif; ?>
		
		<!-- Non-JS Fallback Pagination -->
		<div class="ke-pagination-fallback">
			<?php
			echo paginate_links( array(
				'total'   => $query->max_num_pages,
				'current' => $query->query_vars['paged'],
				'format'  => '?paged=%#%',
			) );
			?>
		</div>
	</div>

	<!-- Loading Overlay -->
	<div id="ke-loading-overlay" class="ke-loading-overlay">
		<div class="ke-spinner"></div>
	</div>
</div>

<?php get_footer(); ?>
