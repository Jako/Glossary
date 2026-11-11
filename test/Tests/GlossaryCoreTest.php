<?php
/**
 * Glossary Core Tests
 *
 * @package glossary
 * @subpackage test
 */

class GlossaryCoreTest extends GlossaryTestCase
{
    const TEMPLATE_TERM = [
        'Template' => '<span class="glossary-term" data-toggle="tooltip" data-html="true" title="" data-original-title="<p>Template <strong>Test</strong> Template</p>">Template</span>'
    ];

    /**
     * @dataProvider pageProvider
     */
    public function testPage(bool $fullwords, string $resultFile)
    {
        $result = $this->highlight($this->getData('Page/source.page.tpl'), self::TEMPLATE_TERM, [
            'sections' => true,
            'fullwords' => $fullwords,
        ]);

        $this->assertSame($this->getData('Page/' . $resultFile), $result);
    }

    public static function pageProvider(): array
    {
        return [
            'partial words' => [false, 'result.page.tpl'],
            'full words' => [true, 'result_fullwords.page.tpl'],
        ];
    }

    /**
     * @dataProvider fullwordsProvider
     */
    public function testFullwords(array $terms, string $source, string $expected)
    {
        $this->assertSame($expected, $this->highlight($source, $terms, ['fullwords' => true]));
    }

    public static function fullwordsProvider(): array
    {
        return [
            'single term' => [['Foo' => '[F]'], '<p>Foo</p>', '<p>[F]</p>'],
            'multiple occurrences' => [['Foo' => '[F]'], '<p>Foo, Foo and Foo.</p>', '<p>[F], [F] and [F].</p>'],
            'no partial words' => [['Foo' => '[F]'], '<p>Foobar xFoo Foo_bar Foo1</p>', '<p>Foobar xFoo Foo_bar Foo1</p>'],
            'case sensitive' => [['Foo' => '[F]'], '<p>foo FOO Foo</p>', '<p>foo FOO [F]</p>'],
            'unicode word boundaries' => [['Füße' => '[F]'], '<p>Füße Füßen äFüße</p>', '<p>[F] Füßen äFüße</p>'],
            'term with a space' => [['Foo Bar' => '[FB]'], '<p>Foo Bar Foo  Bar</p>', '<p>[FB] Foo  Bar</p>'],
            'special characters' => [['C++' => '[C]', '.NET' => '[N]'], '<p>C++, .NET, C++x, ASP.NET</p>', '<p>[C], [N], C++x, ASP.NET</p>'],
            'regex characters' => [['a.b (c)' => '[R]'], '<p>a.b (c) axb (c)</p>', '<p>[R] axb (c)</p>'],
            'numeric term' => [[2024 => '[Y]'], '<p>2024 and 12024</p>', '<p>[Y] and 12024</p>'],
            'empty term' => [['' => '[E]', 'Foo' => '[F]'], '<p>Foo</p>', '<p>[F]</p>'],
        ];
    }

    /**
     * @dataProvider partialWordsProvider
     */
    public function testPartialWords(array $terms, string $source, string $expected)
    {
        $this->assertSame($expected, $this->highlight($source, $terms, ['fullwords' => false]));
    }

    public static function partialWordsProvider(): array
    {
        return [
            'partial words' => [['Foo' => '[F]'], '<p>Foobar xFoo Foo</p>', '<p>[F]bar x[F] [F]</p>'],
            'case sensitive' => [['Foo' => '[F]'], '<p>foobar Foobar</p>', '<p>foobar [F]bar</p>'],
            'overlapping terms' => [['Foo' => '[F]', 'Foobar' => '[FB]'], '<p>Foobar Foo</p>', '<p>[FB] [F]</p>'],
        ];
    }

    /**
     * @dataProvider overlappingTermsProvider
     */
    public function testOverlappingTerms(array $terms)
    {
        $this->assertSame(
            '<p>[FB] is great, [F] too</p>',
            $this->highlight('<p>Foo Bar is great, Foo too</p>', $terms)
        );
    }

    public static function overlappingTermsProvider(): array
    {
        return [
            'longer term first' => [['Foo Bar' => '[FB]', 'Foo' => '[F]']],
            'shorter term first' => [['Foo' => '[F]', 'Foo Bar' => '[FB]']],
        ];
    }

    public function testReplacementIsNotHighlightedAgain()
    {
        $terms = [
            'Foo' => '<span title="Bar">Foo Bar</span>',
            'Bar' => '<span title="Foo">Bar Foo</span>',
        ];
        $this->assertSame(
            '<p><span title="Bar">Foo Bar</span> <span title="Foo">Bar Foo</span></p>',
            $this->highlight('<p>Foo Bar</p>', $terms)
        );
    }

    /**
     * @dataProvider protectedHtmlProvider
     */
    public function testProtectedHtml(string $source, string $expected)
    {
        $this->assertSame($expected, $this->highlight($source, ['Foo' => '[F]'], [
            'disabledTags' => 'a,form,select',
        ]));
    }

    public static function protectedHtmlProvider(): array
    {
        return [
            'double quoted attribute' => ['<p title="a Foo">Foo</p>', '<p title="a Foo">[F]</p>'],
            'single quoted attribute' => ["<p title='a Foo'>Foo</p>", "<p title='a Foo'>[F]</p>"],
            'unquoted attribute' => ['<p class=Foo>Foo</p>', '<p class=Foo>[F]</p>'],
            'greater than in attribute' => ['<p data-x="a > Foo">Foo</p>', '<p data-x="a > Foo">[F]</p>'],
            'tag name' => ['<Foo>Foo</Foo>', '<Foo>[F]</Foo>'],
            'html comment' => ['<!-- Foo -->Foo', '<!-- Foo -->[F]'],
            'disabled tag' => ['<a href="#">Foo</a> Foo', '<a href="#">Foo</a> [F]'],
            'disabled tag with nested tags' => ['<a href="#"><b>Foo</b></a> Foo', '<a href="#"><b>Foo</b></a> [F]'],
            'uppercase disabled tag' => ['<A HREF="#">Foo</A> Foo', '<A HREF="#">Foo</A> [F]'],
            'tags starting with a disabled tag name' => [
                '<article><abbr>Foo</abbr><a href="#">Foo</a> Foo</article>',
                '<article><abbr>[F]</abbr><a href="#">Foo</a> [F]</article>'
            ],
            'multiline disabled tag' => ["<form>\nFoo\n</form> Foo", "<form>\nFoo\n</form> [F]"],
            'less than in text' => ['<p>1 < 2 Foo</p>', '<p>1 < 2 [F]</p>'],
            'script' => ['<script>var Foo = "<b>Foo</b>";</script> Foo', '<script>var Foo = "<b>Foo</b>";</script> [F]'],
            'script with attributes' => ['<SCRIPT type="module">Foo()</SCRIPT> Foo', '<SCRIPT type="module">Foo()</SCRIPT> [F]'],
            'style' => ['<style>.Foo { color: red }</style> Foo', '<style>.Foo { color: red }</style> [F]'],
            'textarea' => ['<textarea name="x">Foo</textarea> Foo', '<textarea name="x">Foo</textarea> [F]'],
            'modx chunk' => ['[[$Foo]] Foo', '[[$Foo]] [F]'],
            'modx tags' => ['[[*Foo]] [[+Foo]] [[++Foo]] [[~1? &Foo=`1`]] Foo', '[[*Foo]] [[+Foo]] [[++Foo]] [[~1? &Foo=`1`]] [F]'],
            'modx snippet properties' => [
                '[[!getResources? &tpl=`Foo` &where=`{"pagetitle":"Foo"}`]] Foo',
                '[[!getResources? &tpl=`Foo` &where=`{"pagetitle":"Foo"}`]] [F]'
            ],
            'nested modx tags' => ['[[!Foo? &id=`[[*Foo]]` &x=`[[+Foo:is=`1`:then=`Foo`]]`]] Foo', '[[!Foo? &id=`[[*Foo]]` &x=`[[+Foo:is=`1`:then=`Foo`]]`]] [F]'],
            'modx tag with html' => ['[[+x:then=`<b>Foo</b>`]] Foo', '[[+x:then=`<b>Foo</b>`]] [F]'],
            'modx tag in attribute' => ['<a href="[[~[[*Foo]]]]">x</a><p data-x="[[+Foo]]">Foo</p>', '<a href="[[~[[*Foo]]]]">x</a><p data-x="[[+Foo]]">[F]</p>'],
            'unbalanced brackets' => ['<p>[[ Foo ] Foo]</p>', '<p>[[ [F] ] [F]]</p>'],
            'single brackets' => ['<p>[Foo] [[+a]] Foo</p>', '<p>[[F]] [[+a]] [F]</p>'],
        ];
    }

    /**
     * @dataProvider disabledTagsProvider
     */
    public function testDisabledTagsOption(string $disabledTags, string $expected)
    {
        $source = '<a href="#">Foo</a><b>Foo</b><i>Foo</i>';
        $this->assertSame($expected, $this->highlight($source, ['Foo' => '[F]'], [
            'disabledTags' => $disabledTags,
        ]));
    }

    public static function disabledTagsProvider(): array
    {
        return [
            'none' => ['', '<a href="#">[F]</a><b>[F]</b><i>[F]</i>'],
            'one' => ['b', '<a href="#">[F]</a><b>Foo</b><i>[F]</i>'],
            'with spaces' => [' a , i ', '<a href="#">Foo</a><b>[F]</b><i>Foo</i>'],
            'empty values' => ['b,,', '<a href="#">[F]</a><b>Foo</b><i>[F]</i>'],
        ];
    }

    /**
     * @dataProvider sectionsProvider
     */
    public function testSections(string $start, string $end)
    {
        $source = "Foo {$start}Foo{$end} Foo {$start}Bar Foo{$end}";
        $this->assertSame('Foo [F] Foo Bar [F]', $this->highlight($source, ['Foo' => '[F]'], [
            'sections' => true,
            'sectionsStart' => $start,
            'sectionsEnd' => $end,
        ]));
    }

    public static function sectionsProvider(): array
    {
        return [
            'default markers' => ['<!-- GlossaryStart -->', '<!-- GlossaryEnd -->'],
            'markers with regex characters' => ['[[start]]', '[[end]]'],
            'markers with the regex delimiter' => ['~start~', '~end~'],
        ];
    }

    public function testSectionsDisabled()
    {
        $source = 'Foo <!-- GlossaryStart -->Foo<!-- GlossaryEnd -->';
        $this->assertSame('[F] [F]', $this->highlight($source, ['Foo' => '[F]'], [
            'sections' => false,
        ]));
    }

    public function testSectionsWithoutMarkersInText()
    {
        $this->assertSame('Foo', $this->highlight('Foo', ['Foo' => '[F]'], [
            'sections' => true,
        ]));
    }

    public function testNoTerms()
    {
        $this->assertSame('<p>Foo</p>', $this->highlight('<p>Foo</p>', []));
    }

    /**
     * @dataProvider anchorProvider
     */
    public function testGetAnchor(string $term, string $expected)
    {
        $this->assertSame($expected, $this->glossary->getAnchor($term));
    }

    public static function anchorProvider(): array
    {
        return [
            'lowercase' => ['Foo', 'foo'],
            'spaces' => ['Foo Bar', 'foo-bar'],
            'transliteration' => ['Ärger über Öl', 'arger-uber-ol'],
            'quotes' => ['"Quoted" term', 'quoted-term'],
            'special characters' => ['C++ & .NET', 'c--net'],
            'allowed characters' => ['foo_bar-1', 'foo_bar-1'],
            'leading and trailing dashes' => ['-Foo-', 'foo'],
        ];
    }

    public function testGetAnchorWithoutTransliteration()
    {
        $anchor = $this->glossary->getAnchor('Привет мир');
        $this->assertNotSame('', trim($anchor, '-'));
        $this->assertSame($anchor, $this->glossary->getAnchor('Привет мир'));
    }

    public function testGetTerms()
    {
        $prefix = self::TEST_PREFIX . uniqid();
        $this->createTerm($prefix . ' Foo', 'Explanation with <b>"html"</b> & \'quotes\'');
        $this->glossary->options['html'] = true;
        $this->glossary->options['resid'] = 0;

        $terms = $this->glossary->getTerms($this->createChunk('[[+term]]|[[+link]]|[[+explanation]]|[[+html]]'));

        $this->assertArrayHasKey($prefix . ' Foo', $terms);
        $this->assertSame(
            $prefix . ' Foo|#' . strtolower($prefix) . '-foo|Explanation with &lt;b&gt;&quot;html&quot;&lt;/b&gt; &amp; &#039;quotes&#039;|1',
            $terms[$prefix . ' Foo']
        );
    }

    public function testGetTermsWithoutHtml()
    {
        $prefix = self::TEST_PREFIX . uniqid();
        $this->createTerm($prefix);
        $this->glossary->options['html'] = false;

        $terms = $this->glossary->getTerms($this->createChunk('[[+html]]'));

        $this->assertSame('', $terms[$prefix]);
    }

    public function testGetTermsLinkMatchesSnippetAnchor()
    {
        $term = self::TEST_PREFIX . uniqid() . ' Ärger "quoted"';
        $this->createTerm($term);

        $terms = $this->glossary->getTerms($this->createChunk('[[+link]]'));

        $this->assertStringEndsWith('#' . $this->glossary->getAnchor($term), $terms[$term]);
    }

    public function testGetTermAnchorsAreUnique()
    {
        $prefix = self::TEST_PREFIX . uniqid();
        $this->createTerm($prefix . ' Foo Bar');
        $this->createTerm($prefix . ' Foo-Bar');
        $this->createTerm($prefix . ' Foo Bar 2');

        $anchors = $this->glossary->getTermAnchors();

        $anchor = strtolower($prefix) . '-foo-bar';
        $this->assertSame($anchor, $anchors[$prefix . ' Foo Bar']);
        $this->assertSame($anchor . '-2', $anchors[$prefix . ' Foo-Bar']);
        $this->assertSame($anchor . '-2-2', $anchors[$prefix . ' Foo Bar 2']);
        $this->assertSame(count($anchors), count(array_unique($anchors)));
    }

    public function testGetTermsAndGroupedTermsUseUniqueAnchors()
    {
        $prefix = 'Zz' . strtolower(self::TEST_PREFIX) . uniqid();
        $this->createTerm($prefix . ' Foo Bar');
        $this->createTerm($prefix . ' Foo-Bar');

        $links = $this->glossary->getTerms($this->createChunk('[[+link]]'));
        $grouped = array_column($this->glossary->getGroupedTerms(false)['Z'], 'anchor', 'term');

        $anchor = strtolower($prefix) . '-foo-bar';
        $this->assertStringEndsWith('#' . $anchor, $links[$prefix . ' Foo Bar']);
        $this->assertStringEndsWith('#' . $anchor . '-2', $links[$prefix . ' Foo-Bar']);
        $this->assertSame($anchor, $grouped[$prefix . ' Foo Bar']);
        $this->assertSame($anchor . '-2', $grouped[$prefix . ' Foo-Bar']);
    }

    public function testGetGroupedTerms()
    {
        $prefix = 'Zz' . strtolower(self::TEST_PREFIX) . uniqid();
        // Created in reverse order to test the sorting
        $this->createTerm($prefix . ' Beta');
        $this->createTerm($prefix . ' Alpha');
        $this->createTerm('Ä' . $prefix);

        $letters = $this->glossary->getGroupedTerms(false);

        $this->assertSame([$prefix . ' Alpha', $prefix . ' Beta'], $this->filterTerms($letters['Z'], $prefix));
        $this->assertSame(['Ä' . $prefix], $this->filterTerms($letters['A'], 'Ä' . $prefix));
        $this->assertSame(array_keys($letters), $this->sortedKeys($letters));
        foreach ($letters as $terms) {
            $this->assertNotEmpty($terms);
        }
    }

    public function testGetGroupedTermsShowEmptySections()
    {
        $letters = $this->glossary->getGroupedTerms(true);

        foreach (range('A', 'Z') as $letter) {
            $this->assertArrayHasKey($letter, $letters);
        }
        $this->assertSame(array_keys($letters), $this->sortedKeys($letters));
    }

    /**
     * Get the term names of a letter group, that start with a prefix.
     *
     * @param array $terms
     * @param string $prefix
     * @return array
     */
    private function filterTerms(array $terms, string $prefix): array
    {
        return array_values(array_filter(array_column($terms, 'term'), function ($term) use ($prefix) {
            return strpos($term, $prefix) === 0;
        }));
    }

    /**
     * @param array $letters
     * @return array
     */
    private function sortedKeys(array $letters): array
    {
        $keys = array_keys($letters);
        sort($keys, SORT_NATURAL);
        return $keys;
    }
}
