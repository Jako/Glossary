<?php

use PHPUnit\Framework\TestCase;

/**
 * Glossary Test Case
 *
 * @package glossary
 * @subpackage test
 */

abstract class GlossaryTestCase extends TestCase
{
    /**
     * Prefix of all database objects created by the tests
     */
    const TEST_PREFIX = 'GlossaryTest';

    /**
     * @var modX $modx
     */
    protected $modx = null;
    /**
     * @var Glossary $glossary
     */
    protected $glossary = null;

    /**
     * @var xPDOObject[] Database objects created by the test, removed in tearDown
     */
    private $createdObjects = [];

    /**
     * @var bool The lexicon cache is refreshed once per test run, so that changed lexicon files are used
     */
    private static $lexiconRefreshed = false;

    /**
     * Ensure all tests have a reference to the MODX and Glossary objects
     */
    public function setUp(): void
    {
        $this->modx = GlossaryTestHarness::_getConnection();
        if (!self::$lexiconRefreshed) {
            $this->modx->getCacheManager()->refresh(['lexicon_topics' => []]);
            self::$lexiconRefreshed = true;
        }

        $corePath = $this->modx->getOption('glossary.core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/glossary/');
        require_once $corePath . 'model/glossary/glossary.class.php';

        // Use a fresh instance, so that changed options don't leak into other tests
        $this->glossary = new Glossary($this->modx);
        $this->glossary->options['debug'] = true;

        $this->modx->placeholders = [];
        // Snippets, plugins and processors get this instance with getService
        $this->modx->services['glossary'] = &$this->glossary;
        $this->modx->glossary = &$this->glossary;

        error_reporting(E_ALL);
    }

    /**
     * Remove the created database objects and the references at the end of the test case
     */
    public function tearDown(): void
    {
        foreach (array_reverse($this->createdObjects) as $object) {
            $object->remove();
        }
        $this->createdObjects = [];

        unset($this->modx->services['glossary'], $this->modx->glossary);
        $this->modx = null;
        $this->glossary = null;
    }

    /**
     * Get the content of a test data file.
     *
     * @param string $name The filename relative to the test data folder
     * @return string
     */
    protected function getData(string $name): string
    {
        return file_get_contents(__DIR__ . '/Data/' . $name);
    }

    /**
     * Highlight the terms in a text with changed Glossary options.
     *
     * @param string $text
     * @param array $terms
     * @param array $options
     * @return string
     */
    protected function highlight(string $text, array $terms, array $options = []): string
    {
        $this->glossary->options = array_merge($this->glossary->options, $options);
        return $this->glossary->highlightTerms($text, $terms);
    }

    /**
     * Create a Glossary term, that is removed at the end of the test.
     *
     * @param string $term
     * @param string $explanation
     * @return Term
     */
    protected function createTerm(string $term, string $explanation = 'Explanation'): Term
    {
        /** @var Term $object */
        $object = $this->modx->newObject('Term', [
            'term' => $term,
            'explanation' => $explanation,
        ]);
        $this->assertTrue($object->save(), 'The test term "' . $term . '" could not be saved.');
        return $this->trackObject($object);
    }

    /**
     * Create a chunk, that is removed at the end of the test.
     *
     * @param string $content
     * @return string The chunk name
     */
    protected function createChunk(string $content): string
    {
        /** @var modChunk $chunk */
        $chunk = $this->modx->newObject('modChunk', [
            'name' => self::TEST_PREFIX . '.' . uniqid(),
            'snippet' => $content,
        ]);
        $this->assertTrue($chunk->save(), 'The test chunk could not be saved.');
        $this->trackObject($chunk);
        return $chunk->get('name');
    }

    /**
     * Remove a database object at the end of the test.
     *
     * @param xPDOObject $object
     * @return xPDOObject
     */
    protected function trackObject($object)
    {
        $this->createdObjects[] = $object;
        return $object;
    }
}
