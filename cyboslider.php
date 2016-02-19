<?php

/**
 * Plugin Name: Cyboslider
 * Plugin URI: https://github.com/cyrillbolliger/cyboslider
 * Version: 1.2.0
 * Description: Slider with captions and links, responsive.
 * Author: Cyrill Bolliger
 * Text Domain: cyboslider
 * Domain Path: languages
 * GitHub Plugin URI: cyrillbolliger/cyboslider
 * License: GPL 2.
 */
 
/**
 * Copyright 2015  Cyrill Bolliger  (email : bolliger@gmx.ch)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
 

/**
 * lock out script kiddies: die an direct call 
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * abspath to plugins directory
 */
define( 'CYBOSLIDER_PLUGIN_PATH', dirname( __FILE__ ) );

/**
 * version number (dont forget to change it also in the header)
 */
define( 'CYBOSLIDER_VERSION', '1.2.0' );

/**
 * plugin prefix
 */
define( 'CYBOSLIDER_PLUGIN_PREFIX', 'cyboslider_' );

/**
 * other constants
 */
define( 'CYBOSLIDER_WIDTH', 970 );
define( 'CYBOSLIDER_HEIGHT', 339 );
define( 'CYBOSLIDER_IMAGE_WIDTH', 570 );
define( 'CYBOSLIDER_IMAGE_HEIGHT', 319 );
define( 'CYBOSLIDER_CAPTIONS_WIDTH', 380 );
define( 'CYBOSLIDER_CAPTIONS_HEIGHT', 79 );
define( 'CYBOSLIDER_INTERMEDIATE_WIDTH', 669 );
 
/**
 * load settings class
 */
require_once( CYBOSLIDER_PLUGIN_PATH . '/includes/class-cyboslider-settings.php' );


if ( ! class_exists( 'Cyboslider_Main' ) ) {
	
	class Cyboslider_Main extends Cyboslider_Settings {
		
		/*
		 * Construct the plugin object
		 */
		public function __construct() {
			parent::__construct();
			
			register_activation_hook( __FILE__, array( &$this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
			
			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'init', array( &$this, 'fe_init' ) );
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'admin_menu', array( &$this, 'add_menu' ) );
			add_action( 'plugins_loaded', array( &$this, 'i18n' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'load_resources' ) );
			add_action( 'wp_print_scripts', array( &$this, 'load_inline_resources' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'load_resources' ) );
			add_action( 'tgmpa_register', array( &$this, 'register_required_plugins' ) );
			add_action( 'after_setup_theme', array( &$this, 'register_image_size' ) );
               add_filter( 'post_row_actions', array( &$this, 'remove_quickedit_link' ) );
               add_filter( 'post_updated_messages', array( &$this, 'tweak_post_update_messages_links' ) );
		}
		
		/**
		 * Activate the plugin
		 */
		public function activate() {
			$this->add_roles_on_plugin_activation();
			$this->add_capabilities_on_plugin_activation();
			$this->create_tables_on_plugin_activation();
		}
		
		/**
		 * Deactivate the plugin
		 */
		public function deactivate() {
			$this->remove_capabilities_on_plugin_deactivation();
			$this->remove_roles_on_plugin_deactivation();
		}
		
		/**
		 * Hook into WP's init action hook.
		 */
		public function init() {
			$this->load_custom_post_type();
		}
		
		/**
		 * Hook into WP's init action hook for frontend pages
		 */
		public function fe_init() {
			if ( ! is_admin() ) {
				add_shortcode( 'cyboslider', array( &$this, 'short_code_handler' ) );
			}
		}
		
		/**
		 * Hook into WP's admin_init action hook
		 */
		public function admin_init() {
			$this->init_settings();
			$this->load_tgm_plugin_activation_class();
		}
		
		/**
		 * Initialize some custom settings
		 */
		public function init_settings() {
			
		}
		
		/**
		 * Add a menu
		 */
		public function add_menu() {
			
		}

		/**
		 * Menu Callback
		 */
		public function plugin_settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'cyboslider' ) );
			}
			
			// Render the settings template
			//include( sprintf ( "%s/templates/settings.php", dirname( __FILE__ ) ) );
		}
		
		/**
		 * I18n.
		 * Put the translation in the languages folder in the plugins directory
		 * Name the translation files like "nameofplugin-lanugage_COUUNTRY.po". Ex: "cyboslider-fr_FR.po"
		 */
		public function i18n() {
			$path = dirname( plugin_basename(__FILE__) ) . '/languages';
			load_plugin_textdomain( 'cyboslider', false, $path );
		}
		
		/**
		 * Add roles on plugin activation
		 */
		public function add_roles_on_plugin_activation() {
			if ( is_multisite() ) {
				global $wpdb;
				$blogs_list = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
				if ( ! empty( $blogs_list ) ) {
					foreach ($blogs_list as $blog) {
						switch_to_blog($blog['blog_id']);
						$this->add_roles_for_sigle_blog();
						restore_current_blog();
					}
				}
			} else {
				$this->add_roles_for_sigle_blog();
			}
		}
		
		/**
		 * actually adds the roles
		 */
		private function add_roles_for_sigle_blog() {
			foreach( $this->roles as $role ) {
				add_role( $role[0], $role[1], $role[2] );
			}
		}
		
		/**
		 * Remove roles on plugin deactivation
		 */
		public function remove_roles_on_plugin_deactivation() {
			if ( is_multisite() ) {
				global $wpdb;
				$blogs_list = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
				if ( ! empty( $blogs_list ) ) {
					foreach ($blogs_list as $blog) {
						switch_to_blog($blog['blog_id']);
						$this->remove_roles_for_sigle_blog();
						restore_current_blog();
					}
				}
			} else {
				$this->remove_roles_for_sigle_blog();
			}
		}
		
		/**
		 * actually removes the roles
		 */
		private function remove_roles_for_sigle_blog() {
			foreach( $this->roles as $role ) {
				remove_role( $role[0] );
			}
		}
		
		/**
		 * Add capabilities on plugin activation
		 */
		public function add_capabilities_on_plugin_activation() {
			if ( is_multisite() ) {
				global $wpdb;
				$blogs_list = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
				if ( ! empty( $blogs_list ) ) {
					foreach ($blogs_list as $blog) {
						switch_to_blog($blog['blog_id']);
						$this->add_capabilities_for_single_blog();
						restore_current_blog();
					}
				}
			} else {
				$this->add_capabilities_for_single_blog();
			}
		}
		
		
		/**
		 * Actually add capabilities
		 */
		private function add_capabilities_for_single_blog() {
			$capabilities = array(
				'cyboslider_edit_slider',
			); 
			$this->add_plugin_capabilities_for( 'editor', $capabilities[0] );
			$this->add_plugin_capabilities_for( 'administrator' , $capabilities );
		}
		
		
		/**
		 * Remove capabilities on plugin deactivation
		 */
		public function remove_capabilities_on_plugin_deactivation() {
			if ( is_multisite() ) {
				global $wpdb;
				$blogs_list = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
				if ( ! empty( $blogs_list ) ) {
					foreach ($blogs_list as $blog) {
						switch_to_blog($blog['blog_id']);
						$this->remove_capabilities_for_single_blog();
						restore_current_blog();
					}
				}
			} else {
				$this->remove_capabilities_for_single_blog();
			}
		}
		
		
		/**
		 * Actually remove capabilities
		 */
		private function remove_capabilities_for_single_blog() {
			$capabilities = array(
				'cyboslider_edit_slider',
			); 
			$this->remove_plugin_capabilities_for( 'editor', $capabilities[0] );
			$this->remove_plugin_capabilities_for( 'administrator' , $capabilities );
			
		}
		
		/**
		 * Add capabilities
		 * 
		 * @var string			$role_name		subject
		 * @var string|array 	$capabilities	caps to add
		 */
		public function add_plugin_capabilities_for( $role_name, $capabilities ) {
			$role = get_role( $role_name );
			foreach ( (array) $capabilities as $capability ) {
				$role->add_cap( $capability );
			}
		}
		
		/**
		 * Remove capabilities
		 * 
		 * @var string			$role_name		subject
		 * @var string|array 	$capabilities	caps to remove
		 */
		public function remove_plugin_capabilities_for( $role_name, $capabilities ) {
			$role = get_role( $role_name );
			foreach ( (array) $capabilities as $capability ) {
				$role->remove_cap( $capability );
			}
		}
		
		/**
		 * Add tables on plugin activation if they dont exist yet
		 */
		public function create_tables_on_plugin_activation() {
			// dont forget to check if tables dont exist yet
			// dont forget to use $this->network_tables and $this->single_blog_tables (with $wpdb->prefix) as table names
		}
		
		/**
		 * handle short code
		 * 
		 * @var		array	$atts	provided from WP's add_shortcode() function
		 * @return	string
		 */
		public function short_code_handler( $atts ) {
			$frontend = new Cyboslider_Frontend();
			return $frontend->process_short_code( $atts );
		}
		
		/**
		 * load ressources (js, css)
		 */
		public function load_resources() {
			
			foreach ( $this->styles as $style ) {
				if ( is_admin() && $style['scope'] == ( 'admin' || 'shared' ) ) {
					if ( ! wp_style_is( $style['handle'], 'enqueued' ) ) {
						$this->register_style( $style );
						wp_enqueue_style( $style['handle'] );
					}
				}
				if ( ! is_admin() && $style['scope'] == ( 'frontend' || 'shared' ) ) {
					if ( ! wp_style_is( $style['handle'], 'enqueued' ) ) {
						$this->register_style( $style );
						wp_enqueue_style( $style['handle'] );
					}
				}
			}
			
			foreach ( $this->scripts as $script ) {
				if ( is_admin() && $script['scope'] == ( 'admin' || 'shared' ) ) {
					if ( ! wp_script_is( $script['handle'], 'enqueued' ) ) {
						$this->register_script( $script );
						wp_enqueue_script( $script['handle'] );
					}
				}
				if ( ! is_admin() && $script['scope'] == ( 'frontend' || 'shared' ) ) {
					if ( ! wp_script_is( $script['handle'], 'enqueued' ) ) {
						$this->register_script( $script );
						wp_enqueue_script( $script['handle'] );
					}
				}
			}
		}
		
		/**
		 * load inline ressources
		 */
		public function load_inline_resources() {
			$constants = array(
				'width'            => CYBOSLIDER_WIDTH,
				'height'           => CYBOSLIDER_HEIGHT,
				'imageWidth'       => CYBOSLIDER_IMAGE_WIDTH,
				'imageHeight'      => CYBOSLIDER_IMAGE_HEIGHT,
				'captionsWidth'    => CYBOSLIDER_CAPTIONS_WIDTH,
				'captionsHeight'   => CYBOSLIDER_CAPTIONS_HEIGHT,
				'intermediateWidth'=> CYBOSLIDER_INTERMEDIATE_WIDTH,
			);
			wp_localize_script( 'cyboslider-frontend-js', 'cybosliderConst', $constants );
		}
		
		/**
		 * register script
		 * 
		 * @var array 	$script		for params see __construct in Cyboslider_Settings
		 */
		public function register_script( $script ) {
			wp_register_script( 
				$script['handle'],
				plugins_url( $script['src'], __FILE__ ),
				$script['deps'],
				CYBOSLIDER_VERSION,
				$script['in_footer']
			);
		}
		
		/**
		 * register style
		 * 
		 * @var array 	$style		for params see __construct in Cyboslider_Settings
		 */
		public function register_style( $style ) {
			wp_register_style(
				$style['handle'],
				plugins_url( $style['src'], __FILE__ ),
				$style['deps'],
				CYBOSLIDER_VERSION,
				$style['media']
			);
		}
		
		
		/**
		 * Load TGM plugin 
		 *
		 * Will only be loaded for single site blogs (MU isn't supportet yet. Check https://github.com/TGMPA/TGM-Plugin-Activation for
		 * more information. Most problably in v3 it will be supported.)
		 *
		 * @package    WP Team Manager Extended
		 * @package    TGM-Plugin-Activation
		 * @uses       /vendor/class-tgm-plugin-activation.php
		 * @link       https://github.com/thomasgriffin/TGM-Plugin-Activation
		 * 
		 * @todo       update the TGM Plugin and remove the 'if not is_multisite()' condition as soon as the TGM Plugin supports MU.
		 * 
		 */
		private function load_tgm_plugin_activation_class() {
			/**
			 * exit if multisite. The TGM Plugin doesent support MU blogs yet.
			 * 
			 * @todo   update the TGM Plugin and remove the 'if not is_multisite()' condition as soon as the TGM Plugin supports MU.
			 */
			if ( is_multisite() ) {
				return; // BREAKPOINT
			}
			
			// Include the TGM_Plugin_Activation class.
			require_once( CYBOSLIDER_PLUGIN_PATH . '/vendor/class-tgm-plugin-activation.php' );
		}
		
		
		/**
		 * Register the required plugins for this plugin.
		 *
		 * The variable passed to tgmpa_register_plugins() should be an array of plugin
		 * arrays.
		 *
		 * This function is hooked into tgmpa_init, which is fired within the
		 * TGM_Plugin_Activation class constructor.
		 * 
		 * @package    TGM-Plugin-Activation
		 * @uses       /vendor/class-tgm-plugin-activation.php
		 * @link       https://github.com/thomasgriffin/TGM-Plugin-Activation
		 * 
		 * @todo       update the TGM Plugin and remove the 'if not is_multisite()' condition as soon as the TGM Plugin supports MU.
		 */
		private function register_required_plugins() {
			/**
			 * exit if multisite. The TGM Plugin doesent support MU blogs yet.
			 * 
			 * @todo   update the TGM Plugin and remove the 'if not is_multisite()' condition as soon as the TGM Plugin supports MU.
			 */
			if ( is_multisite() ) {
				return; // BREAKPOINT
			}
			
			/**
			 * Array of plugin arrays. Required keys are name and slug.
			 * If the source is NOT from the .org repo, then source is also required.
			 */
			$plugins = array(
				// REQUIRED PLUGIN from Github to allow automatic Updates of the Theme itself, that is hosted on github
				array(
					'name'               => 'GitHub updater', // The plugin name.
					'slug'               => 'github-updater', // The plugin slug (typically the folder name).
					'source'             => get_stylesheet_directory() . '/vendor/plugins/github-updater.zip', // The plugin source.
					'required'           => true, // If false, the plugin is only 'recommended' instead of required.
					'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
					'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
					'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
					'external_url'       => '', // If set, overrides default API URL and points to an external URL.
				),
				// REQUIRED PLUGINS from the WordPress Plugin Repository.
				array(
					'name'               => 'Meta Box',
					'slug'               => 'meta-box',
					'required'           => true,
					'force_activation'   => true,
					'force_deactivation' => false,
				),
				array(
					'name'               => 'Meta Box Text Limiter',
					'slug'               => 'meta-box-text-limiter',
					'required'           => true,
					'force_activation'   => true,
					'force_deactivation' => false,
				),
			);
			
			/**
			 * Array of configuration settings. Amend each line as needed.
			 * If you want the default strings to be available under your own theme domain,
			 * leave the strings uncommented.
			 * Some of the strings are added into a sprintf, so see the comments at the
			 * end of each line for what each argument will be.
			 */
			$config = array(
				'default_path' => '',                      // Default absolute path to pre-packaged plugins.
				'menu'         => 'tgmpa-install-plugins', // Menu slug.
				'has_notices'  => true,                    // Show admin notices or not.
				'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
				'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
				'is_automatic' => true,                   // Automatically activate plugins after installation or not.
				'message'      => '',                      // Message to output right before the plugins table.
				'strings'      => array(
					'page_title'                      => __( 'Install Required Plugins', 'cyboslider' ),
					'menu_title'                      => __( 'Install Plugins', 'cyboslider' ),
					'installing'                      => __( 'Installing Plugin: %s', 'cyboslider' ), // %s = plugin name.
					'oops'                            => __( 'Something went wrong with the plugin API.', 'cyboslider' ),
					'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'cyboslider' ), // %1$s = plugin name(s).
					'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'cyboslider' ), // %1$s = plugin name(s).
					'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'cyboslider' ), // %1$s = plugin name(s).
					'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'cyboslider' ), // %1$s = plugin name(s).
					'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'cyboslider' ), // %1$s = plugin name(s).
					'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'cyboslider' ), // %1$s = plugin name(s).
					'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'cyboslider' ), // %1$s = plugin name(s).
					'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'cyboslider' ), // %1$s = plugin name(s).
					'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'cyboslider' ),
					'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'cyboslider' ),
					'return'                          => __( 'Return to Required Plugins Installer', 'cyboslider' ),
					'plugin_activated'                => __( 'Plugin activated successfully.', 'cyboslider' ),
					'complete'                        => __( 'All plugins installed and activated successfully. %s', 'cyboslider' ), // %s = dashboard link.
					'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
				)
			);
			
			tgmpa( $plugins, $config );
		
		}
		
		/**
		 * load the plugin specific post type
		 */
		private function load_custom_post_type() {
			// load cyboslider post type
			require_once( CYBOSLIDER_PLUGIN_PATH . '/includes/class-cyboslider-post-type.php' );
			$post_type = new Cyboslider_Post_Type();
			
			// register post type and taxonomy
			$post_type->register_post_type();
			
			// add meta boxes
			add_filter( 'rwmb_meta_boxes', array( $post_type, 'add_meta_boxes' ) );
			
			// register custom overview
			$post_type->register_overview();
		}
		
		/**
		 * Add image size for slider images
		 * 
		 * @see https://codex.wordpress.org/Function_Reference/add_image_size
		 */
		public function register_image_size() {
			$image_size = apply_filters( 
				'cyboslider_image_size',
				array( 
					'width'  => 570,
					'height' => 319,
				)
			);
			
			add_image_size( 'cyboslider_image', $image_size['width'], $image_size['height'], array( 'center', 'center' ) );
		}
          
          /**
           * Remove the quick edit link in the posts table
           * 
           * @since 1.1.4
           */
          public function remove_quickedit_link( $action ) {
               if ( 'cyboslider' == get_post_type() ) {
                    unset( $action['inline hide-if-no-js'] );
               }
               return $action;
          }
          
          /**
           * Tweaks the links in the post update messages for cyboslider
           * 
           * The 'View post' link goes to the home url,
           * the 'Post preview' link is removed.
           * 
           * @since 1.1.4
           */
          public function tweak_post_update_messages_links( $messages ) {
               if ( 'cyboslider' == get_post_type() ) {
                    $messages['post'][1] = sprintf( 
                         __( 'Post updated. <a href="%s">View post</a>' ), 
                         esc_url( get_home_url() ) 
                    );
                    $messages['post'][6] = sprintf(
                         __( 'Post published. <a href="%s">View post</a>' ),
                         esc_url( get_home_url() )
                    );
                    $messages['post'][8] = __( 'Post submitted.', 'cyboslider' );
                    $messages['post'][10] = __( 'Post draft updated.', 'cyboslider' );
               }
               return $messages;
          }
		
	} // END class Cyboslider_Main
} // END if ( ! class_exists( 'Cyboslider_Main' ) )

if ( class_exists( 'Cyboslider_Main' ) ) {
	
	if ( ! is_admin() ) {
		require_once( CYBOSLIDER_PLUGIN_PATH . '/includes/class-cyboslider-frontend.php' );
	}
	
	$cyboslider_main = new Cyboslider_Main();
}

if ( ! function_exists( 'the_cyboslider' ) ) :
	/**
	 * echo out the slider
	 * 
	 * use this function to integrate the slider into your theme:
	 * <?php the_cyboslider() ?>
	 */
	function the_cyboslider() {
		echo get_the_cyboslider();
	}
endif;

if ( ! function_exists( 'get_the_cyboslider' ) ) :
	/**
	 * get the slider
	 */
	function get_the_cyboslider() {
		
		require_once( CYBOSLIDER_PLUGIN_PATH . '/includes/class-cyboslider-frontend.php' );
		
		$frontend = new Cyboslider_Frontend();
		return $frontend->get_the_slider();
	}
endif;
	