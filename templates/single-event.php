<?php
/**
 * Single Event Template - Theme-Independent Premium Design
 */

// If the theme header is the hang, we bypass it but keep essential WP heads
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
	<style>
		/* High-End Reset & Typography */
		:root {
			--ke-primary-bg: #ffffff;
			--ke-text-main: #111827;
			--ke-text-muted: #6b7280;
			--ke-accent: #3b82f6;
			--ke-radius: 24px;
		}
		
		body.ke-minimal-mode {
			margin: 0;
			padding: 0;
			background: var(--ke-primary-bg);
			font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			color: var(--ke-text-main);
			line-height: 1.5;
		}

		.ke-container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 60px 40px;
		}

		/* Typography from Image */
		.ke-eyebrow {
			text-transform: uppercase;
			font-size: 13px;
			font-weight: 800;
			letter-spacing: 0.1em;
			color: var(--ke-text-main);
			margin-bottom: 20px;
		}

		.ke-title {
			font-size: 64px;
			font-weight: 900;
			margin: 0 0 50px 0;
			letter-spacing: -0.02em;
			line-height: 1.1;
		}

		/* Main Layout Split */
		.ke-hero-split {
			display: flex;
			gap: 80px;
			align-items: flex-start;
		}

		.ke-poster-image-wrap {
			flex: 1.2;
		}

		.ke-poster-image-wrap img {
			width: 100%;
			height: auto;
			border-radius: var(--ke-radius);
			box-shadow: 0 30px 60px -12px rgba(50,50,93,0.25), 0 18px 36px -18px rgba(0,0,0,0.3);
			display: block;
		}

		.ke-details-side {
			flex: 1;
			display: flex;
			flex-direction: column;
			gap: 40px;
			padding-top: 20px;
		}

		/* Meta Items from Image */
		.ke-meta-node {
			display: flex;
			gap: 25px;
			align-items: center;
		}

		.ke-meta-icon {
			width: 54px;
			height: 54px;
			background: #f3f4f6;
			border-radius: 16px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #4b5563;
			flex-shrink: 0;
		}

		.ke-meta-icon svg {
			width: 24px;
			height: 24px;
		}

		.ke-meta-info {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		.ke-meta-label {
			font-size: 15px;
			font-weight: 800;
			color: var(--ke-text-main);
		}

		.ke-meta-value {
			font-size: 16px;
			color: var(--ke-text-muted);
			font-weight: 500;
		}

		.ke-meta-value strong {
			color: #374151;
		}

		/* Responsiveness */
		@media (max-width: 1024px) {
			.ke-hero-split { flex-direction: column; gap: 40px; }
			.ke-title { font-size: 40px; }
			.ke-container { padding: 40px 20px; }
		}
	</style>
</head>
<body class="ke-minimal-mode">

<?php
$event_id    = get_the_ID();
$event_date  = get_post_meta( $event_id, 'KE_event_date', true );
$event_time  = get_post_meta( $event_id, 'KE_event_time', true );
$venue_id    = get_post_meta( $event_id, 'KE_event_venue_id', true );
$phone       = $venue_id ? get_post_meta( $venue_id, 'KE_venue_phone', true ) : 'N/A';
$venue_name  = $venue_id ? get_the_title( $venue_id ) : 'TBA';
$venue_addr  = $venue_id ? get_post_meta( $venue_id, 'KE_venue_address', true ) : '';

$categories = get_the_terms( $event_id, 'event_category' );
$cat_name = ! empty( $categories ) ? $categories[0]->name : 'Event';

// Icons
$icon_calendar = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
$icon_clock = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
$icon_venue = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"></path><path d="M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7H3z"></path><path d="M5 21V7"></path><path d="M19 21V7"></path><path d="M9 21v-4a2 2 0 0 1 4 0v4"></path></svg>';
$icon_phone = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
?>

<div class="ke-container">
	
	<!-- Header Section -->
	<div class="ke-eyebrow"><?php echo esc_html($cat_name); ?></div>
	<h1 class="ke-title"><?php the_title(); ?></h1>

	<!-- Main Grid -->
	<div class="ke-hero-split">
		
		<!-- Left: Image -->
		<div class="ke-poster-image-wrap">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large' ); ?>
			<?php else : ?>
				<img src="<?php echo KE_PLUGIN_URL . 'assets/images/event-placeholder.jpg'; ?>" alt="<?php the_title(); ?>">
			<?php endif; ?>
		</div>

		<!-- Right: Details -->
		<div class="ke-details-side">
			
			<!-- Date -->
			<div class="ke-meta-node">
				<div class="ke-meta-icon"><?php echo $icon_calendar; ?></div>
				<div class="ke-meta-info">
					<div class="ke-meta-label">Date</div>
					<div class="ke-meta-value"><?php echo $event_date ? date_i18n( 'd F Y', strtotime( $event_date ) ) : 'TBA'; ?></div>
				</div>
			</div>

			<!-- Time -->
			<div class="ke-meta-node">
				<div class="ke-meta-icon"><?php echo $icon_clock; ?></div>
				<div class="ke-meta-info">
					<div class="ke-meta-label">Time</div>
					<div class="ke-meta-value"><?php echo $event_time ?: 'TBA'; ?></div>
				</div>
			</div>

			<!-- Venue -->
			<div class="ke-meta-node">
				<div class="ke-meta-icon"><?php echo $icon_venue; ?></div>
				<div class="ke-meta-info">
					<div class="ke-meta-label">Venue</div>
					<div class="ke-meta-value"><strong><?php echo esc_html($venue_name); ?></strong></div>
					<?php if ( $venue_addr ) : ?>
						<div class="ke-meta-value" style="font-size: 14px; opacity: 0.7;"><?php echo esc_html($venue_addr); ?></div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Phone -->
			<div class="ke-meta-node">
				<div class="ke-meta-icon"><?php echo $icon_phone; ?></div>
				<div class="ke-meta-info">
					<div class="ke-meta-label">Phone</div>
					<div class="ke-meta-value"><?php echo esc_html($phone); ?></div>
				</div>
			</div>

		</div>

	</div>

	<!-- Description Section -->
	<?php if ( get_the_content() ) : ?>
		<div class="ke-description-section" style="margin-top: 80px; max-width: 800px;">
			<h2 style="font-size: 28px; margin-bottom: 30px;">About the Event</h2>
			<div class="ke-content-rich">
				<?php 
				while ( have_posts() ) {
					the_post();
					the_content();
				}
				?>
			</div>
		</div>
	<?php endif; ?>

</div>

<?php wp_footer(); ?>
</body>
</html>
