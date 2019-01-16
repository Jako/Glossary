<?php
/**
 * Resolve creating db tables
 *
 * THIS RESOLVER IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package glossary
 * @subpackage build
 *
 * @var mixed $object
 * @var modX $modx
 * @var array $options
 */

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modelPath = $modx->getOption('glossary.core_path', null, $modx->getOption('core_path') . 'components/glossary/') . 'model/';
            
            $modx->addPackage('glossary', $modelPath, null);


            $manager = $modx->getManager();

            $manager->createObjectContainer('Term');

            break;
    }
}

return true;