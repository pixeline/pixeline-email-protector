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
            ['answer' => 'user@example.com', 'try' => '<a href="mailto:user@example.com">user@example.com</a>'],
            ['answer' => 'user@sub.example.com', 'try' => '<a href="mailto:user@sub.example.com">user@sub.example.com</a>'],
        );
        foreach ($use_cases as $test) {
            $this->assertEquals(
                $test['answer'],
                Parser::removeHyperLink($test['try'])
            );
        }

    }

}
