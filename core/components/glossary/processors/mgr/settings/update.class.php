<?php
/**
 * Update a system setting
 *
 * @package glossary
 * @subpackage processors
 */

// Compatibility between 2.x/3.x
if (file_exists(MODX_PROCESSORS_PATH . 'system/settings/update.class.php')) {
    require_once MODX_PROCESSORS_PATH . 'system/settings/update.class.php';
} elseif (!class_exists('modSystemSettingsUpdateProcessor')) {
    class_alias(\MODX\Revolution\Processors\System\Settings\Update::class, \modSystemSettingsUpdateProcessor::class);
}

class GlossarySystemSettingsUpdateProcessor extends modSystemSettingsUpdateProcessor
{
    public $checkSavePermission = false;
    public $languageTopics = array('setting', 'namespace', 'glossary:setting');

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function beforeSave()
    {
        $this->setProperty('namespace', 'glossary');
        $this->checkForBooleanValue();
        $this->checkCanSave();
        return parent::beforeSave();
    }

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function afterSave()
    {
        $this->updateTranslations($this->getProperties());
        $this->clearCache();
        return parent::afterSave();
    }

    /**
     * Verify the namespace passed is a valid namespace
     */
    protected function checkCanSave()
    {
        $key = $this->getProperty('key');
        if (strpos($key, 'glossary.') !== 0) {
            $this->addFieldError('key', $this->modx->lexicon('glossary.systemsetting_key_err_nv'));
        }
        if (!$this->modx->hasPermission('settings') && !$this->modx->hasPermission('glossary_settings')) {
            $this->addFieldError('usergroup', $this->modx->lexicon('glossary.systemsetting_usergroup_err_nv'));
        }
    }
}

return 'GlossarySystemSettingsUpdateProcessor';
