<?php
/**
 * Glossary Term Highlighter Plugin
 *
 * @package glossary
 * @subpackage plugin
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$className = 'Glossary' . $modx->event->name;

$corePath = $modx->getOption('glossary.core_path', null, $modx->getOption('core_path') . 'components/glossary/');
/** @var GlossaryBase $glossary */
$glossary = $modx->getService('glossary', 'GlossaryBase', $corePath . 'model/glossary/', array(
    'core_path' => $corePath
));

$modx->loadClass('GlossaryPlugin', $glossary->getOption('modelPath') . 'glossary/events/', true, true);
$modx->loadClass($className, $glossary->getOption('modelPath') . 'glossary/events/', true, true);
if (class_exists($className)) {
    /** @var GlossaryPlugin $handler */
    $handler = new $className($modx, $scriptProperties);
    $handler->run();
}

return;
