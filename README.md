# Pixeline's Email Protector
**Contributors:** pixeline
**Donate link:** http://goo.gl/7L2ua
Plugin URI: https://pixeline.be/downloads/email-protector-plugin-for-wordpress-450.html
**Tags:** spam, email, address, harvest, obfuscate, protection, email protection, antispam, emailaddress, encode, encrypt, link, mailto, obfuscate, protect, spambot
**Requires at least:** 2.7
**Tested up to:** 6.8.2
**Stable tag:** trunk

A WordPress plugin that lets your users write plain email addresses without worrying about spambots and email harvesters.


## Description

This plugin provides an unobtrusive yet efficient protection against email harvesters / spambots.

Post/page authors may write email addresses in their article in the usual format ("john@doe.com") without exposing them to spam email harvesters. The plugin takes care of the obfuscation, implementing a graceful degradation technique focusing on usability so as to protect your email addresses from harvesters while keeping them usable to your human visitors.

The plugin replaces any email address found in posts, pages, comments and excerpts, and replace them by a bit of html markup that should deceive most email harvesters: `<span class="email">john(replace the parenthesis by @)doe.com</span>`.
If javascript is available, it will display a clickable link and display the original email to the human user. Maximum usability, maximum protection.

<strong>Please <a href="http://wordpress.org/extend/plugins/pixelines-email-protector/">rate the plugin</a> if you like it.</strong>


## Installation

1. Unzip the file into your wp-content/plugins directory.
2. In your wp-admin screen, activate the plugin. That's it, your emails are now safe!

Additionally, there is a Settings screen (Email Protector) allowing you  you to customize the text that is displayed as a replacement to the email address.


## Usage

Write your email addresses inside your posts and pages as usual. When the plugin is activated, it will replace them by a human-readable html string that explains how to deduce the email address, and if javascript is available (99.9% of the time), the original email address will be displayed as a clickable mailto: link. For example:
`Hello john@doe.com. How are you today?`
will become
`<span class="pep-email">John( replace these parenthesis by @ )doe.com</span>`.

Additionally, you can specify what the mailto: link should look like by sticking a parenthesis inside of which you put the visible link text, like this:

`Hello john@doe.com(John Doe). How are you today?`
will become
`<span class="pep-email" title="John Doe">John( replace these parenthesis by @ )doe.com</span>`.


### inside a theme
If you need to protect emails inside your Theme's files (like the footer.php for example), you can use the function safe_email() like this:

` echo safe_email('you@domain.com'); `


## Changelog

### 1.4.0

### 1.3.0
- Extensive rewrite.
- Plugin does not run in the Admin anymore.
- The plugin does not need jQuery anymore. Plain vanilla javascript.


### 1.2.6
- Fix bug occurring when there are similar addresses, one being a substring of the other. Thank you, [@mkranz](https://wordpress.org/support/profile/mkranz)


### 1.2.5
- Stupid error fixed. My bad.


### 1.2.4
- Fixed all notices showing up when WP_DEBUG is true.


### 1.2.3
- Fixed a possible cause of javascript errors on some setups.


### 1.2.2
- Fixed Warnings appearing before comments.


### 1.2.1
- Added filters for get_the_content, get_the_title and get_the_excerpt


### 1.2
- Full code rewrite in OOP to avoid polluting the namespace.
- added filters to protect emails in title, widgets, and comments.
- Provided a function safe_email($email) to protect emails outside the loop in a theme for example.
- Clarified the Settings screen and provided thorough documentation.


### v1.1
- Now detects "mailto:" links and protects them too.


### v1.0.3
- Added the option to specify what should be the visible part of the clickable email by adding a title attribute to the generated Anchor.
- Changed the span class from "email" to "pep-email" to (kind of) use the "pep" namespace.


### v1.0.2
- Corrected the plugin's "Stable version" variable.
- fixed folderpath issue because of the wrong foldername the wordpress repository generates for the plugin :-/


### v.1.0.0
- Initial release
