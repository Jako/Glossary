<?php
/**
 * Get list system settings
 *
 * @package glossary
 * @subpackage processors
 */

// Compatibility between 2.x/3.x
if (file_exists(MODX_PROCESSORS_PATH . 'system/settings/getlist.class.php')) {
    require_once MODX_PROCESSORS_PATH . 'system/settings/getlist.class.php';
} elseif (!class_exists('modSystemSettingsGetListProcessor')) {
    class_alias(\MODX\Revolution\Processors\System\Settings\GetList::class, \modSystemSettingsGetListProcessor::class);
}

class GlossarySystemSettingsGetlistProcessor extends modSystemSettingsGetListProcessor
{
    public $languageTopics = ['setting', 'namespace', 'glossary:setting'];
    public $objectType = 'setting';
    public $primaryKeyField = 'key';

    /**
     * {@inheritDoc}
     * @return array
     */
    public function prepareCriteria()
    {
        return ['namespace' => 'glossary'];
    }
}

return 'GlossarySystemSettingsGetlistProcessor';
