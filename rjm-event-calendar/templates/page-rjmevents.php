<?php
/*
Template Name: RJM events
*/
?>
<?php get_header(); ?>
	<div id="primary" class="site-content">
		<div id="content" role="main">
		  
    <?php
      // Don't show items before 6:00 am
    	$from = strtotime('today 6:00') + ( get_option( 'gmt_offset' ) * 3600 );
    	
    	// Set limit to 20 items
    	$limit = 20;
    	
    	// Set description max characters
    	$desc_max_char = 300;
    	
    	// Query
    	global $wpdb;
    	$querystr = "
        SELECT *
        FROM $wpdb->posts wposts, $wpdb->postmeta metastart, $wpdb->postmeta metaend
        WHERE (wposts.ID = metastart.post_id AND wposts.ID = metaend.post_id)
        AND (metaend.meta_key = 'events_enddate' AND metaend.meta_value > $from )
        AND metastart.meta_key = 'events_startdate'
        AND wposts.post_type = 'rjmevent'
        AND wposts.post_status = 'publish'
        ORDER BY metastart.meta_value ASC LIMIT $limit";
    	
    	$events = $wpdb->get_results($querystr, OBJECT);
    	
    	// Start loop
    	if ($events){
    		global $post;
    		echo '<div class="agenda">';
    		
    		foreach ($events as $post){
    			setup_postdata($post);
    			
    			// Pak de custom velden begin en eind datum
    			$custom 	   = get_post_custom(get_the_ID());
    			$start_datum = $custom["events_startdate"][0];
    			$eind_datum  = $custom["events_enddate"][0];
    			
    			// Check of het een nieuwe dag is
    			$event_date  = date("d-m-Y", $start_datum);
    			$event_dag 	 = date("j", $start_datum);
    			$event_maand = date("F", $start_datum);
    			$event_jaar  = date("Y", $start_datum);
    			
    			// Local time format
    			$time_format = get_option('time_format');
    			$stime       = date($time_format, $start_datum);
    			$etime       = date($time_format, $eind_datum);
    			
    			// Output
    			?>
    			<article>
  				  <header class="entry-header">
  				    <h1 class="entry-title">
  				      <a href="<?php echo get_permalink($post_ID); ?>" rel="bookmark"><?php the_title(); ?></a>
  				    </h1>
  						<div class="comments-link">
    						<?php echo $event_dag.' '.$event_maand.' '.$event_jaar.', '.$stime . ' - ' . $etime; ?>
    				  </div><!-- .comments-link -->
  					</header><!-- .entry-header -->
  
  					<div class="entry-content">
  					  <p><?php if (strlen($post->post_content) > $desc_max_char) { echo substr($post->post_content, 0, $desc_max_char) . '...'; } else { echo $post->post_content; } ?></p>
  					</div><!-- .entry-content -->
  		
  					<footer class="entry-meta">
    					<a href="<?php echo get_permalink($post_ID); ?>">Read more</a>
    				</footer><!-- .entry-meta -->
    		  </article>
    			<?php
    		} // Einde foreach
    		echo '</div>';
    	} // End loop
    ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>