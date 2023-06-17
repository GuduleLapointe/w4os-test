<?php
add_filter(
	'body_class',
	function ( array $classes ) {
		$classes[] = 'w4os-splash w4os-welcome';
		return $classes;
	}
);

?><!doctype html>
<html <?php language_attributes(); ?>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<div id="content" class="site-content">
			<div id="primary" class="content-area">
				<main id="main" class="site-main w4os-splash splash" role="main">
					<?php
					/* Start the Loop */
					while ( have_posts() ) :
						the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<div class="entry-content">
								<?php the_content(); ?>
							</div><!-- .entry-content -->

						</article><!-- #post-<?php the_ID(); ?> -->
						<?php
					endwhile; // End of the loop.
					?>
				</div><!-- #main -->
			</div><!-- #primary -->
		</div><!-- #content -->
	</div><!-- #page -->

</body>
</html>
