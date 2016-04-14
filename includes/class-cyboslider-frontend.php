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
				
				$output .= '<div id="cyboslider-wrapper" style="width: ' . CYBOSLIDER_WIDTH . 'px; height: ' . CYBOSLIDER_HEIGHT . 'px;">';
				
				$output .= '<div id="cyboslider-screen" style="width: ' . CYBOSLIDER_IMAGE_WIDTH . 'px; height: ' . CYBOSLIDER_IMAGE_HEIGHT . 'px;">';
				
				$output .= '<ul id="cyboslider-images-list" style="height: ' . CYBOSLIDER_IMAGE_HEIGHT . 'px;">';
				// the loop - add the images
				while ( $query->have_posts() ) {
					$query->the_post();
					
					$post_id = get_the_ID();
					
					$slide = get_post_meta( $post_id );
					
					$image           = $this->get_the_image( $post_id );
					$title           = get_the_title();
					$link            = empty( $slide[ $prefix . 'link' ][0] ) ? '' : $slide[ $prefix . 'link' ][0];
					$target          = $this->is_link_external( $link ) ? ' target="_blank"' : '';
					$subtitle        = empty( $slide[ $prefix . 'subtitle' ][0] ) ? __( '(No subtitle)', 'cyboslider' ) : $slide[ $prefix . 'subtitle' ][0];
					$mobile_caption  = '<div class="cyboslider-mobile-caption" style="height: ' . CYBOSLIDER_CAPTIONS_HEIGHT . 'px;">'.
					                       '<div class="cyboslider-mobile-caption-wrapper">'.
					                           '<span class="cyboslider-caption-title">' . esc_html( $title ) . '</span>'.
					                           '<span class="cyboslider-caption-subtitle">' . esc_html( $subtitle ) . '</span>'.
					                       '</div>'.
					                   '</div>';
					
					
					$output .= '<li id="cyboslider-image-' . esc_attr( $post_id ) . '" class="cyboslider-image cyboslider-image-' . $x . '" data-cyboslider-item="' . $x . '" style="height: ' . CYBOSLIDER_IMAGE_HEIGHT . 'px;">'.
					               '<a href="' . esc_url( $link ) .'" title="' . esc_attr( $title ) . '"' . esc_attr( $target ) . '>'.
					                   $image.
					                   $mobile_caption .
					               '</a>'.
					           '</li>';
					
					$x++;
				}
				$output .= '</ul>'; // #cyboslider-images-list
				
				$output .= '</div>'; // #cyboslider-screen
				
				rewind_posts();
				$x = 0;
				
				$output .= '<ul id="cyboslider-captions-list" style="height: ' . CYBOSLIDER_IMAGE_HEIGHT . 'px; width: ' . CYBOSLIDER_CAPTIONS_WIDTH . 'px;">';
				// the loop - add the captions
				while ( $query->have_posts() ) {
					$query->the_post();
					
					$post_id = get_the_ID();
					
					$slide = get_post_meta( $post_id );
					
					$title    = get_the_title();
					$subtitle = empty( $slide[ $prefix . 'subtitle' ][0] ) ? __( '(No subtitle)', 'cyboslider' ) : $slide[ $prefix . 'subtitle' ][0];
					$link     = empty( $slide[ $prefix . 'link' ][0] ) ? '' : $slide[ $prefix . 'link' ][0];
					$target   = $this->is_link_external( $link ) ? ' target="_blank"' : '';
					
					$output .= '<li id="cyboslider-caption-' . esc_attr( $post_id ) . '" class="cyboslider-caption cyboslider-caption-' . $x . '" data-cyboslider-item="' . $x . '" style="height: ' . CYBOSLIDER_CAPTIONS_HEIGHT . 'px;">'.
					               '<a href="' . esc_url( $link ) .'" title="' . esc_attr( $title ) . '"' . esc_attr( $target ) . '>'.
					                   '<span class="cyboslider-caption-title">' . esc_html( $title ) . '</span>'.
					                   '<span class="cyboslider-caption-subtitle">' . esc_html( $subtitle ) . '</span>'.
					               '</a>'.
					           '</li>';
					
					$x++;
				}
                    for ( $x = $x; 3 >= $x; $x++ ) {
                         $output .= '<li class="cyboslider-caption-dummy" style="height: ' . CYBOSLIDER_CAPTIONS_HEIGHT . 'px;"></li>';
                    }
				$output .= '</ul>'; // #cyboslider-captions-list
				
				rewind_posts();
				$x = 0;
				
				$output .= '<ul id="cyboslider-mobile-buttons-list">';
				// the loop - add the captions
				while ( $query->have_posts() ) {
					$query->the_post();
					
					$output .= '<li class="cyboslider-mobile-button cyboslider-mobile-button-' . $x . '" data-cyboslider-item="' . $x . '" style="height: ' . CYBOSLIDER_CAPTIONS_HEIGHT . 'px;">'.
					               '<span>' . ( $x + 1 ) . '</span>'.
					           '</li>';
					
					$x++;
				}
                    for ( $x = $x; 4 >= $x; $x++ ) {
                         $output .= '<li class="cyboslider-mobile-button-dummy" style="height: ' . CYBOSLIDER_CAPTIONS_HEIGHT . 'px;"></li>';
                    }
				$output .= '</ul>'; // #cyboslider-mobile-buttons-list
				
				
				
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
                    global $_wp_additional_image_sizes;
				
				$image_size = array( 
					'width'  => $_wp_additional_image_sizes['cyboslider_image']['width'],
					'height' => $_wp_additional_image_sizes['cyboslider_image']['height'],
				);
				$image_url = plugin_dir_url( CYBOSLIDER_PLUGIN_PATH . '/cyboslider.php' ) . 'img/default.jpg';
				return '<img class="attachment-post-thumbnail cyboslider-default-image wp-post-image" width="'.$image_size['width'].'" height="'.$image_size['height'].'" alt="'.__( 'No image found', 'cyboslider' ).'" src="'.$image_url.'">';
			}
		}
		
		/**
		 * checks if a link points to another domain
		 * 
		 * @return    bool    true for another domain,
		 *                    false for the same domain
		 */
		private function is_link_external( $url ) {
			$local_url_components = parse_url( home_url() );
			$local_host = str_replace( 'www.', '', $local_url_components['host'] );
			/**
			 * @author    Ruslan Bes
			 */
			$components = parse_url( $url );
			if ( empty( $components['host'] ) ) {
				return false; // we will treat url like '/relative.php' as relative
			}
			if ( strcasecmp( $components['host'], $local_host ) === 0 ) {
				return false; // url host looks exactly like the local host
			}
			// check if the url host is a subdomain
			return strrpos( strtolower( $components['host'] ), '.'.$local_host ) !== strlen( $components['host']  ) - strlen( '.'.$local_host ); 
		}
	}
}
	