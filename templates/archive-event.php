<?php
/**
 * Archive Events Template - Full Foxiz Integration
 */

get_header(); ?>

<div class="rb-container">
	<div class="rb-section">
		<div class="ke-layout-sidebar">
			
			<main class="ke-main-col">
				<header class="ke-rb-archive-header">
					<h1 class="ke-foxiz-section-title"><?php post_type_archive_title(); ?></h1>
					
					<!-- Foxiz-Style Filter Bar -->
					<div class="ke-filters-bar">
						<form id="ke-filter-form" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>">
							<div class="ke-filter-inner">
								<!-- Search -->
								<div class="ke-filter-node">
									<label>Keywords</label>
									<input type="text" name="ke_search" placeholder="Search events..." value="<?php echo esc_attr( $_GET['ke_search'] ?? '' ); ?>">
								</div>

								<!-- Category -->
								<div class="ke-filter-node">
									<label>Type</label>
									<select name="ke_category">
										<option value="">All Categories</option>
										<?php 
										$terms = get_terms( array( 'taxonomy' => 'event_category', 'hide_empty' => true ) );
										foreach ( $terms as $term ) : ?>
											<option value="<?php echo $term->slug; ?>" <?php selected( $_GET['ke_category'] ?? '', $term->slug ); ?>><?php echo $term->name; ?></option>
										<?php endforeach; ?>
									</select>
								</div>

								<!-- City -->
								<div class="ke-filter-node">
									<label>City</label>
									<select name="ke_city">
										<option value="">All Cities</option>
										<?php 
										$terms = get_terms( array( 'taxonomy' => 'event_city', 'hide_empty' => true ) );
										foreach ( $terms as $term ) : ?>
											<option value="<?php echo $term->slug; ?>" <?php selected( $_GET['ke_city'] ?? '', $term->slug ); ?>><?php echo $term->name; ?></option>
										<?php endforeach; ?>
									</select>
								</div>

								<!-- Sort -->
								<div class="ke-filter-node">
									<label>Order</label>
									<select name="ke_sort">
										<option value="upcoming" <?php selected( $_GET['ke_sort'] ?? '', 'upcoming' ); ?>>Nearest Date</option>
										<option value="latest" <?php selected( $_GET['ke_sort'] ?? '', 'latest' ); ?>>Latest Added</option>
										<option value="date_desc" <?php selected( $_GET['ke_sort'] ?? '', 'date_desc' ); ?>>Newest to Oldest</option>
									</select>
								</div>

								<div class="ke-filter-node">
									<button type="submit" class="button button-primary">Filter</button>
								</div>
							</div>
						</form>
					</div>
				</header>

				<!-- Event Grid -->
				<div id="ke-archive-loop" class="ke-archive-grid-wrapper">
					<?php
					$query = KE_Query::get_instance()->get_events();
					echo KE_Query::get_instance()->render_events_loop( $query, array(
						'columns'        => 3,
						'columns_tablet' => 2,
						'columns_mobile' => 1,
						'gap'            => 'large'
					) );
					?>
				</div>

				<!-- Footer -->
				<div class="ke-archive-footer">
					<?php if ( $query->max_num_pages > 1 ) : ?>
						<button type="button" id="ke-load-more" 
								data-current-page="<?php echo $query->query_vars['paged']; ?>" 
								data-max-pages="<?php echo $query->max_num_pages; ?>"
								class="button button-secondary is-full-width">
							Load More Events
						</button>
					<?php endif; ?>
				</div>

				<!-- Loading Overlay -->
				<div id="ke-loading-overlay" class="ke-loading-overlay">
					<div class="ke-spinner"></div>
				</div>
			</main>

			<!-- Sidebar -->
			<div class="ke-sidebar-col">
				<?php get_sidebar(); ?>
			</div>

		</div>
	</div>
</div>

<?php get_footer(); ?>
