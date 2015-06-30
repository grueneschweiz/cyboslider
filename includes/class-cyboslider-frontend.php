<?php

// die on direct call
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


if ( ! class_exists( 'Cyboslider_Frontend' ) ) {

	class Cyboslider_Frontend {
		
		/**
		 * Process short code
		 * 
		 * @var     array    $atts    provided from WP's add_shortcode() function
		 * @return  string            html with the content to be displayed
		 */
		public function process_short_code( $atts ) {
			$error_msg = __( 'Unknown shortcode or invalid arguments.', 'cyboslider' );
			
			return $error_msg;
		}
		
		
		/**
		 * get the slider html
		 * 
		 * @return    string        the slider html
		 */
		public function get_the_slider() {
			$prefix = CYBOSLIDER_PLUGIN_PREFIX;
			$output = '';
			$x      = 0;
			
			// the query
			$query_args = array( 
				'post_type'      => 'cyboslider',
				'post_status'    => 'publish',
				'posts_per_page' => 4,
			);
			$query = new WP_Query( $query_args );
			
			// if have posts
			if ( $query->have_posts() ) {
				
				$output .= '<div id="cyboslider-wrapper">';
				
				$output .= '<div id="cyboslider-screen">';
				
				$output .= '<ul id="cyboslider-images-list">';
				// the loop - add the images
				while ( $query->have_posts() ) {
					$query->the_post();
					
					$post_id = get_the_ID();
					
					$slide = get_post_meta( $post_id );
					
					$image    = $this->get_the_image( $post_id );
					$title    = get_the_title();
					$link     = empty( $slide[ $prefix . 'link' ][0] ) ? '' : $slide[ $prefix . 'link' ][0];
					
					$output .= '<li id="cyboslider-image-' . $post_id . '" class="cyboslider-image cyboslider-image-' . $x . '">'.
					               '<a href="' . $link .'" title="' . $title . '">'.
					                   $image.
					               '</a>'.
					           '</li>';
					
					$x++;
				}
				$output .= '</ul>'; // #cyboslider-images-list
				
				$output .= '</div>'; // #cyboslider-screen
				
				rewind_posts();
				$x = 0;
				
				$output .= '<ul id="cyboslider-captions-list">';
				// the loop - add the captions
				while ( $query->have_posts() ) {
					$query->the_post();
					
					$post_id = get_the_ID();
					
					$slide = get_post_meta( $post_id );
					
					$title    = get_the_title();
					$subtitle = empty( $slide[ $prefix . 'subtitle' ][0] ) ? __( '(No subtitle)', 'cyboslider' ) : $slide[ $prefix . 'subtitle' ][0];
					$link     = empty( $slide[ $prefix . 'link' ][0] ) ? '' : $slide[ $prefix . 'link' ][0];
					
					$output .= '<li id="cyboslider-caption-' . $post_id . '" class="cyboslider-caption cyboslider-caption-' . $x . '" data-cyboslider-item="' .$x . '">'.
					               '<a href="' . $link .'" title="' . $title . '">'.
					                   '<span class="cyboslider-caption-title">' . $title . '</span>'.
					                   '<span class="cyboslider-caption-subtitle">' . $subtitle . '</span>'.
					               '</a>'.
					           '</li>';
					
					$x++;
				}
				$output .= '</ul>'; // #cyboslider-captions-list
				
				$output .= '</div>'; // #cyboslider-wrapper
				
			} else {
				// no posts found
			}
			
			// Restore original Post Data
			wp_reset_postdata();
			
			return $output;
		}
		
		
		/**
		 * get the image or a default one (full html)
		 * 
		 * @var      int    $post_id    the post id of the slide
		 * @return   string             the html
		 */
		private function get_the_image( $post_id ) {
			// get post meta
			$post_meta = get_post_meta( $post_id );
			
			// if a image is defined
			if ( ! empty( $post_meta[ CYBOSLIDER_PLUGIN_PREFIX . 'slide' ][0] ) ) {
				
				// attachment id
				$post_image_id = (int) $post_meta[ CYBOSLIDER_PLUGIN_PREFIX . 'slide' ][0];
				
				// the image
				$post_image_img = wp_get_attachment_image( $post_image_id, 'cyboslider_image' );
				
				return $post_image_img; // BREAKPOINT
			} else {
				// if no image was defined
				// return a default image
				
				$image_size = apply_filters( 
					'cyboslider_image_size', 
					array( 
						'width'  => 570, // also defined in cyboslider.php -> register_image_size()
						'height' => 319  // also defined in cyboslider.php -> register_image_size()
					)
				);
				$image_url = plugin_dir_url( CYBOSLIDER_PLUGIN_PATH . '/cyboslider.php' ) . 'img/default.jpg';
				return '<img class="attachment-post-thumbnail cyboslider-default-image wp-post-image" width="'.$image_size['width'].'" height="'.$image_size['height'].'" alt="'.__( 'No image found', 'cyboslider' ).'" src="'.$image_url.'">';
			}
		}
	}
}
	