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
// Like WordPress, the escapers leave entities that are already encoded alone.
function esc_attr($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8', false);
}
function esc_html($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8', false);
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

// mailto anchors: an autolink is still discarded, a labelled link is now kept.

$autolink = $plugin->email_protect('<p>Write to <a href="mailto:john@doe.com">john@doe.com</a> today.</p>');
assert_true(strpos($autolink, '<span class="pep-email">john(') !== false, 'Autolinked address should become a protected span.');
assert_true(strpos($autolink, '<a ') === false, 'Autolink anchor should be discarded.');

$shouting = $plugin->email_protect('<a href="mailto:john@doe.com">JOHN@DOE.COM</a>');
assert_true(strpos($shouting, '<a ') === false, 'Autolink should be recognised whatever its case.');

$button = $plugin->email_protect('<a class="btn-primary" target="_blank" href="mailto:john@doe.com">Get in touch</a>');
assert_true(strpos($button, 'class="btn-primary"') !== false, 'Labelled anchor should keep its classes.');
assert_true(strpos($button, 'target="_blank"') !== false, 'Labelled anchor should keep its other attributes.');
assert_true(strpos($button, '>Get in touch</a>') !== false, 'Labelled anchor should keep its visible text.');
assert_true(strpos($button, 'data-pep-email="john(') !== false, 'Labelled anchor should carry the obfuscated address.');
assert_true(strpos($button, 'mailto:john@doe.com') === false, 'Labelled anchor should not leak the address.');
assert_true(strpos($button, 'john@doe.com') === false, 'No readable address should survive anywhere in the markup.');

$with_subject = $plugin->email_protect('<a class="btn" href="mailto:john@doe.com?subject=Hello there">Mail us</a>');
assert_true(strpos($with_subject, '?subject=Hello there"') !== false, 'A mailto query should be carried over untouched.');

$nested = $plugin->email_protect('<a class="btn" href="mailto:john@doe.com"><span>Get <b>in</b> touch</span></a>');
assert_true(strpos($nested, '<span>Get <b>in</b> touch</span></a>') !== false, 'Markup inside the label should survive.');

$single_quoted = $plugin->email_protect("<a class='btn' href='mailto:john@doe.com'>Get in touch</a>");
assert_true(strpos($single_quoted, 'data-pep-email="john(') !== false, 'Single-quoted href should be handled too.');

$address_in_label = $plugin->email_protect('<a class="btn" href="mailto:john@doe.com">Write to john@doe.com</a>');
assert_true(strpos($address_in_label, 'data-pep-email') === false, 'A label holding an address must not produce a nested anchor.');
assert_true(strpos($address_in_label, '<span class="pep-email">john(') !== false, 'That case falls back to a protected span.');

$untouched = $plugin->email_protect('<p><a href="/pricing">Pricing</a> and john@doe.com</p>');
assert_true(strpos($untouched, '<a href="/pricing">Pricing</a>') !== false, 'Anchors without a mailto should be left alone.');

// Stored XSS (CVE-2026-136610). Each payload below is what kses lets through in a
// comment from an unauthenticated visitor: only <a href title>, entities left encoded.

/**
 * True when the markup holds an element or attribute a browser would execute.
 * Parsed as HTML, so text inside a quoted attribute value is not mistaken for markup.
 */
function has_live_script($html)
{
    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<!DOCTYPE html><html><body>' . $html . '</body></html>');
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    foreach ($dom->getElementsByTagName('*') as $element) {
        if (in_array(strtolower($element->nodeName), array('script', 'img', 'svg', 'iframe'), true)) {
            return true;
        }
        foreach ($element->attributes as $attribute) {
            if (stripos($attribute->nodeName, 'on') === 0) {
                return true;
            }
        }
    }
    return false;
}

// The detector itself must see the payloads on the unpatched output shapes.
assert_true(has_live_script('<img src=x onerror=alert(1)>'), 'Detector: live element.');
assert_true(has_live_script('<a title=" href="x onmouseover=alert(1) y">Click</a>'), 'Detector: smuggled attribute.');
assert_true(!has_live_script('<a href="x onmouseover=alert(1) y">Click</a>'), 'Detector: quoted text is inert.');

// 1. Entity-encoded markup in the href was decoded, then returned raw.
$decoded = $plugin->email_protect('<a href="mailto:&lt;img src=x onerror=alert(1)&gt;">john@doe.com</a>');
assert_true(!has_live_script($decoded), 'An entity-encoded href must not be decoded into live markup: ' . $decoded);

$decoded_empty = $plugin->email_protect('<a href="mailto:&lt;img src=x onerror=alert(1)&gt;@doe.com"></a>');
assert_true(!has_live_script($decoded_empty), 'Same, for an anchor without a label: ' . $decoded_empty);

// 2. A fake unquoted href inside another attribute's value swallowed a quote,
//    turning the rest of that value into real attributes.
$smuggled = $plugin->email_protect('<a title="href=mailto:john@doe.com" href="x onmouseover=alert(1) y">Click</a>');
assert_true(!has_live_script($smuggled), 'Attribute text must never become attributes: ' . $smuggled);

$smuggled_sq = $plugin->email_protect('<a title="href=\'mailto:john@doe.com" href="\' onmouseover=alert(1) ">Click</a>');
assert_true(!has_live_script($smuggled_sq), 'Same, with a single-quoted fake href: ' . $smuggled_sq);

// 3. The real href is the one that counts, not text that looks like one.
$decoy = $plugin->email_protect('<a title="href=mailto:john@doe.com" href="/pricing">Pricing</a>');
assert_true(strpos($decoy, 'href="/pricing"') !== false, 'An href-lookalike in a title must not hijack a normal link: ' . $decoy);
assert_true(strpos($decoy, 'data-pep-email') === false, 'That link has no mailto to park.');

// 4. Kept anchors are rebuilt with every attribute value escaped.
$quoted_title = $plugin->email_protect('<a title=\'say "hi"\' href="mailto:john@doe.com">Get in touch</a>');
assert_true(strpos($quoted_title, 'title="say &quot;hi&quot;"') !== false, 'Attribute values should be re-escaped: ' . $quoted_title);
assert_true(!has_live_script($quoted_title), 'And stay inert.');

// 5. The excerpt path shares the mailto handling.
$excerpt = $plugin->email_protect_excerpt('<a href="mailto:&lt;script&gt;alert(1)&lt;/script&gt;">john@doe.com</a>');
assert_true(!has_live_script($excerpt), 'Excerpts must not decode markup either: ' . $excerpt);

echo "All regression tests passed.\n";
