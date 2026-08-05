<?php

declare(strict_types=1);

// Lightweight regression tests for plugin behavior without bootstrapping WordPress.

$GLOBALS['pxln_test_options'] = array();

function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {}
function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {}
function is_admin()
{
    return false;
}
function plugins_url($path = '', $plugin = '')
{
    return 'https://example.test/wp-content/plugins/pixeline-email-protector';
}
function plugin_dir_path($file)
{
    return dirname($file) . '/';
}
function plugin_basename($file)
{
    return basename($file);
}
function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {}
function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {}
function wp_specialchars_decode($string, $quote_style = ENT_COMPAT)
{
    return html_entity_decode($string, $quote_style, 'UTF-8');
}
function esc_attr($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}
function esc_html($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}
function __($text, $domain = 'default')
{
    return $text;
}
function register_setting($option_group, $option_name, $args = array()) {}
function add_settings_section($id, $title, $callback, $page) {}
function add_settings_field($id, $title, $callback, $page, $section = 'default', $args = array()) {}
function sanitize_text_field($value)
{
    return trim(strip_tags((string) $value));
}
function wp_unslash($value)
{
    return $value;
}

function get_option($option, $default = false)
{
    return array_key_exists($option, $GLOBALS['pxln_test_options'])
        ? $GLOBALS['pxln_test_options'][$option]
        : $default;
}

function update_option($option, $value)
{
    $GLOBALS['pxln_test_options'][$option] = $value;
    return true;
}

if (!function_exists('wp_html_split')) {
    function wp_html_split($input)
    {
        return preg_split('/(<[^>]+>)/', (string) $input, -1, PREG_SPLIT_DELIM_CAPTURE);
    }
}

require_once dirname(__DIR__) . '/pixeline-email-protector.php';

function assert_true($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "Assertion failed: {$message}\n");
        exit(1);
    }
}

$plugin = new WP_Email_Protector();

$input = '<p>Contact: john@doe.com</p><script>var e="john@doe.com";</script><style>.x{content:"john@doe.com";}</style><pre>john@doe.com</pre>';
$output = $plugin->email_protect($input);

assert_true(strpos($output, '<span class="pep-email">john(') !== false, 'Visible content email should be replaced.');
assert_true(strpos($output, '<script>var e="john@doe.com";</script>') !== false, 'Script block should remain unchanged.');
assert_true(strpos($output, '<style>.x{content:"john@doe.com";}</style>') !== false, 'Style block should remain unchanged.');
assert_true(strpos($output, '<pre>john@doe.com</pre>') !== false, 'Preformatted block should remain unchanged.');

$label_output = $plugin->email_protect('john@doe.com(John Doe)');
assert_true(strpos($label_output, 'title="John Doe"') !== false, 'Label in parenthesis should map to title attribute.');

$safe_output = safe_email('john@doe.com');
assert_true(strpos($safe_output, 'class="pep-email"') !== false, 'safe_email() should return plugin markup.');

echo "All regression tests passed.\n";
