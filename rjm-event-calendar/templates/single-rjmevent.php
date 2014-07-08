<?php
/**
 * The Template for displaying a single post with the post type rjmevent
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>
			<?php
				// Custom velden pakken
				$custom 	 = get_post_custom(get_the_ID());
				$start_datum = $custom["events_startdate"][0];
				$eind_datum  = $custom["events_enddate"][0];
				
				// Time format
				$time_format = get_option('time_format');
				$stime = date($time_format, $start_datum);
				$etime = date($time_format, $eind_datum);
				
				$event_date  = date("d-m-Y", $start_datum);
				$event_dag 	 = date("j", $start_datum);
  			$event_maand = date("F", $start_datum);
  			$event_jaar  = date("Y", $start_datum);
  			
  			$time = $event_dag.' '.$event_maand.' '.$event_jaar.', '.$stime . ' - ' . $etime;
  		?>
				
				<header class="entry-header">
				  <h1 class="entry-title"><?php the_title(); ?></h1>
				  <div class="comments-link">
					  <?php echo $time; ?>
				  </div>
				</header>
				<div class="entry-content">
				  <?php the_content(); ?>
				</div>
				
				<nav class="nav-single">
					<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
					<span class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'twentytwelve' ) . '</span> %title' ); ?></span>
					<span class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'twentytwelve' ) . '</span>' ); ?></span>
				</nav><!-- .nav-single -->

			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>