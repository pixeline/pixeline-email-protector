<?php
/*
Plugin Name: Email protector
Plugin URI: https://pixeline.be
Description: Write email addresses in your pages/posts without worrying about spambots and email harvesters.
Version: 1.3.2
Author: pixeline
Author URI: https://pixeline.be
*/

if (!class_exists('WP_Email_Protector')) {
	class WP_Email_Protector{
		/**
		 * @var string The options string name for this plugin
		 */
		var $optionsName = 'p_email_protector_options';
		protected $pluginVersion;
		protected $pluginId;
		/**
		 * @var array $options Stores the options for this plugin
		 */
		var $options = array();

		/**
		 * @var string $url The url to this plugin
		 */
		var $url = '';
		/**
		 * @var string $urlpath The path to this plugin
		 */
		var $urlpath = '';

		//Class Functions


		public function __construct() {
			//Language Setup
			$locale = get_locale();
			$mo = plugin_dir_path(__FILE__) . 'languages/' . 'pxln_email_protector' . '-' . $locale . '.mo';
			load_textdomain('pxln_email_protector', $mo);

			//"Constants" setup
			$this->pluginId = 'email-protector';
			$this->pluginVersion = '1.3.2';
			$this->url = plugins_url(basename(__FILE__), __FILE__);
			$this->urlpath = plugins_url('', __FILE__);

			//Initialize the options
			$this->getOptions();

			//Actions
			if(is_admin()){
				add_action( 'admin_menu', array(&$this, "admin_menu_link"));
				
			} else{
				add_filter( 'comment_text', array( $this, "email_protect"));
				add_filter( 'the_content', array( $this, "email_protect"));
				add_filter( 'get_the_content', array( $this, "email_protect"));
				add_filter( 'the_excerpt', array( $this, "email_protect_excerpt"));
				add_filter( 'get_the_excerpt', array( $this, 'email_protect_excerpt' ));
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts'));
				add_filter( 'the_title', array( $this, "email_protect"));
				add_filter( 'get_the_title', array( $this, "email_protect"));
				add_filter( 'widget_content', array( $this, "email_protect"));
			}
		}

		function frontend_scripts(){
			//wp_enqueue_script('jquery');
			wp_enqueue_script('pxln-email-protector', $this->urlpath  . '/pixeline-email-protector.js', array(), $this->pluginVersion, true);
		}


		function pep_removeMailtoLink($content){

			// Removes <a href="mailto:" links sometimes added via copy/pasting into the visual editor

			$r = '`\<a([^>]+)href\=\"mailto\:([^">]+)\"([^>]*)\>(.*?)\<\/a\>`ism';
			preg_match_all($r, $content, $addresses, PREG_SET_ORDER);
			$the_addrs = $addresses[0];
			for ($a = 0; $a < count($the_addrs); $a++) {
				$repaddr[$a] = preg_replace($r, '$2', $the_addrs[$a]);
			}
			$cc = str_replace($the_addrs, $repaddr, $content);
			return $cc;

		}


		function email_protect($content) {
			$content = $this->pep_removeMailtoLink($content);
			// ----------------------------------------------------------------------
			// MAIN FUNCTION: replaces any email address by its harvest-proof counterpart.
			// ----------------------------------------------------------------------
			$addr_pattern = '/([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\.([A-Z]{2,4})(\((.+?)\))?/i';
			preg_match_all($addr_pattern, $content, $addresses);
			$the_addrs = $addresses[0];
			for ($a = 0; $a < count($the_addrs); $a++) {
				$repaddr[$a] = preg_replace($addr_pattern, '<span title="$5" class="pep-email">$1(' . $this->options['pep_email_substitution_string'] . ')$2.$3</span>', $the_addrs[$a]);
			}
			$cc = str_replace($the_addrs, $repaddr, $content);
			return $cc;
		}


		function email_protect_excerpt($content) {
			$content = $this->pep_removeMailtoLink($content);
			// ----------------------------------------------------------------------
			// SECUNDARY FUNCTION: replaces any email address by its harvest-proof counterpart in the POST EXCERPT.
			// ----------------------------------------------------------------------
			$addr_pattern = '/([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\.([A-Z]{2,4})(\((.+?)\))?/i';
			preg_match_all($addr_pattern, $content, $addresses);
			$the_addrs = $addresses[0];
			for ($a = 0; $a < count($the_addrs); $a++) {
				if (count($the_addrs[$a]) == 4)
					$repaddr[$a] = preg_replace($addr_pattern, '$5', $the_addrs[$a]);
				else
					$repaddr[$a] = preg_replace($addr_pattern, '$1', $the_addrs[$a]);
			}
			$cc = str_replace($the_addrs, $repaddr, $content);
			return $cc;
		}


		/**
		 * @desc Retrieves the plugin options from the database.
		 * @return array
		 */
		function getOptions() {
			if (!$theOptions = get_option($this->optionsName)) {
				$theOptions = array('pep_email_substitution_string'=> 'Replace this parenthesis with the @ sign');
				update_option($this->optionsName, $theOptions);
			}
			$this->options = $theOptions;
		}

		/**
		 * Saves the admin options to the database.
		 */
		function saveAdminOptions(){
			return update_option($this->optionsName, $this->options);
		}

		/**
		 * @desc Adds the options subpanel
		 */
		function admin_menu_link() {
			add_options_page('Email protector', 'Email protector', 10, basename(__FILE__), array(&$this, 'admin_options_page'));
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
		}


		/**
		 * @desc Adds the Settings link to the plugin activate/deactivate page
		 */
		function filter_plugin_actions($links, $file) {
			$settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
			array_unshift( $links, $settings_link ); // before other links

			return $links;
		}

		/**
		 * Adds settings/options page
		 */
		function admin_options_page() {
			if($_POST['p_email_protector_save']){
				if (! wp_verify_nonce($_POST['_wpnonce'], 'p_email_protector-update-options') ) die('Whoops! There was a problem with the data you posted. Please go back and try again.');
				$this->options['pep_email_substitution_string'] = $_POST['pep_email_substitution_string'];

				$this->saveAdminOptions();

				echo '<div class="updated"><p>', _('Success! Your changes were sucessfully saved!'), '</p></div>';
			}
			
			require 'admin.ui.php';
		}
	} //End Class
} //End if class exists statement

if (class_exists('WP_Email_Protector')) {
	$p_email_protector_var = new WP_Email_Protector();
}

if(!function_exists('safe_email')){
	// Public function usable in themes
	// ex: echo safe_email('hello@boby.com');
	function safe_email( $email ) {
		$p_email_protector_var = new WP_Email_Protector();
		return $p_email_protector_var->email_protect($email);
	}
}
