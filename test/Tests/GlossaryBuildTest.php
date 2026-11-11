<?php
/**
 * Glossary Build Tests
 *
 * @package glossary
 * @subpackage test
 */

class GlossaryBuildTest extends GlossaryTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        if (!class_exists('xPDOTransport') && !class_exists('xPDO\Transport\xPDOTransport')) {
            $this->modx->loadClass('transport.xPDOTransport', XPDO_CORE_PATH, true, true);
        }
        if (!class_exists('xPDOTransport') && class_exists('xPDO\Transport\xPDOTransport')) {
            class_alias('xPDO\Transport\xPDOTransport', 'xPDOTransport');
        }
    }

    public function testSetupOptionsInstall()
    {
        $output = $this->getSetupOptions(xPDOTransport::ACTION_INSTALL);

        $this->assertStringContainsString('<h2>Install Glossary</h2>', $output);
        $this->assertStringContainsString('name="resid" id="resid" width="450" value="0"', $output);
    }

    public function testSetupOptionsUpgradeShowsCurrentResourceId()
    {
        /** @var modSystemSetting $setting */
        $setting = $this->modx->getObject('modSystemSetting', ['key' => 'glossary.resid']);
        if (!$setting) {
            $this->markTestSkipped('The glossary.resid system setting does not exist.');
        }

        $output = $this->getSetupOptions(xPDOTransport::ACTION_UPGRADE);

        $this->assertStringContainsString('<h2>Upgrade Glossary</h2>', $output);
        $this->assertStringContainsString('value="' . htmlspecialchars($setting->get('value'), ENT_QUOTES, 'UTF-8') . '"', $output);
    }

    /**
     * Get the output of the setup options for a package action.
     *
     * @param string $action
     * @return string
     */
    private function getSetupOptions(string $action): string
    {
        $modx = $this->modx;
        $options = [xPDOTransport::PACKAGE_ACTION => $action];
        return include dirname(__DIR__, 2) . '/_build/setup.options.php';
    }
}
