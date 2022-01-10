<?php

use PHPUnit\Framework\TestCase;

/**
 * Glossary Test Case
 *
 * @package glossary
 * @subpackage test
 */

class GlossaryTestCase extends TestCase
{
    /**
     * @var modX $modx
     */
    protected $modx = null;
    /**
     * @var Glossary $glossary
     */
    protected $glossary = null;

    /**
     * Ensure all tests have a reference to the MODX and Quip objects
     */
    public function setUp()
    {
        $this->modx = GlossaryTestHarness::_getConnection();

        $corePath = $this->modx->getOption('glossary.core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/glossary/');
        require_once $corePath . 'model/glossary/glossary.class.php';

        $this->glossary = new Glossary($this->modx);
        $this->glossary->options['debug'] = true;

        $this->modx->placeholders = array();
        $this->modx->glossary = &$this->glossary;

        error_reporting(E_ALL);
    }

    /**
     * Remove reference at end of test case
     */
    public function tearDown()
    {
        $this->modx = null;
        $this->glossary = null;
    }
}
