<?php
/**
 * Glossary Snippet Tests
 *
 * @package glossary
 * @subpackage test
 */

use TreehillStudio\Glossary\Snippets\GlossarySnippet;

class GlossarySnippetTest extends GlossaryTestCase
{
    /**
     * @var string Prefix of the terms created by the test
     */
    private $prefix;

    public function setUp(): void
    {
        parent::setUp();
        $this->prefix = 'Zz' . strtolower(self::TEST_PREFIX) . uniqid();
    }

    public function testDefaultProperties()
    {
        $snippet = new GlossarySnippet($this->modx, []);

        $this->assertSame([
            'outerTpl' => 'Glossary.listOuterTpl',
            'groupTpl' => 'Glossary.listGroupTpl',
            'termTpl' => 'Glossary.listItemTpl',
            'showNav' => true,
            'navOuterTpl' => 'Glossary.navOuterTpl',
            'navItemTpl' => 'Glossary.navItemTpl',
            'showEmptySections' => false,
            'toPlaceholder' => '',
        ], $snippet->getProperties());
    }

    /**
     * @dataProvider booleanPropertyProvider
     */
    public function testBooleanProperties($value, bool $expected)
    {
        $snippet = new GlossarySnippet($this->modx, ['showNav' => $value, 'showEmptySections' => $value]);

        $this->assertSame($expected, $snippet->getProperty('showNav'));
        $this->assertSame($expected, $snippet->getProperty('showEmptySections'));
    }

    public static function booleanPropertyProvider(): array
    {
        return [
            'string 1' => ['1', true],
            'string 0' => ['0', false],
            'string true' => ['true', true],
            'string false' => ['false', false],
            'bool true' => [true, true],
        ];
    }

    public function testOutput()
    {
        $this->createTerm($this->prefix . ' Beta');
        $this->createTerm($this->prefix . ' Alpha');

        $output = $this->runSnippet();

        $this->assertStringStartsWith('<nav>', $output);
        $this->assertStringContainsString('<nav>', $output);
        $this->assertStringContainsString('Z,', $output);
        $this->assertStringContainsString('<li id="Z">', $output);
        $this->assertMatchesRegularExpression(
            '~' . preg_quote($this->termOutput('Alpha'), '~') . preg_quote($this->termOutput('Beta'), '~') . '~',
            $output,
            'The terms are not rendered in alphabetical order.'
        );
    }

    public function testAnchorMatchesHighlighterLink()
    {
        $term = $this->prefix . ' Ärger "quoted"';
        $this->createTerm($term);

        $output = $this->runSnippet();
        $links = $this->glossary->getTerms($this->createChunk('[[+link]]'));

        $anchor = substr($links[$term], strpos($links[$term], '#') + 1);
        $this->assertStringContainsString($anchor . ':' . $term . ':Z;', $output);
    }

    public function testDuplicateAnchorsAreUnique()
    {
        $this->createTerm($this->prefix . ' Foo Bar');
        $this->createTerm($this->prefix . ' Foo-Bar');

        $output = $this->runSnippet();

        $anchor = strtolower($this->prefix) . '-foo-bar';
        $this->assertStringContainsString($anchor . ':' . $this->prefix . ' Foo Bar:Z;', $output);
        $this->assertStringContainsString($anchor . '-2:' . $this->prefix . ' Foo-Bar:Z;', $output);
    }

    public function testHideNav()
    {
        $this->createTerm($this->prefix);

        $output = $this->runSnippet(['showNav' => '0']);

        $this->assertStringNotContainsString('<nav>', $output);
        $this->assertStringStartsWith('<ul>', $output);
    }

    public function testShowEmptySections()
    {
        $output = $this->runSnippet(['showEmptySections' => '1']);

        foreach (range('A', 'Z') as $letter) {
            $this->assertMatchesRegularExpression('~<nav>.*' . $letter . '(?:disabled)?,.*</nav>~s', $output);
        }
    }

    public function testToPlaceholder()
    {
        $this->createTerm($this->prefix . ' Alpha');

        $output = $this->runSnippet(['toPlaceholder' => 'glossaryTest']);

        $this->assertSame('', $output);
        $nav = $this->modx->getPlaceholder('glossaryTest.nav');
        $items = $this->modx->getPlaceholder('glossaryTest.items');
        $this->assertStringStartsWith('<nav>', $nav);
        $this->assertStringStartsWith('<ul>', $items);
        $this->assertStringContainsString($this->termOutput('Alpha'), $items);
        $this->assertSame($nav . $items, $this->modx->getPlaceholder('glossaryTest'));
    }

    /**
     * Run the snippet with test chunks.
     *
     * @param array $properties
     * @return string
     */
    private function runSnippet(array $properties = []): string
    {
        $snippet = new GlossarySnippet($this->modx, array_merge([
            'outerTpl' => $this->createChunk('<ul>[[+groups]]</ul>'),
            'groupTpl' => $this->createChunk('<li id="[[+letter]]">[[+items]]</li>'),
            'termTpl' => $this->createChunk('[[+anchor]]:[[+term]]:[[+letter]];'),
            'navOuterTpl' => $this->createChunk('<nav>[[+letters]]</nav>'),
            'navItemTpl' => $this->createChunk('[[+letter]][[+class]],'),
        ], $properties));
        return $snippet->execute();
    }

    /**
     * Get the expected termTpl output of a test term.
     *
     * @param string $suffix
     * @return string
     */
    private function termOutput(string $suffix): string
    {
        $term = $this->prefix . ' ' . $suffix;
        return $this->glossary->getAnchor($term) . ':' . $term . ':Z;';
    }
}
