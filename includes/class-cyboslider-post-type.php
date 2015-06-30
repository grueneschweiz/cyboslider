<?php

/**
 * lock out script kiddies: die an direct call 
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'Cyboslider_Post_Type' ) ) {
	
	/**
	 * contains the methodes to register the cyboslider post type
	 * adds the needed metaboxes to the post type
	 * modifies the overview table to suit the post type
	 * 
	 * @uses    the Meta Box plugin. see http://metabox.io/
	 */
	class Cyboslider_Post_Type {
		
		/**
		 * registers the custom post type
		 */
		public function register_post_type() {
			//register post type
			$labels = array( 
				'name'               => __( 'Slides', 'cyboslider' ),
				'singular_name'      => __( 'Slide', 'cyboslider' ),
				'add_new'            => __( 'Add New Slide', 'cyboslider' ),
				'add_new_item'       => __( 'Add New', 'cyboslider' ),
				'edit_item'          => __( 'Edit Slide', 'cyboslider' ),
				'new_item'           => __( 'New Slide', 'cyboslider' ),
				'view_item'          => __( 'View Slide', 'cyboslider' ),
				'search_items'       => __( 'Search Slide', 'cyboslider' ),
				'not_found'          => __( 'Not found any Slides', 'cyboslider' ),
				'not_found_in_trash' => __( 'No Slide found in Trash', 'cyboslider' ),
				'parent_item_colon'  => __( 'Parent Slide:', 'cyboslider' ),
				'menu_name'          => __( 'Slider', 'cyboslider' ),
			);
			/*
			$capabilities = array(
				'publish_posts'      => 'cyboslider_edit_slider',
				'edit_posts'         => 'cyboslider_edit_slider',
				'edit_others_posts'  => 'cyboslider_edit_slider',
				'read_private_posts' => 'cyboslider_edit_slider',
				'edit_post'          => 'cyboslider_edit_slider',
				'delete_post'        => 'cyboslider_edit_slider',
				//'delete_posts'=> 'cyboslider_edit_slider',
				'read_post'          => 'cyboslider_edit_slider',
			);
			*/
			
			$args = array(
				'label'               => __( 'Slider', 'cyboslider' ),
				'labels'              => $labels,
				'hierarchical'        => false,
				'supports'            => array( 'title' ),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'can_export'          => true,
				'capability_type'     => 'page', //'cyboslider_edit_slider',
				//'capabilities'        => $capabilities,
				'menu_icon'           => 'dashicons-images-alt2',
				'rewrite'             => false,
			);
			register_post_type( 'cyboslider', $args );
			
		}
		
		
		/**
		 * Add meta boxes to the cyboslider post type
		 * 
		 * @uses    the Meta Box plugin.
		 * @see     http://metabox.io/
		 */
		public function add_meta_boxes( $meta_boxes ) {
			$prefix = CYBOSLIDER_PLUGIN_PREFIX;
			
			$meta_boxes[] = array(
				'id'         => $prefix . 'slide_details',
				'title'      => __( 'Slide details', 'cyboslider' ),
				'pages'      => array( 'cyboslider' ),
				'context'    => 'normal',
				'priority'   => 'high',
				'autosave'   => true,
				'fields'     => array(
					array(
						'name' => __( 'Subtitle','cyboslider' ),
						'desc' => __( 'The text below the title.', 'cyboslider' ),
						'id'   => $prefix . 'subtitle',
						'type' => 'text',
						'std'  => __( '(No subtitle)', 'cyboslider' ),
						'size' => 60,
					),
					array(
						'name' => __( 'Link','cyboslider' ),
						'desc' => __( 'Link to the corresponding article (internal or external).', 'cyboslider' ),
						'id'   => $prefix . 'link',
						'type' => 'text',
						'std'  => '',
					),
					array(
						'name' => __('Slide','cyboslider' ),
						'desc' => __('Please upload slide','politch' ),
						'id'   => $prefix . 'slide',
						'type' => 'image_advanced',
						'std'  => ''
					),
				)
			);
			
			return $meta_boxes;
		}
		
		/**
		 * register the custom post overview 
		 */
		public function register_overview() {
			add_filter( 'manage_cyboslider_posts_columns', array( &$this, 'overview_columns_head' ) );
			add_action( 'manage_cyboslider_posts_custom_column', array( &$this,'overview_columns_content' ), 10, 2 );
		}
		
		/**
		 * display a description for our custom column in the overview table
		 * 
		 * @access    must be public, else wp can't call the function
		 */
		public function overview_columns_head( $defaults ) {
			$defaults['cyboslider_featured_image'] = __( 'Slide', 'cyboslider' );
			$defaults['cyboslider_post_id']    = __( 'ID', 'cyboslider' );
			return $defaults;
		}
		
		/**
		 * display the slide in the overview table
		 * 
		 * @access    must be public, else wp can't call the function
		 */
		public function overview_columns_content( $column_name, $post_ID ) {
			if ( $column_name == 'cyboslider_featured_image' ) {
				$post_featured_image = $this->get_featured_image( $post_ID );
				if ( $post_featured_image ) {
					echo '<img src="' . $post_featured_image . '" />';
				}
			}
			if ( $column_name == 'cyboslider_post_id' ) {
				echo $post_ID;
			}
		}
		
		/**
		 * get featured image of the cyboslider post type
		 */
		private function get_featured_image( $post_ID ) {
			
			// get post meta
			$post_meta = get_post_meta( $post_ID );
			
			// if a image is defined
			if ( ! empty( $post_meta[ CYBOSLIDER_PLUGIN_PREFIX . 'slide' ][0] ) ) {
				
				$post_thumbnail_id = (int) $post_meta[ CYBOSLIDER_PLUGIN_PREFIX . 'slide' ][0];
				
				$post_thumbnail_img = wp_get_attachment_image_src( $post_thumbnail_id, 'thumbnail' );
				return $post_thumbnail_img[0]; // BREAKPOINT
			}
		}
	}
}