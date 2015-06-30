<?php

//if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();


require_once( dirname( __FILE__ ) . '/settings.php' );


$cyboslider_uninstall = new Cyboslider_Uninstall();
$cyboslider_uninstall->uninstall();

if( ! class_exists( 'Cyboslider_Uninstall' ) ) {

	class Cyboslider_Uninstall extends Cyboslider_Settings {
		
		/**
		 * uninstall the plugin
		 */
		public function uninstall() {
			if ( is_multisite() ) {
				
				// delete network wide options
				foreach ( $this->network_options as $option_name ) {
					delete_site_option( $option_name );
				}
				
				global $wpdb;
				
				// delete network wide tables
				foreach ( $this->network_tables as $table_name ) {
					$wpdb->query( "DROP TABLE IF EXISTS $table_name");
				}
				
				
				// delete individual blog settings and tables 
				$blogs_list = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
				if ( ! empty( $blogs_list ) ) {
					foreach ($blogs_list as $blog) {
						switch_to_blog($blog['blog_id']);
						uninstall_on_single_blog();
						restore_current_blog();
					}
				}
				
			} else {
				
				uninstall_on_single_blog();
				
			}
		}
		
		/**
		 * remove the tables and settings for each blog
		 */
		private function uninstall_on_single_blog() {
			
			foreach ( $this->single_blog_options as $option_name ) {
				delete_option( $option_name );
			}
			
			global $wpdb;
			
			foreach ( $this->single_blog_tables as $table_name ) {
				$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $table_name );
			}
			
		}
	
	}
}
