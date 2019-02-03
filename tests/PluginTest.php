<?php
/**
 * Unit testing the class using PHPUnit.
 * https://phpunit.de/getting-started/phpunit-7.html
 *
 * To run the tests, use this in the Terminal:
 * ./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/PluginTest
 */

//declare (strict_types = 1);

use PHPUnit\Framework\TestCase;

$tests = array('<strong>Year 3C &amp; 3R:</strong> John Doe (Patrick) <a title="Click here to email this contact" href="mailto:john@doe.com" target="_blank" rel="noopener">johndoe@gmail.com</a> / Mandy Doe (Jack) <a href="mailto:joen@gmail.com">Joen@gmail.com</a>
');

class EmailTest extends TestCase
{
    public function setUp()
    {
        // Reproduce wordpress env
        $wp_did_header = true;
        // Load the WordPress library.
        require_once dirname(__FILE__) . '/../wordpress/wp-load.php';
        //define('ABSPATH', dirname(__FILE__) . '/../wordpress/');
    }
    public function testRremoveHyperLink(): void
    {
        $this->assertEquals(
            'user@example.com',
            WP_Email_Protector::removeHyperLink('<a href="mailto:user@example.com">user@example.com</a>')
        );
    }

}
