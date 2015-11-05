<?php

// Usage: [hide_me ids="" category_name="" order="DESC|ASC" orderby="none|ID|author|title|name|type|date|modified|parent|rand|comment_count" post_type="post|page|revision|attachment|nav_menu_item" posts_per_page="" offset="" tag="" taxonomy="" tax_term="" tax_operator="IN|NOT IN|AND" post_parent="" class=""]
function hide_me_shortcode($atts, $content){
	extract(shortcode_atts(array(
		'ids'				=> '',
		'category_name'		=> '',
		'order'				=> 'DESC',
		'orderby'			=> 'date',
		'post_type'			=> 'post',
		'posts_per_page'	=> '',
		'offset'			=> '1',
		'tag'				=> '',
		'taxonomy'			=> '',
		'tax_term'			=> '',
		'tax_operator'		=> 'IN',
		'post_parent'		=> '',
		'class'				=> ''
	), $atts));


	$args = array(
		'category_name'  => $category_name,
		'order'          => $order,
		'orderby'        => $orderby,
		'post_type'      => explode( ',', $post_type ),
		'posts_per_page' => $posts_per_page,
		'offset'         => $offset,
		'tag'            => $tag,
	);

	if( $ids ) {
		$posts_in = array_map( 'intval', explode( ',', $ids ) );
		$args['post__in'] = $posts_in;
	}
	if ( !empty( $taxonomy ) && !empty( $tax_term ) ) {
		$tax_term = explode( ', ', $tax_term );
		if( !in_array( $tax_operator, array( 'IN', 'NOT IN', 'AND' ) ) ){
			$tax_operator = 'IN';
		}
		$tax_args = array(
			'tax_query' => array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $tax_term,
					'operator' => $tax_operator
				)
			)
		);
		$args = array_merge( $args, $tax_args );
	}
	if( $post_parent ) {
		if( 'current' == $post_parent ) {
			global $post;
			$post_parent = $post->ID;
		}
		$args['post_parent'] = $post_parent;
	}

	$posts = new WP_Query( $args );


	if ( ! $posts->have_posts() ){
		return esc_html__('No posts here', 'hide_me');
	}

	$output = '';
	while ( $posts->have_posts() ): $posts->the_post();
		global $post;

		$thumbnail = get_the_post_thumbnail($post->ID,'thumbnail');
		$hasthumb_class = ($thumbnail!='') ? ' has_thumbnail' : ' without_thumbnail';
		$comments_number = get_comments_number();

		$output .= '<div class="hide_me_posts'.esc_attr($hasthumb_class).' '.esc_attr($class).'">';
			$output .= ($thumbnail!='') ? '<div class="hide_me_thumbnail"><span class="overlay"></span>' . get_the_post_thumbnail($post->ID, 'full') . '</div>' :'';
			$output .= '<div class="hide_me_post_content">';
			$output .= '<h5><a href="' . esc_url(get_permalink()) . '">' . get_the_title() . '</a></h5>';
			$date = get_the_date();
			$output .= '<p class="hide_me_time"><span class="day">'.get_the_date('d').'</span> <span class="month">'.get_the_date('M').'</span> / <span class="hide_me_comments">' . esc_attr(sprintf( _n('%s Comment', '%s Comments', $comments_number, 'hide_me'), $comments_number ) ).'</span></p>';
			$output .= '<p>' . get_the_excerpt() . '</p>';
			$output .= '<a href="' . esc_url(get_permalink()) . '" class="hide_me_readmore">'.__('Read More', 'hide_me').'</a>';
			$output .= '</div>';
		$output .= '</div>';
	endwhile;
	wp_reset_postdata();
	return $output;

}

add_shortcode( 'hide_me', 'hide_me_shortcode');

add_action( 'wp_enqueue_scripts', 'hide_me_scripts' );

function hide_me_scripts() {
	wp_enqueue_script( 'hide_me_init', plugins_url().'/hide_me/js/init.js', array());
	wp_enqueue_style('hide_me_stlye', plugins_url().'/hide_me/css/style.css');
}

