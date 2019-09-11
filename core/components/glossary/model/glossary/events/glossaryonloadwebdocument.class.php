<?php
/**
 * @package glossary
 * @subpackage plugin
 */

class GlossaryOnLoadWebDocument extends GlossaryPlugin
{
    public function run()
    {
        $contexts = $this->glossary->getOption('enabledContexts');
        if ($contexts) {
            $contexts = explode(',', $contexts);
            if (!in_array($this->modx->context->key, $contexts)) {
                return;
            }
        }
        $templates = $this->glossary->getOption('enabledTemplates');
        if ($templates) {
            $templates = explode(',', $templates);
            if (!in_array($this->modx->resource->get('template'), $templates)) {
                return;
            }
        }

        $targetResId = $this->glossary->getOption('resid');
        $chunkName = $this->glossary->getOption('tpl');

        if ($this->modx->getCount('modResource', $targetResId)) {
            if ($this->modx->resource->get('id') != $targetResId) {
                $content = $this->modx->resource->get('content');
                $terms = $this->glossary->getTerms($chunkName);
                $newContent = $this->glossary->highlightTerms($content, $terms);
                $this->modx->resource->set('content', $newContent);
            }
        } else {
            if ($this->glossary->getOption('debug')) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'The MODX System Setting "glossary.resid" does not point to a published and undeleted resource.', '', 'GlossaryPlugin');
            }
        }
    }
}
