<?php
/**
 * Glossary Highlight Terms Output Filter
 *
 * @package glossary
 * @subpackage snippet
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('glossary.core_path', null, $modx->getOption('core_path') . 'components/glossary/');
/** @var GlossaryBase $glossary */
$glossary = $modx->getService('glossary', 'GlossaryBase', $corePath . 'model/glossary/', array(
    'core_path' => $corePath
));

// Get options
$input = $modx->getOption('input', $scriptProperties, '', true);

$targetResId = $glossary->getOption('resid');
$chunkName = $glossary->getOption('tpl');
$output = '';

if ($modx->getCount('modResource', $targetResId)) {
    if ($modx->resource->get('id') != $targetResId) {
        $terms = $glossary->getTerms($chunkName);
        $output = $glossary->highlightTerms($input, $terms);
    }
} else {
    if ($glossary->getOption('debug')) {
        $modx->log(xPDO::LOG_LEVEL_ERROR, 'The MODX System Setting "glossary.resid" does not point to a published and undeleted resource.', '', 'GlossaryHighlightTerms');
    }
}

return $output;
