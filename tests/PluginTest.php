<?php
/**
 * Unit testing the class using PHPUnit.
 * https://phpunit.de/getting-started/phpunit-7.html
 *
 * To run the tests, use this in the Terminal:
 * ./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/PluginTest
 */

declare (strict_types = 1);
use PHPUnit\Framework\TestCase;

$tests = array('<strong>Year 3C &amp; 3R:</strong> John Doe (Patrick) <a title="Click here to email this contact" href="mailto:john@doe.com" target="_blank" rel="noopener">johndoe@gmail.com</a> / Mandy Doe (Jack) <a href="mailto:joen@gmail.com">Joen@gmail.com</a>
', );

class ParserTest extends TestCase
{
    public function setUp()
    {
    }
    public function testRremoveHyperLink(): void
    {
        $use_cases = array(
            ['email' => 'user@example.com', 'html_string' => '<a href="mailto:user@example.com">user@example.com</a>'],
            ['email' => 'user@sub.example.com', 'html_string' => '<a href="mailto:user@sub.example.com">user@sub.example.com</a>'],
            ['email' => 'foo@bar.co', 'html_string' => '<a href="mailto:foo@bar.co">foo@bar.co</a>'],
            ['email' => 'foo@bar.com', 'html_string' => '<a href="mailto:foo@bar.com">foo@bar.com</a>'],
            ['email' => 'foobar@foo.com', 'html_string' => '<a href="mailto:foobar@foo.com">foobar@foo.com</a>'],
            ['email' => 'bar@foo.co', 'html_string' => '<a href="mailto:bar@foo.co">bar@foo.co</a>'],
            ['email' => 'president@our.org', 'html_string' => '<a href="mailto:president@our.org">president@our.org</a>'],
            ['email' => 'vicepresident@our.co.uk', 'html_string' => '<a href="mailto:vicepresident@our.co.uk">vicepresident@our.co.uk</a>'],
            ['email' => '15characterlong@address.test', 'html_string' => '<a title="This is a great title, man!" href="mailto:15characterlong@address.test" rel="noopener">15characterlong@address.test</a>'],
            ['email' => '<strong>Year 4J:</strong> Georgina Tate (Celia) georgina.tate@googlemail.com', 'html_string' => '<strong>Year 4J:</strong> Georgina Tate (Celia) <a href="mailto:georgina.tate@googlemail.com">georgina.tate@googlemail.com</a>'],
            ['email' => ' / Fiona Dashwood (Oliver) fionajpalmer fionajpalmer@yahoo.com / Patrick Dor (William) patrickdor@yahoo.co.uk', 'html_string' => ' / Fiona Dashwood (Oliver) fionajpalmer <a href="mailto:fionajpalmer@yahoo.com">fionajpalmer@yahoo.com</a> / Patrick Dor (William) <a href="mailto:patrickdor@yahoo.co.uk">patrickdor@yahoo.co.uk</a>'],
        );
        foreach ($use_cases as $test) {
            $this->assertEquals(
                $test['email'],
                Parser::removeHyperLink($test['html_string'])
            );
        }

    }

}
