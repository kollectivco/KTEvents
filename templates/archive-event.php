<?php
/**
 * Archive Events Template - Phase 2 with AJAX support
 */

get_header(); ?>

<div class="ke-container ke-archive-event" id="ke-archive-container" data-post-type="event">
	<header class="ke-archive-header">
		<h1 class="ke-archive-title"><?php post_type_archive_title(); ?></h1>
		
		<div class="ke-filters">
			<form id="ke-filter-form" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>">
				<div class="ke-filter-grid">
					<!-- Search -->
					<div class="ke-filter-item">
						<label for="ke_search">Search</label>
						<input type="text" name="ke_search" id="ke_search" placeholder="Enter keywords..." value="<?php echo esc_attr( $_GET['ke_search'] ?? '' ); ?>">
					</div>

					<!-- Category -->
					<div class="ke-filter-item">
						<label for="ke_category">Category</label>
						<select name="ke_category" id="ke_category">
							<option value="">All Categories</option>
							<?php 
							$terms = get_terms( array( 'taxonomy' => 'event_category', 'hide_empty' => true ) );
							foreach ( $terms as $term ) : ?>
								<option value="<?php echo $term->slug; ?>" <?php selected( $_GET['ke_category'] ?? '', $term->slug ); ?>><?php echo $term->name; ?></option>
							<?php endforeach; ?>
						</select>
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

					<!-- Status -->
					<div class="ke-filter-item">
						<label for="ke_status">Status</label>
						<select name="ke_status" id="ke_status">
							<option value="">All Statuses</option>
							<option value="upcoming" <?php selected( $_GET['ke_status'] ?? '', 'upcoming' ); ?>>Upcoming</option>
							<option value="ongoing" <?php selected( $_GET['ke_status'] ?? '', 'ongoing' ); ?>>Ongoing</option>
							<option value="past" <?php selected( $_GET['ke_status'] ?? '', 'past' ); ?>>Past</option>
							<option value="cancelled" <?php selected( $_GET['ke_status'] ?? '', 'cancelled' ); ?>>Cancelled</option>
						</select>
					</div>

					<!-- Sort -->
					<div class="ke-filter-item">
						<label for="ke_sort">Sort By</label>
						<select name="ke_sort" id="ke_sort">
							<option value="upcoming" <?php selected( $_GET['ke_sort'] ?? '', 'upcoming' ); ?>>Nearest Date</option>
							<option value="latest" <?php selected( $_GET['ke_sort'] ?? '', 'latest' ); ?>>Latest Added</option>
							<option value="date_asc" <?php selected( $_GET['ke_sort'] ?? '', 'date_asc' ); ?>>Date (Asc)</option>
							<option value="date_desc" <?php selected( $_GET['ke_sort'] ?? '', 'date_desc' ); ?>>Date (Desc)</option>
							<option value="title_asc" <?php selected( $_GET['ke_sort'] ?? '', 'title_asc' ); ?>>Title A-Z</option>
							<option value="title_desc" <?php selected( $_GET['ke_sort'] ?? '', 'title_desc' ); ?>>Title Z-A</option>
						</select>
					</div>

					<!-- Toggles & Buttons -->
					<div class="ke-filter-item ke-filter-actions">
						<label class="ke-featured-toggle">
							<input type="checkbox" name="ke_featured" value="1" <?php checked( $_GET['ke_featured'] ?? '', '1' ); ?>> Featured Only
						</label>
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
		$query = KE_Query::get_instance()->get_events();
		echo KE_Query::get_instance()->render_events_loop( $query );
		?>
	</div>

	<!-- Pagination/Load More -->
	<div class="ke-archive-footer">
		<?php if ( $query->max_num_pages > 1 ) : ?>
			<button type="button" id="ke-load-more" 
					data-current-page="<?php echo $query->query_vars['paged']; ?>" 
					data-max-pages="<?php echo $query->max_num_pages; ?>"
					class="ke-load-more-btn">
				Load More Events
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
