<?php
/**
 * @package glossary
 * @subpackage plugin
 */

class GlossaryOnLoadWebDocument extends GlossaryPlugin
{
    public function run()
    {
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
