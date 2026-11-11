<?php
/**
 * @package glossary
 * @subpackage plugin
 */

namespace TreehillStudio\Glossary\Plugins\Events;

use TreehillStudio\Glossary\Plugins\Plugin;
use xPDO;

class OnLoadWebDocument extends Plugin
{
    /**
     * The mime types of resources, in which the terms are highlighted
     * @var string[]
     */
    protected $mimeTypes = ['text/html', 'application/xhtml+xml'];

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function init()
    {
        $resource = $this->modx->resource;
        if (!$resource) {
            return false;
        }
        // The content of static resources, weblinks and symlinks is a path, url or resource id
        foreach (['modStaticResource', 'modWebLink', 'modSymLink'] as $class) {
            if (is_a($resource, $class) || is_a($resource, 'MODX\\Revolution\\' . $class)) {
                return false;
            }
        }
        /** @var \modContentType $contentType */
        $contentType = $resource->getOne('ContentType');
        if ($contentType && !in_array(strtolower($contentType->get('mime_type')), $this->mimeTypes)) {
            return false;
        }
        return true;
    }

    public function process()
    {
        $contexts = $this->glossary->getOption('enabledContexts');
        if ($contexts) {
            $contexts = array_map('trim', explode(',', $contexts));
            if (!in_array($this->modx->context->key, $contexts)) {
                return;
            }
        }
        $templates = $this->glossary->getOption('enabledTemplates');
        if ($templates) {
            $templates = array_map('trim', explode(',', $templates));
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
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'The MODX System Setting "glossary.resid" does not point to an existing resource.', '', 'GlossaryPlugin');
            }
        }
    }
}
