<?php if ( ! defined( 'WPINC' ) ) {
	die;}

add_action( 'template_include', 'w4os_template_include' );
function w4os_template_include( $template ) {
	global $wp_query;
	$plugindir      = dirname( __DIR__ );
	$post_name      = ( isset( $wp_query->queried_object->post_name ) ) ? $wp_query->queried_object->post_name : '';
	$template_slug  = str_replace( '.php', '', basename( $template ) );
	$post_type_slug = get_post_type();
	$custom         = "$plugindir/templates/$template_slug-$post_name.php";
	if ( file_exists( $custom ) ) {
		return $custom;
	}
	return $template;
}

add_filter( 'the_content', 'w4os_the_content' );
function w4os_the_content( $content ) {
	global $wp_query;
	global $template;
	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}
	$plugindir      = dirname( __DIR__ );
	$post_type_slug = get_post_type();
	$post_name      = ( isset( $wp_query->queried_object->post_name ) ) ? $wp_query->queried_object->post_name : '';
	$template_slug  = str_replace( '.php', '', basename( $template ) );
	$custom_slug    = "content-$post_type_slug-$post_name";
	$custom         = "$plugindir/templates/$custom_slug.php";
	if ( file_exists( $custom ) ) {
		if(isset($_GET['et_fb']) && $_GET['et_fb']) {
			$content = '<div class="content">'
			. '<h1>' . __('Format errorr', 'w4os') . '</h1>'
			. __('The pages generated by w4os plugin are not compatible with Divi Builder. Please quit Visual Builder and use standard WordPress editor instead.', 'w4os')
			. '</div>';
		} else {
			ob_start();
			include $custom;
			$custom_content = ob_get_clean();
			$content        = "<br><br><br><div class='" . W4OS_SLUG . " content $template_slug $post_type_slug'>$custom_content</div>";
		}
	}
	$content = wp_cache_get( 'w4os_notices' ) . $content;
	wp_cache_delete( 'w4os_notices' );
	return $content;
}

// ### Interesting 1
// add_filter('the_title', 'w4os_add_after_title', 10, 2);
// function w4os_add_after_title($title, $post_ID) {
// if ( is_single() && is_main_query() && ! is_admin() && ! is_null( $post_ID ) ) {
// if(w4os_backtrace_match('breadcrumb')) return $title;
//
// $post = get_post( $post_ID );
// $title_after = '';
// if ( $post instanceof WP_Post ) {
// switch($post->post_type) {
// case 'bands':
// $title_after .= (w4os_get_option('layout_page_title:genre')) ? w4os_get_meta([ 'tax_genres' ], $post_ID) : '';
// $title_after .= (w4os_get_option('layout_page_title:band_members')) ? w4os_get_meta([ 'members' ], $post_ID) : '';
// if(w4os_get_option('layout_page_title:official_website')) {
// $url = rwmb_meta( 'official_website', array(), $post_ID );
// if($url) {
// $links[] = sprintf("<li class=link><a href='%s'>%s</a></li>", $url, __('Official Website', 'w4os'));
// }
// }
// if(w4os_get_option('layout_page_title:official_store')) {
// $url = rwmb_meta( 'official_store', array(), $post_ID );
// if($url) {
// $links[] = sprintf("<li class=link><a href='%s'>%s</a></li>", $url, __('Official Store', 'w4os'));
// }
// }
// if(!empty($links)) $title_after .= "<ul class=links>" . join(' ', $links) . "</ul>";
// break;
//
// case 'records':
// case 'songs':
// if(w4os_get_option('layout_page_title:band')) {
// if($band_ID = rwmb_meta( 'band', array(), $post_ID )) {
// $band = get_post($band_ID);
// echo "<pre>"; print_r($band); die;
// $title_after .= sprintf(__('by <a href="%s">%s</a>', 'w4os'), get_permalink($band), $band->post_title);
// }
// }
//
// $title_after .= (w4os_get_option('layout_page_title:release_type')) ? w4os_get_meta([ 'release_type' ], $post_ID) : '';
// $title_after .= (w4os_get_option('layout_page_title:release')) ? w4os_get_meta([ 'release' ], $post_ID, [ 'before' => '&#x2117;' ]) : '';
// $title_after .= (w4os_get_option('layout_page_title:authors')) ? w4os_get_meta([ 'authors' ], $post_ID, [ 'before' => '&#169;' ] ) : '';
// $title_after .= (w4os_get_option('layout_page_title:genre')) ? w4os_get_meta([ 'tax_genres' ], $post_ID) : '';
// break;
// }
// }
// if ( is_single() )
// if (is_singular(array('bands')))
// $title= $title . "</h1>-after<h1>";
// }
// $title_before = (!empty($title_before)) ? "</h1><div class='surtitle'>$title_before</div>" : '';
// $title_after = (!empty($title_after)) ? "</h1><div class='subtitle'>$title_after</div>" : '';
// return $title_before . $title . $title_after;
// }
