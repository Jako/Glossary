<?php
/**
 * Remove a Term
 *
 * @package glossary
 * @subpackage processors
 */

use TreehillStudio\Glossary\Processors\ObjectRemoveProcessor;

class GlossaryTermRemoveProcessor extends ObjectRemoveProcessor
{
    public $classKey = 'Term';
    public $objectType = 'glossary.term';

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function afterRemove()
    {
        $this->glossary->clearResourceCache();
        return parent::afterRemove();
    }
}

return 'GlossaryTermRemoveProcessor';
