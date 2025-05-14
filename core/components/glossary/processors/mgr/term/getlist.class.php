<?php
/**
 * Get list Terms
 *
 * @package glossary
 * @subpackage processors
 */

use TreehillStudio\Glossary\Processors\ObjectGetListProcessor;

class GlossaryTermGetListProcessor extends ObjectGetListProcessor
{
    public $classKey = 'Term';
    public $defaultSortField = 'term';
    public $objectType = 'glossary.term';

    /**
     * {@inheritDoc}
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where([
                'term:LIKE' => '%' . $query . '%',
                'OR:explanation:LIKE' => '%' . $query . '%',
            ]);
        }
        return $c;
    }
}

return 'GlossaryTermGetListProcessor';
