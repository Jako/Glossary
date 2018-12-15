<?php
/**
 * Update term
 *
 * @package glossary
 * @subpackage processor
 */

class GlossaryTermUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'Term';
    public $languageTopics = array('glossary:default');
    public $objectType = 'glossary.term';

    public function beforeSave()
    {
        $term = $this->getProperty('term');
        if (empty($term)) {
            $this->addFieldError('term', $this->modx->lexicon('glossary.term_err_ns_term'));
        } elseif (preg_match('/[^\d\w-_.:,; ]+/\u', $term)) {
            $this->addFieldError('term', $this->modx->lexicon('glossary.term_err_nv_term'));
        }

        $explanation = $this->getProperty('explanation');
        if (empty($explanation)) {
            $this->addFieldError('explanation', $this->modx->lexicon('glossary.term_err_ns_explanation'));
        };

        $this->object->set('updatedon', time());
        $this->object->set('updatedby', $this->modx->user->get('id'));

        return parent::beforeSave();
    }
}

return 'GlossaryTermUpdateProcessor';
