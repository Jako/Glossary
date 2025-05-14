<?php
/**
 * Resolve setup options
 *
 * @package glossary
 * @subpackage build
 *
 * @var array $options
 * @var xPDOObject $object
 */
$success = false;

if ($object->xpdo) {
    /** @var xPDO $modx */
    $modx =& $object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /** @var modSystemSetting $setting */
            $setting = $modx->getObject('modSystemSetting', [
                'key' => 'glossary.resid'
            ]);
            if ($setting != null) {
                $setting->set('value', $modx->getOption('resid', $options, '0'));
                $setting->save();
            } else {
                $modx->log(xPDO::LOG_LEVEL_ERROR, 'The glossary.resid system setting was not found and can\'t be updated.');
            }

            $success = true;
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            $success = true;
            break;
    }
}
return $success;
