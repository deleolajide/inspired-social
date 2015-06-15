<?php if($image_attributes): ?>

	<a href="<?php echo $image_attributes[0];?>" alt="<?php echo $title;?>" title="<?php echo $title;?>">

		<img src="<?php echo $image_attributes[0]; ?>" width="133" height="100">

	</a>

<?php else : ?>

	<a href="<?php echo admin_url();?>/post.php?post=<?php echo $post->ID;?>&action=edit#postimagediv" alt="<?php echo _e('Not Set', 'wp-helpers');?>" title="<?php echo _e('Set Featured Image', 'wp-helpers');?>">

		<?php _e('Not Set', 'wp-helpers'); ?>

	</a>

<?php endif;