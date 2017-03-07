<div class="wrap">
	<h1>Email Protector</h1>
	<p><?php _e('by ', 'pxln_email_protector'); ?> <a href="https://pixeline.be" target="_blank" class="external">pixeline</a></p>
	<p style="font-weight:bold;">
	<?php printf( __( 'If you like this plugin, please  %s give it a good rating %s on the Wordpress Plugins repository, and if you make any money out of it, %s send a few coins over to its author %s.', 'pxln_email_protector' ), '<a href="http://wordpress.org/extend/plugins/pixelines-email-protector/" target="_blank">', '</a>', '<a title="Paypal donation page" target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=J9X5B6JUVPBHN&lc=US&item_name=pixeline%20%2d%20Wordpress%20plugin&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHostedGuest">', '</a>' );
?>
	</p>

	<h2 style="border-top:1px solid #999;padding-top:1em;"><?php _e('What does it do?');?></h2>
	<p><?php _e('This plugin replaces any email address found in posts, pages, comments and excerpts, and replace them by a bit of html markup that will deceive most - if not all - email harvesters.');?></p>
	<p><?php _e('Additionally, if javascript is available on the clientside, the markup will be turned into a clickable email link (mailto:), so there will be no loss of usability for your legitimate users.');?></p>
	
	<blockquote>
		<?php _e('Example: let\'s imagine you\'ve written in your post: ');?><code>Contact John: john@doe.com</code>. <?php _e('This email address could be spotted and used by spambots. This plugin will automatically convert it to', 'pxln_email_protector');?>
		<code>&lt;span class="pep-email"&gt;john(<?php echo $this->options['pep_email_substitution_string'] ;?>)doe.com&lt;/span&gt;</code>.
	</blockquote>
	<h4><?php _e('Controlling the "visible" part of the email link', 'pxln_email_protector');?></h4>
	<p><?php _e('You can optionally set the visible text in the email link, by simply adding it in a parenthesis right next to the email. Like this:', 'pxln_email_protector');?></p>
	<blockquote><?php _e('Example: say you wrote in your post: ', 'pxln_email_protector');?><code>Contact John: john@doe.com(John Doe)</code>. <?php _e('It will be automatically converted to', 'pxln_email_protector');?>
	<code>&lt;span title="John Doe" class="pep-email"&gt;john(<?php echo $this->options['pep_email_substitution_string'] ;?>)doe.com&lt;/span&gt;</code>.</blockquote>
	<p><?php _e('The javascript will then turn this markup into:', 'pxln_email_protector');?></p>
	<code>&lt;a class="pep-email" href="mailto:john@doe.com"&gt;John Doe&lt;/a&gt;</code>
	<h3><?php _e('Configuration', 'pxln_email_protector');?></h3>
	<form method="post" id="p_email_protector_options">
	<?php wp_nonce_field('p_email_protector-update-options'); ?>
		<table width="100%" cellspacing="2" cellpadding="5" class="form-table" style="background:#DDD;">
			<tr valign="top">
				<td>
				<label style="font-weight:normal;font-size:110%"><?php _e("You may wish to customize the text to put inside the parenthesis and substitute the @ sign with:", 'pxln_email_protector'); ?><br>
					<input name="pep_email_substitution_string" type="text" id="pep_email_substitution_string" size="45" value="<?php echo $this->options['pep_email_substitution_string'] ;?>"/>
				</label>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="p_email_protector_save" class="button-primary" value="<?php _e('Save Changes', 'pxln_email_protector'); ?>" />
		</p>
	</form>

<h2><?php _e('Usage', 'pxln_email_protector');?></h2>
<h3><?php _e('In a post, page, comment or widget', 'pxln_email_protector');?></h3>
<p><?php _e('Simply write the plain email address in your post, as if spambots never existed!', 'pxln_email_protector');?></p>
<h3><?php _e('In your theme', 'pxln_email_protector');?></h3>
<p><?php _e('You can use the function ', 'pxln_email_protector');?><code>safe_email($email)</code> to protect an email address directly written inside a theme file.</p>
<blockquote><?php _e('Example:', 'pxln_email_protector');?>
	<code>Write me at &lt;?php echo safe_email('john@doe.com');?&gt; and i'll get back to you A.S.A.P.</code>
</blockquote>
<h2><?php _e('Help & support');?></h2>
<p><?php _e('Post your questions / bugs on ');?><a href="http://wordpress.org/support/plugin/pixelines-email-protector" target="_blank"><?php _e('the Wordpress forum dedicated plugin page');?></a>.</p>
<h3><?php _e('Sustain the work', 'pxln_email_protector');?></h3>
<p><?php _e('This plugin saves your emails from spambots. Why not show your gratitude by giving just a few coins to the maker of this plugin ?', 'pxln_email_protector');?></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
	<input type="image" src="https://www.paypal.com/en_US/BE/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
</div>