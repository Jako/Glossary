<?php
/**
 * Glossary Lexicon Tests
 *
 * @package glossary
 * @subpackage test
 */

class GlossaryLexiconTest extends GlossaryTestCase
{
    /**
     * Lexicon keys used by the MODX object processors for the glossary.term object type
     */
    const PROCESSOR_KEYS = [
        'glossary.term_err_nfs',
        'glossary.term_err_ns',
        'glossary.term_err_remove',
        'glossary.term_err_save',
    ];

    /**
     * @dataProvider languageProvider
     */
    public function testAllLanguagesHaveTheEnglishKeys(string $language)
    {
        $english = $this->getEntries('en');
        $entries = $this->getEntries($language);

        $this->assertSame([], array_values(array_diff(array_keys($english), array_keys($entries))), 'Missing keys in ' . $language);
        $this->assertSame([], array_values(array_diff(array_keys($entries), array_keys($english))), 'Unknown keys in ' . $language);
    }

    /**
     * @dataProvider languageProvider
     */
    public function testProcessorKeysExist(string $language)
    {
        $entries = $this->getEntries($language);

        foreach (self::PROCESSOR_KEYS as $key) {
            $this->assertArrayHasKey($key, $entries);
            $this->assertNotSame('', trim($entries[$key]));
        }
    }

    /**
     * @dataProvider languageProvider
     */
    public function testUsedKeysExist(string $language)
    {
        $entries = $this->getEntries($language);

        $this->assertSame([], array_values(array_diff($this->getUsedKeys(), array_keys($entries))));
    }

    public static function languageProvider(): array
    {
        $languages = [];
        foreach (glob(dirname(__DIR__, 2) . '/core/components/glossary/lexicon/*', GLOB_ONLYDIR) as $folder) {
            $languages[basename($folder)] = [basename($folder)];
        }
        return $languages;
    }

    /**
     * Get all lexicon entries of a language.
     *
     * @param string $language
     * @return array
     */
    private function getEntries(string $language): array
    {
        $_lang = [];
        foreach (glob(dirname(__DIR__, 2) . '/core/components/glossary/lexicon/' . $language . '/*.inc.php') as $file) {
            include $file;
        }
        return $_lang;
    }

    /**
     * Get the glossary lexicon keys used in the manager javascript and the PHP code.
     *
     * @return array
     */
    private function getUsedKeys(): array
    {
        $base = dirname(__DIR__, 2) . '/';
        $files = array_merge(
            glob($base . 'source/js/mgr/{,*/}*.js', GLOB_BRACE),
            glob($base . 'core/components/glossary/{src,processors,controllers}/{,*/,*/*/}*.php', GLOB_BRACE)
        );
        $keys = [];
        foreach ($files as $file) {
            preg_match_all("~(?:\\b_\\(|lexicon\\()\\s*'(glossary\\.[^']+)'~", file_get_contents($file), $matches);
            $keys = array_merge($keys, $matches[1]);
        }
        return array_values(array_unique(array_merge($keys, self::PROCESSOR_KEYS)));
    }
}
