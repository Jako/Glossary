<?php
/**
 * @package glossary
 * @subpackage plugin
 */

abstract class GlossaryPlugin
{
    /** @var modX $modx */
    protected $modx;
    /** @var GlossaryBase $glossary */
    protected $glossary;
    /** @var array $scriptProperties */
    protected $scriptProperties;

    public function __construct($modx, &$scriptProperties)
    {
        $this->scriptProperties =& $scriptProperties;
        $this->modx = &$modx;
        $corePath = $this->modx->getOption('glossary.core_path', null, $this->modx->getOption('core_path') . 'components/glossary/');
        $this->glossary = $this->modx->getService('glossary', 'GlossaryBase', $corePath . 'model/glossary/', array(
            'core_path' => $corePath
        ));
    }

    abstract public function run();
}
