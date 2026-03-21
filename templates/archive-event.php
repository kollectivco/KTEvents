<?php
/**
 * Archive Events Template - Full Foxiz Integration
 */

get_header(); ?>

<div class="ke-frontend-main">
	<div class="rb-container">
		<div class="rb-section">
			<div class="ke-layout-sidebar">
				
				<main class="ke-main-col">
					<header class="ke-rb-archive-header">
						<h1 class="ke-foxiz-section-title"><?php post_type_archive_title(); ?></h1>
						
						<!-- Foxiz-Style Dual-Layer Filter Bar -->
						<div class="ke-filters-bar">
							<form id="ke-filter-form" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>">
								
								<!-- Layer 1: Navigation Tabs -->
								<div class="ke-quick-nav">
									<div class="ke-nav-scroll">
										<button type="button" class="ke-nav-item <?php echo empty($_GET['ke_range']) && empty($_GET['ke_recommended']) ? 'active' : ''; ?>" data-range="">Upcoming</button>
										<button type="button" class="ke-nav-item <?php echo ($_GET['ke_recommended'] ?? '') === '1' ? 'active' : ''; ?>" data-meta="ke_recommended">Recommended</button>
										<button type="button" class="ke-nav-item <?php echo ($_GET['ke_range'] ?? '') === 'today' ? 'active' : ''; ?>" data-range="today">Today</button>
										<button type="button" class="ke-nav-item <?php echo ($_GET['ke_range'] ?? '') === 'weekend' ? 'active' : ''; ?>" data-range="weekend">Weekend</button>
										<button type="button" class="ke-nav-item <?php echo ($_GET['ke_range'] ?? '') === 'week' ? 'active' : ''; ?>" data-range="week">This Week</button>
										<button type="button" class="ke-nav-toggle-btn" id="ke-toggle-advanced">Get Specific <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
									</div>
								</div>

								<!-- Layer 2: Dynamic Location Pills -->
								<div class="ke-location-pills">
									<div class="ke-pills-scroll">
										<button type="button" class="ke-pill-item <?php echo empty($_GET['ke_city']) ? 'active' : ''; ?>" data-city="">All Events</button>
										<?php 
										$cities = get_terms( array( 'taxonomy' => 'event_city', 'hide_empty' => true ) );
										foreach ( $cities as $city ) : ?>
											<button type="button" class="ke-pill-item <?php echo ($_GET['ke_city'] ?? '') === $city->slug ? 'active' : ''; ?>" data-city="<?php echo esc_attr($city->slug); ?>">
												<?php echo esc_html($city->name); ?>
											</button>
										<?php endforeach; ?>
									</div>
								</div>

								<!-- Hidden Inputs for Quick Filters -->
								<input type="hidden" name="ke_range" id="ke-input-range" value="<?php echo esc_attr($_GET['ke_range'] ?? ''); ?>">
								<input type="hidden" name="ke_recommended" id="ke-input-recommended" value="<?php echo esc_attr($_GET['ke_recommended'] ?? ''); ?>">
								<input type="hidden" name="ke_city" id="ke-input-city" value="<?php echo esc_attr($_GET['ke_city'] ?? ''); ?>">

								<!-- Level 3: Advanced Filter Form (Collapsible) -->
								<div id="ke-advanced-filters" class="ke-filter-inner" style="display: none;">
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
											$categories = get_terms( array( 'taxonomy' => 'event_category', 'hide_empty' => true ) );
											foreach ( $categories as $cat ) : ?>
												<option value="<?php echo $cat->slug; ?>" <?php selected( $_GET['ke_category'] ?? '', $cat->slug ); ?>><?php echo $cat->name; ?></option>
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
										<button type="submit" class="button button-primary is-full-width">Apply Filters</button>
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

				<!-- Sidebar Column -->
				<?php if ( is_active_sidebar( 'ke-events-sidebar' ) ) : ?>
					<div class="ke-sidebar-col">
						<div class="ke-sidebar-inner">
							<?php dynamic_sidebar( 'ke-events-sidebar' ); ?>
						</div>
					</div>
				<?php else : ?>
					<div class="ke-sidebar-col">
						<div class="ke-sidebar-inner">
							<?php get_sidebar(); ?>
						</div>
					</div>
				<?php endif; ?>

			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>
