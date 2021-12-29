<?php
/**
 * Glossary connector
 *
 * @package glossary
 * @subpackage connector
 *
 * @var modX $modx
 */

require_once dirname(__FILE__, 4) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('glossary.core_path', null, $modx->getOption('core_path') . 'components/glossary/');
/** @var Glossary $glossary */
$glossary = $modx->getService('glossary', 'Glossary', $corePath . 'model/glossary/', [
    'core_path' => $corePath
]);

// Handle request
$modx->request->handleRequest([
    'processors_path' => $glossary->getOption('processorsPath'),
    'location' => ''
]);
