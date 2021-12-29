<?php
/**
 * Abstract update processor
 *
 * @package glossary
 * @subpackage processors
 */

namespace TreehillStudio\Glossary\Processors;

use TreehillStudio\Glossary\Glossary;
use modObjectUpdateProcessor;
use modX;

/**
 * Class ObjectUpdateProcessor
 */
class ObjectUpdateProcessor extends modObjectUpdateProcessor
{
    public $languageTopics = ['glossary:default'];

    /** @var Glossary $glossary */
    public $glossary;

    protected $required = [];

    /**
     * {@inheritDoc}
     * @param modX $modx A reference to the modX instance
     * @param array $properties An array of properties
     */
    public function __construct(modX &$modx, array $properties = [])
    {
        parent::__construct($modx, $properties);

        $corePath = $this->modx->getOption('glossary.core_path', null, $this->modx->getOption('core_path') . 'components/glossary/');
        $this->glossary = $this->modx->getService('glossary', 'Glossary', $corePath . 'model/glossary/');
    }

    /**
     * Get a boolean property.
     * @param string $k
     * @param mixed $default
     * @return bool
     */
    public function getBooleanProperty($k, $default = null)
    {
        return ($this->getProperty($k, $default) === 'true' || $this->getProperty($k, $default) === true || $this->getProperty($k, $default) === '1' || $this->getProperty($k, $default) === 1);
    }

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function beforeSave()
    {
        foreach ($this->required as $required) {
            $value = $this->getProperty($required);
            if (empty($value)) {
                $this->addFieldError($required, $this->modx->lexicon('field_required'));
            }
        }

        return parent::beforeSave();
    }
}
