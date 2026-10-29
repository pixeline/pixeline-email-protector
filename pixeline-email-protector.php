<?php
/*
Plugin Name: Email protector
Plugin URI: https://pixeline.be
Description: Obfuscate any email address published on the public face of a wordpress website, protecting them against spambots and email harvesters.
Version: 1.5.0
Author: pixeline
Author URI: https://pixeline.be
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('EMAIL_PROTECTOR_VERSION')) {
	define('EMAIL_PROTECTOR_VERSION', '1.5.0');
}

if (!class_exists('WP_Email_Protector')) {
	class WP_Email_Protector
	{
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
		 * @var string $localizationDomain Domain used for localization
		 */
		var $localizationDomain = "pxln_email_protector";

		/**
		 * @var string $url The url to this plugin
		 */
		var $url = '';
		/**
		 * @var string $urlpath The path to this plugin
		 */
		var $urlpath = '';

		//Class Functions


		public function __construct()
		{
			//Language Setup
			add_action('plugins_loaded', array($this, 'load_textdomain'));

			//"Constants" setup
			$this->pluginId = 'email-protector';
			$this->pluginVersion = EMAIL_PROTECTOR_VERSION;
			$this->url = plugins_url(basename(__FILE__), __FILE__);
			$this->urlpath = plugins_url('', __FILE__);

			//Initialize the options
			$this->getOptions();

			//Actions
			if (is_admin()) {
				add_action('admin_menu', array(&$this, "admin_menu_link"));
				add_action('admin_init', array($this, 'register_settings'));
			} else {
				add_filter('comment_text', array($this, "email_protect"));
				add_filter('the_content', array($this, "email_protect"));
				add_filter('get_the_content', array($this, "email_protect"));
				add_filter('the_excerpt', array($this, "email_protect_excerpt"));
				add_filter('get_the_excerpt', array($this, 'email_protect_excerpt'));
				add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
				add_filter('the_title', array($this, "email_protect"));
				add_filter('get_the_title', array($this, "email_protect"));
				add_filter('widget_text', array($this, "email_protect"));
				add_filter('widget_text_content', array($this, "email_protect"));
			}
		}

		function load_textdomain()
		{
			load_plugin_textdomain('pxln_email_protector', false, dirname(plugin_basename(__FILE__)) . '/languages');
		}

		function frontend_scripts()
		{
			//wp_enqueue_script('jquery');
			$js_file = plugin_dir_path(__FILE__) . 'pixeline-email-protector.js';
			$ver = file_exists($js_file) ? filemtime($js_file) : $this->pluginVersion;
			wp_enqueue_script('pxln-email-protector', $this->urlpath  . '/pixeline-email-protector.js', array(), $ver, true);
		}


		/**
		 * Obfuscate a full address the same way the visible replacement does.
		 *
		 * @param string $address Bare email address, without any ?subject= query.
		 * @return string e.g. "john(Replace this parenthesis with the @ sign)doe.com"
		 */
		function pep_obfuscate_address($address)
		{
			$substitution_string = isset($this->options['pep_email_substitution_string'])
				? $this->options['pep_email_substitution_string']
				: '';

			return str_replace('@', '(' . $substitution_string . ')', $address);
		}


		/**
		 * Take mailto: anchors out of the harvesters' reach.
		 *
		 * Two very different things are written as a mailto anchor, and they need opposite
		 * treatment:
		 *
		 *  - An autolink, whose visible text IS the address — what editors produce when an
		 *    address is pasted into the visual editor. Nothing is lost by discarding it: the
		 *    anchor is replaced by the bare address, which the main pass then turns into the
		 *    usual protected <span>.
		 *
		 *  - A deliberate link, whose visible text is something else ("Get in touch"). Here
		 *    the anchor carries the author's design — classes, target, rel, nested markup —
		 *    and discarding it silently publishes a styled button as a raw address. Until
		 *    1.4.3 that is exactly what happened. The anchor is now kept intact and only its
		 *    href is taken away, the address riding along obfuscated in data-pep-email for
		 *    the frontend script to restore.
		 *
		 * An address appearing inside the visible text falls back to the first branch: the
		 * main pass would wrap it in a <span>, which the script turns into an <a>, and an
		 * anchor nested in an anchor is not valid HTML.
		 *
		 * @param string $content Content being filtered.
		 * @return string
		 */
		function pep_removeMailtoLink($content)
		{
			if (stripos($content, 'mailto:') === false) {
				return $content;
			}

			$anchor_pattern = '~<a\b([^>]*?)\bhref\s*=\s*(?:"mailto:([^"]*)"|\'mailto:([^\']*)\'|mailto:([^\s>]+))([^>]*)>(.*?)</a>~is';
			$plugin = $this;

			return preg_replace_callback(
				$anchor_pattern,
				function ($matches) use ($plugin) {
					$mailto_value = '';
					for ($i = 2; $i <= 4; $i++) {
						if (isset($matches[$i]) && $matches[$i] !== '') {
							$mailto_value = $matches[$i];
							break;
						}
					}

					if ($mailto_value === '') {
						return $matches[0];
					}

					$mailto_value = wp_specialchars_decode($mailto_value, ENT_QUOTES);

					/* Only the part before ?subject=…&body=… is the address; the query is
					   carried over untouched so the restored link still opens with it. */
					$query_at = strpos($mailto_value, '?');
					$email    = $query_at === false ? $mailto_value : substr($mailto_value, 0, $query_at);
					$query    = $query_at === false ? '' : substr($mailto_value, $query_at);

					$label = trim(strip_tags($matches[6]));

					if (
						$email === ''
						|| $label === ''
						|| strcasecmp($label, $email) === 0
						|| strcasecmp($label, $mailto_value) === 0
						|| preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,63}/i', $label)
					) {
						return $email;
					}

					$attributes = rtrim($matches[1]) . $matches[5];
					$parked     = $plugin->pep_obfuscate_address($email) . $query;

					return '<a' . $attributes . ' data-pep-email="' . esc_attr($parked) . '">'
						. $matches[6] . '</a>';
				},
				$content
			);
		}


		/**
		 * Replace emails only in text nodes, never inside HTML tags/attributes.
		 *
		 * @param string   $content Full HTML content.
		 * @param callable $replace_cb Callback used to replace plain-text email matches.
		 * @return string
		 */
		function pep_replace_text_nodes($content, $replace_cb)
		{
			$email_pattern = '/([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\.([A-Z]{2,63})(\((.+?)\))?/i';

			if (!function_exists('wp_html_split')) {
				$chunks = preg_split('/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
			} else {
				$chunks = wp_html_split($content);
			}

			$excluded_tags = array('script', 'style', 'noscript', 'textarea', 'code', 'pre');
			$excluded_depth = 0;

			foreach ($chunks as $index => $chunk) {
				if ($chunk === '') {
					continue;
				}

				if ($chunk[0] === '<') {
					if (preg_match('~^<\s*/\s*([a-z0-9:-]+)\b~i', $chunk, $matches)) {
						$tag_name = strtolower($matches[1]);
						if (in_array($tag_name, $excluded_tags, true) && $excluded_depth > 0) {
							$excluded_depth--;
						}
					} elseif (preg_match('~^<\s*([a-z0-9:-]+)\b~i', $chunk, $matches)) {
						$tag_name = strtolower($matches[1]);
						if (in_array($tag_name, $excluded_tags, true) && !preg_match('~/\s*>$~', $chunk)) {
							$excluded_depth++;
						}
					}
					continue;
				}

				if ($excluded_depth > 0) {
					continue;
				}

				$chunks[$index] = preg_replace_callback($email_pattern, $replace_cb, $chunk);
			}

			return implode('', $chunks);
		}


		function email_protect($content)
		{
			if (strpos($content, '@') === false) {
				return $content;
			}
			$content = $this->pep_removeMailtoLink($content);
			// ----------------------------------------------------------------------
			// MAIN FUNCTION: replaces any email address by its harvest-proof counterpart.
			// ----------------------------------------------------------------------
			$substitution_string = isset($this->options['pep_email_substitution_string']) ? $this->options['pep_email_substitution_string'] : '';
			$content = $this->pep_replace_text_nodes(
				$content,
				function ($matches) use ($substitution_string) {
					$user_part   = isset($matches[1]) ? $matches[1] : '';
					$domain_part = isset($matches[2]) ? $matches[2] : '';
					$tld_part    = isset($matches[3]) ? $matches[3] : '';
					$title_text  = isset($matches[5]) ? $matches[5] : '';
					$visible_text = $user_part . '(' . $substitution_string . ')' . $domain_part . '.' . $tld_part;
					$title_attr = $title_text !== '' ? ' title="' . esc_attr($title_text) . '"' : '';
					return '<span class="pep-email"' . $title_attr . '>' . esc_html($visible_text) . '</span>';
				}
			);
			return $content;
		}


		function email_protect_excerpt($content)
		{
			if (strpos($content, '@') === false) {
				return $content;
			}
			$content = $this->pep_removeMailtoLink($content);
			// ----------------------------------------------------------------------
			// SECUNDARY FUNCTION: replaces any email address by its harvest-proof counterpart in the POST EXCERPT.
			// ----------------------------------------------------------------------
			$content = $this->pep_replace_text_nodes(
				$content,
				function ($matches) {
					$display_text = '';
					if (isset($matches[5]) && $matches[5] !== '') {
						$display_text = $matches[5];
					} else {
						$display_text = isset($matches[1]) ? $matches[1] : '';
					}
					return esc_html($display_text);
				}
			);
			return $content;
		}


		/**
		 * @desc Retrieves the plugin options from the database.
		 * @return array
		 */
		function getOptions()
		{
			if (!$theOptions = get_option($this->optionsName)) {
				$theOptions = array('pep_email_substitution_string' => 'Replace this parenthesis with the @ sign');
				update_option($this->optionsName, $theOptions);
			}
			$this->options = $theOptions;
		}

		/**
		 * Saves the admin options to the database.
		 */
		function saveAdminOptions()
		{
			return update_option($this->optionsName, $this->options);
		}

		/**
		 * @desc Adds the options subpanel
		 */
		function admin_menu_link()
		{
			add_options_page(__('Email Protector', 'pxln_email_protector'), __('Email Protector', 'pxln_email_protector'), 'manage_options', basename(__FILE__), array(&$this, 'admin_options_page'));
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2);
		}


		/**
		 * @desc Adds the Settings link to the plugin activate/deactivate page
		 */
		function filter_plugin_actions($links, $file)
		{
			$settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings', 'pxln_email_protector') . '</a>';
			array_unshift($links, $settings_link); // before other links

			return $links;
		}

		/**
		 * Adds settings/options page
		 */
		function admin_options_page()
		{
			include 'admin.ui.php';
		}

		/**
		 * Register settings, sections and fields for the Settings API
		 */
		function register_settings()
		{
			register_setting('p_email_protector_options_group', $this->optionsName, array($this, 'sanitize_options'));
			add_settings_section('p_email_protector_main', '', '__return_false', 'pixeline-email-protector');
			add_settings_field(
				'pep_email_substitution_string',
				__('Substitution string for the @ sign', 'pxln_email_protector'),
				array($this, 'render_substitution_string_field'),
				'pixeline-email-protector',
				'p_email_protector_main'
			);
		}

		/**
		 * Sanitize and validate plugin options
		 * @param array $input
		 * @return array
		 */
		function sanitize_options($input)
		{
			$output = array();
			$input  = is_array($input) ? wp_unslash($input) : array();
			if (isset($input['pep_email_substitution_string'])) {
				$value = sanitize_text_field($input['pep_email_substitution_string']);
				$value = preg_replace('/[^A-Za-z0-9 _\.\-\+\[\]\(\)]/', '', $value);
				$value = trim($value);
				if (strlen($value) > 64) {
					$value = substr($value, 0, 64);
				}
				$output['pep_email_substitution_string'] = $value;
			} else {
				$output['pep_email_substitution_string'] = '';
			}
			return $output;
		}

		/**
		 * Render the substitution string field
		 */
		function render_substitution_string_field()
		{
			$options = get_option($this->optionsName);
			$value = isset($options['pep_email_substitution_string']) ? $options['pep_email_substitution_string'] : '';
			echo '<input name="' . esc_attr($this->optionsName) . '[pep_email_substitution_string]" type="text" id="pep_email_substitution_string" size="45" value="' . esc_attr($value) . '"/>';
		}
	} //End Class
} //End if class exists statement

if (class_exists('WP_Email_Protector')) {
	$p_email_protector_var = new WP_Email_Protector();
}

if (!function_exists('safe_email')) {
	// Public function usable in themes
	// ex: echo safe_email('hello@boby.com');
	function safe_email($email)
	{
		global $p_email_protector_var;
		if (!($p_email_protector_var instanceof WP_Email_Protector)) {
			$p_email_protector_var = new WP_Email_Protector();
		}
		return $p_email_protector_var->email_protect($email);
	}
}
