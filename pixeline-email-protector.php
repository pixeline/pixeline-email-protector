<?php
/*
Plugin Name: Email protector
Plugin URI: https://pixeline.be
Description: Write email addresses in your pages/posts without worrying about spambots and email harvesters.
Version: 1.3.0
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
		 * @var string $localizationDomain Domain used for localization
		 */
		var $localizationDomain = "p_email_protector";

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
			$mo = plugin_dir_path(__FILE__) . 'languages/' . $this->localizationDomain . '-' . $locale . '.mo';
			load_textdomain($this->localizationDomain, $mo);

			//"Constants" setup
			$this->pluginId = 'email-protector';
			$this->pluginVersion = '1.3.0';
			$this->url = plugins_url(basename(__FILE__), __FILE__);
			$this->urlpath = plugins_url('', __FILE__);

			//Initialize the options
			$this->getOptions();

			//Actions
			if(is_admin()){
				add_action("admin_menu", array(&$this, "admin_menu_link"));
			} else{
				add_filter('comment_text', array( $this, "email_protect"));
				add_filter('the_content', array( $this, "email_protect"));
				add_filter('get_the_content', array( $this, "email_protect"));
				add_filter('the_excerpt', array( $this, "email_protect_excerpt"));
				add_filter('get_the_excerpt', array( $this, 'email_protect_excerpt' ));
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts'));
				add_filter('the_title', array( $this, "email_protect"));
				add_filter('get_the_title', array( $this, "email_protect"));
				add_filter('widget_content', array( $this, "email_protect"));
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
?>
			<div class="wrap">
			<h1><?php _e('Email Protector', $this->localizationDomain);?></h1>
<p><?php _e('by <a href="https://www.pixeline.be" target="_blank" class="external">pixeline</a>', $this->localizationDomain); ?></p>
			<p style="font-weight:bold;"><?php _e('If you like this plugin, please <a href="http://wordpress.org/extend/plugins/pixelines-email-protector/" target="_blank">give it a good rating</a> on the Wordpress Plugins repository, and if you make any money out of it, <a title="Paypal donation page" target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=J9X5B6JUVPBHN&lc=US&item_name=pixeline%20%2d%20Wordpress%20plugin&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHostedGuest">send a few coins over to me</a>!', $this->localizationDomain); ?></p>

			<h2 style="border-top:1px solid #999;padding-top:1em;"><?php _e('Settings', $this->localizationDomain);?></h2>
<h3><?php _e('What does it do?');?></h3>
    <p><?php _e('This plugin replaces any email address found in posts, pages, comments and excerpts, and replace them by a bit of html markup that will deceive most - if not all - email harvesters.');?></p>
    <p><?php _e('Additionally, if javascript is available on the clientside, the markup will be turned into a clickable email link (mailto:), so there will be no loss of usability for your legitimate users.');?></p>

<blockquote><?php _e('Example: let\'s imagine you\'ve written in your post: ');?><code>Contact John: john@doe.com</code>. <?php _e('This email address could be spotted and used by spambots. This plugin will automatically convert it to', $this->localizationDomain);?>
        <code>&lt;span class="pep-email"&gt;john(<?php echo $this->options['pep_email_substitution_string'] ;?>)doe.com&lt;/span&gt;</code>.</blockquote>

    <h4><?php _e('Controlling the "visible" part of the email link', $this->localizationDomain);?></h4>
    <p><?php _e('You can optionally set the visible text in the email link, by simply adding it in a parenthesis right next to the email. Like this:', $this->localizationDomain);?></p>
    <blockquote><?php _e('Example: say you wrote in your post: ', $this->localizationDomain);?><code>Contact John: john@doe.com(John Doe)</code>. <?php _e('It will be automatically converted to', $this->localizationDomain);?>
        <code>&lt;span title="John Doe" class="pep-email"&gt;john(<?php echo $this->options['pep_email_substitution_string'] ;?>)doe.com&lt;/span&gt;</code>.</blockquote>
  <p><?php _e('The javascript will then turn this markup into:', $this->localizationDomain);?></p>
  <code>&lt;a class="pep-email" href="mailto:john@doe.com"&gt;John Doe&lt;/a&gt;</code>
<h3><?php _e('Configuration', $this->localizationDomain);?></h3>
			<form method="post" id="p_email_protector_options">
			<?php wp_nonce_field('p_email_protector-update-options'); ?>
				<table width="100%" cellspacing="2" cellpadding="5" class="form-table" style="background:#DDD;">
					<tr valign="top">

						<td>
						<label style="font-weight:normal;font-size:110%"><?php _e("You may wish to customize the text to put inside the parenthesis and substitute the @ sign with:", $this->localizationDomain); ?><br>
							<input name="pep_email_substitution_string" type="text" id="pep_email_substitution_string" size="45" value="<?php echo $this->options['pep_email_substitution_string'] ;?>"/>
						</label>
						</td>
					</tr>

				</table>
				<p class="submit">
					<input type="submit" name="p_email_protector_save" class="button-primary" value="<?php _e('Save Changes', $this->localizationDomain); ?>" />
				</p>
			</form>

    <h2><?php _e('Usage', $this->localizationDomain);?></h2>
    <h3><?php _e('In a post, page, comment or widget', $this->localizationDomain);?></h3>
    <p><?php _e('Simply write the plain email address in your post, as if spambots never existed!', $this->localizationDomain);?></p>

    <h3><?php _e('In your theme', $this->localizationDomain);?></h3>
    <p><?php _e('You can use the function ', $this->localizationDomain);?><code>safe_email($email)</code> to protect an email address directly written inside a theme file.</p>
    <blockquote><?php _e('Example:', $this->localizationDomain);?>

    <code>Write me at &lt;?php echo safe_email('john@doe.com');?&gt; and i'll get back to you A.S.A.P.</code>
    </blockquote>
    <h2><?php _e('Help & support');?></h2>
<p><?php _e('Post your questions / bugs on ');?><a href="http://wordpress.org/support/plugin/pixelines-email-protector" target="_blank"><?php _e('the Wordpress forum dedicated plugin page');?></a>.</p>
		<img src="http://pixeline.be/pixeline-downloads-tracker.php?fn=<?php echo $this->pluginId ?>&v=<?php echo $this->pluginVersion ?>&uu=<?php echo $_SERVER['HTTP_HOST']  ?>" width="1" height="1"/>
 <h3><?php _e('Sustain the work', $this->localizationDomain);?></h3>
    <p><?php _e('This plugin saves your emails from spambots. Why not show your gratitude by giving just a few coins to the maker of this plugin ?', $this->localizationDomain);?></p>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
               ">
        <input type="image" src="https://www.paypal.com/en_US/BE/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
    </form>
			<?php
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
