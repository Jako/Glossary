<?php
/**
 * Glossary Snippet
 *
 * @package glossary
 * @subpackage snippet
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

use TreehillStudio\Glossary\Snippets\GlossarySnippet;

$corePath = $modx->getOption('glossary.core_path', null, $modx->getOption('core_path') . 'components/glossary/');
/** @var Glossary $glossary */
$glossary = $modx->getService('glossary', 'Glossary', $corePath . 'model/glossary/', [
    'core_path' => $corePath
]);

$snippet = new GlossarySnippet($modx, $scriptProperties);
if ($snippet instanceof TreehillStudio\Glossary\Snippets\GlossarySnippet) {
    return $snippet->execute();
}
return 'TreehillStudio\Glossary\Snippets\GlossarySnippet class not found';