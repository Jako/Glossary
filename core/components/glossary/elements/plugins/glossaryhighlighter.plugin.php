<?php
/**
 * Glossary Plugin
 *
 * @package glossary
 * @subpackage plugin
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$className = 'TreehillStudio\Glossary\Plugins\Events\\' . $modx->event->name;

$corePath = $modx->getOption('glossary.core_path', null, $modx->getOption('core_path') . 'components/glossary/');
/** @var Glossary $glossary */
$glossary = $modx->getService('glossary', 'Glossary', $corePath . 'model/glossary/', [
    'core_path' => $corePath
]);

if ($glossary) {
    if (class_exists($className)) {
        $handler = new $className($modx, $scriptProperties);
        if (get_class($handler) == $className) {
            $handler->run();
        } else {
            $modx->log(xPDO::LOG_LEVEL_ERROR, $className. ' could not be initialized!', '', 'Glossary Plugin');
        }
    } else {
        $modx->log(xPDO::LOG_LEVEL_ERROR, $className. ' was not found!', '', 'Glossary Plugin');
    }
}

return;