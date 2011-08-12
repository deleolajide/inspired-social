<?php

	// To use this sub-template, just add "do_action( 'bp_album_all_images' ); " to any template on your site

	global $bp;
	$args = array();

	$args['owner_id'] = false;	// Do *not* change this
	$args['id'] = false;		// Do *not* change this
	$args['page']=1;		// Do *not* change this
	$args['max']=false;		// Do *not* change this
	$args['privacy']='public';	// Do *not* change this
	$args['priv_override']=false;	// Do *not* change this
	$args['groupby']=false;		// Do *not* change this

	$args['per_page']=24;
	$args['ordersort']='DESC';
	$args['orderkey']='id';		// Can also use 'random' to shuffle images, but will make site much slower
	$images_per_row = 5;


	// STEP 1: Run the query.
	bp_album_query_pictures($args);

	$row_count = 0;

	// STEP 2: Test if items were found. If so, open the CSS block.
	if ( bp_album_has_pictures() ) : ?>

	    <div class="bpa-content-wrap" id="default">

		<table class="bpa-content-sitewide">
		    <tr>

		    <?php
		    // STEP 3: Iterate through the items the query has found, printing each one out.
		    while ( bp_album_has_pictures() ) : bp_album_the_picture(); ?>

			<td>
				<a href="<?php bp_album_picture_url() ?>" class="media-image"><img width="32" src='<?php bp_album_picture_thumb_url() ?>' /></a>
			</td>
		    <?php

		    $row_count++;

		    if($row_count == $images_per_row){
			echo '</tr><tr>';
			$row_count = 0;
		    }

		    endwhile;


		 // STEP 4: Close the CSS block.   ?>
		    </tr>
		</table>
	    </div><?php

	 endif;

?>
