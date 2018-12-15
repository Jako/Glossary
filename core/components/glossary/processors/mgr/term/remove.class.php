<?php
/**
 * Remove term
 *
 * @package glossary
 * @subpackage processor
 */

class GlossaryTermRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'Term';
    public $languageTopics = array('glossary:default');
    public $objectType = 'glossary.term';
}

return 'GlossaryTermRemoveProcessor';
