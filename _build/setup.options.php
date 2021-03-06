<?php
/**
 * Setup options
 *
 * @package glossary
 * @subpackage build
 *
 * @var array $options
 * @var modX $modx
 */

// Defaults
$defaults = array(
    'resid' => $modx->getOption('0'),
);

$output = '<style type="text/css">
    #modx-setupoptions-panel { display: none; }
    #modx-setupoptions-form p { margin-bottom: 10px; }
    #modx-setupoptions-form h2 { margin-bottom: 15px; }
</style>';

$output = '';
$values = array();
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
        $output .= '<h2>Install Glossary</h2>

        <p>Thanks for installing Glossary. This open source extra was
        developped by Treehill Studio - MODX development in Münsterland.</p>

        <p>During the installation, we will collect some statistical data (the
        hostname, the IP address, the PHP version and the MODX version of your
        MODX installation). Your data will be kept confidential and under no
        circumstances be used for promotional purposes or disclosed to third
        parties.</p>
        
        <p>If you install this package, you are giving us your permission to
        collect, process and use that data for statistical purposes.</p>
        
        <p>Please review the install options carefully.</p>';

        $output .= '<div>
                        <label for="resid">Glossary Resource ID:</label>
                        <input type="text" name="resid" id="resid" width="450" value="' . $defaults['resid'] . '" />
                    </div>';

        break;
    case xPDOTransport::ACTION_UPGRADE:
        $setting = $modx->getObject('modSystemSetting', array('key' => 'glossary.resid'));
        $values['resid'] = ($setting) ? $setting->get('resid') : $defaults['resid'];
        unset($setting);

        $output .= '<h2>Upgrade Glossary</h2>

        <p>Glossary will be upgraded. This open source extra was developped by
        Treehill Studio - MODX development in Münsterland.</p>

        During the upgrade, we will collect some statistical data (the hostname,
        the IP address, the PHP version, the MODX version of your MODX
        installation and the previous installed version of this extra package).
        Your data will be kept confidential and under no circumstances be used
        for promotional purposes or disclosed to third parties.</p>

        <p>If you upgrade this package, you are giving us your permission to
        collect, process and use that data for statistical purposes.</p>';

        $output .= '<div>
                        <label for="resid">Glossary Resource ID:</label>
                        <input type="text" name="resid" id="resid" width="450" value="' . $values['resid'] . '" />
                    </div>
                    <br><br>';

        break;
    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

return $output;
