<div class="wrap">
	<h1>Email Protector</h1>
	<p><?php _e('by ', 'pxln_email_protector'); ?> <a href="https://pixeline.be" target="_blank" rel="noopener noreferrer" class="external">pixeline</a></p>
	<p style="font-weight:bold;">
		<?php printf(__('If you like this plugin, please  %s give it a good rating %s on the Wordpress Plugins repository, and if you make any money out of it, %s send a few coins over to its author %s.', 'pxln_email_protector'), '<a href="http://wordpress.org/extend/plugins/pixelines-email-protector/" target="_blank" rel="noopener noreferrer">', '</a>', '<a title="Ko-Fi donation page" target="_blank" rel="noopener noreferrer" href="https://ko-fi.com/pixeline_be#payment-widget">', '</a>');
		?>
	</p>

	<h2 style="border-top:1px solid #999;padding-top:1em;"><?php _e('What does it do?', 'pxln_email_protector'); ?></h2>
	<p><?php _e('This plugin replaces any email address found in posts, pages, comments and excerpts, and replace them by a bit of html markup that will deceive most - if not all - email harvesters.', 'pxln_email_protector'); ?></p>
	<p><?php _e('Additionally, if javascript is available on the clientside, the markup will be turned into a clickable email link (mailto:), so there will be no loss of usability for your legitimate users.', 'pxln_email_protector'); ?></p>

	<blockquote>
		<?php _e('Example: let\'s imagine you\'ve written in your post: ', 'pxln_email_protector'); ?><code>Contact John: john@doe.com</code>. <?php _e('This email address could be spotted and used by spambots. This plugin will automatically convert it to', 'pxln_email_protector'); ?>
		<code>&lt;span class="pep-email"&gt;john(<?php echo esc_html(isset($this->options['pep_email_substitution_string']) ? $this->options['pep_email_substitution_string'] : ''); ?>)doe.com&lt;/span&gt;</code>.
	</blockquote>
	<h4><?php _e('Controlling the "visible" part of the email link', 'pxln_email_protector'); ?></h4>
	<p><?php _e('You can optionally set the visible text in the email link, by simply adding it in a parenthesis right next to the email. Like this:', 'pxln_email_protector'); ?></p>
	<blockquote><?php _e('Example: say you wrote in your post: ', 'pxln_email_protector'); ?><code>Contact John: john@doe.com(John Doe)</code>. <?php _e('It will be automatically converted to', 'pxln_email_protector'); ?>
		<code>&lt;span title="John Doe" class="pep-email"&gt;john(<?php echo esc_html(isset($this->options['pep_email_substitution_string']) ? $this->options['pep_email_substitution_string'] : ''); ?>)doe.com&lt;/span&gt;</code>.
	</blockquote>
	<p><?php _e('The javascript will then turn this markup into:', 'pxln_email_protector'); ?></p>
	<code>&lt;a class="pep-email" href="mailto:john@doe.com"&gt;John Doe&lt;/a&gt;</code>
	<h3><?php _e('Configuration', 'pxln_email_protector'); ?></h3>
	<?php settings_errors(); ?>
	<form method="post" action="options.php" id="p_email_protector_options">
		<?php settings_fields('p_email_protector_options_group'); ?>
		<table width="100%" cellspacing="2" cellpadding="5" class="form-table" style="background:#DDD;">
			<tr valign="top">
				<td>
					<label style="font-weight:normal;font-size:110%"><?php _e("You may wish to customize the text to put inside the parenthesis and substitute the @ sign with:", 'pxln_email_protector'); ?><br>
						<?php do_settings_sections('pixeline-email-protector'); ?>
					</label>
				</td>
			</tr>
		</table>
		<p class="submit">
			<?php submit_button(__('Save Changes', 'pxln_email_protector'), 'primary', 'submit', false); ?>
		</p>
	</form>

	<h2><?php _e('Usage', 'pxln_email_protector'); ?></h2>
	<h3><?php _e('In a post, page, comment or widget', 'pxln_email_protector'); ?></h3>
	<p><?php _e('Simply write the plain email address in your post, as if spambots never existed!', 'pxln_email_protector'); ?></p>
	<h3><?php _e('In your theme', 'pxln_email_protector'); ?></h3>
	<p><?php _e('You can use the function ', 'pxln_email_protector'); ?><code>safe_email($email)</code> to protect an email address directly written inside a theme file.</p>
	<blockquote><?php _e('Example:', 'pxln_email_protector'); ?>
		<code>Write me at &lt;?php echo safe_email('john@doe.com');?&gt; and i'll get back to you A.S.A.P.</code>
	</blockquote>
	<h2><?php _e('Help & support', 'pxln_email_protector'); ?></h2>
	<p><?php _e('Post your questions / bugs on ', 'pxln_email_protector'); ?><a href="http://wordpress.org/support/plugin/pixelines-email-protector" target="_blank" rel="noopener noreferrer"><?php _e('the WordPress forum dedicated plugin page', 'pxln_email_protector'); ?></a>.</p>
	<h3><?php _e('Sustain the work', 'pxln_email_protector'); ?></h3>
	<p><?php _e('This plugin protects your users from spambots and email harvesters. If it’s been helpful to you, please consider tipping the author to support ongoing development.', 'pxln_email_protector'); ?></p>
	<iframe id='kofiframe' src='https://ko-fi.com/pixeline_be/?hidefeed=true&widget=true&embed=true&preview=true' style='border:none;width:100%;padding:4px;background:#f9f9f9;' height='712' title='pixeline_be'></iframe>
</div>