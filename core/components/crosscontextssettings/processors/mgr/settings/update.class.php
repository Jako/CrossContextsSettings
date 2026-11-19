<?php
/**
 * Update a system setting
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

// Compatibility between 2.x/3.x
if (file_exists(MODX_PROCESSORS_PATH . 'system/settings/update.class.php')) {
    require_once MODX_PROCESSORS_PATH . 'system/settings/update.class.php';
} elseif (!class_exists('modSystemSettingsUpdateProcessor')) {
    class_alias(\MODX\Revolution\Processors\System\Settings\Update::class, \modSystemSettingsUpdateProcessor::class);
}

/**
 * Class CrossContextsSettingsSystemSettingsUpdateProcessor
 */
class CrossContextsSettingsSystemSettingsUpdateProcessor extends modSystemSettingsUpdateProcessor
{
    public $checkSavePermission = false;
    public $languageTopics = ['setting', 'namespace', 'crosscontextssettings:setting', 'crosscontextssettings:default'];

    /**
     * Force the namespace before the properties are set to the object
     * {@inheritDoc}
     * @return bool
     */
    public function beforeSet()
    {
        $this->setProperty('namespace', 'crosscontextssettings');
        return parent::beforeSet();
    }

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function beforeSave()
    {
        $this->checkCanSave();
        return parent::beforeSave();
    }

    /**
     * Only settings with the crosscontextssettings prefix could be edited
     */
    protected function checkCanSave()
    {
        $key = (string)$this->getProperty('key');
        if (strpos($key, 'crosscontextssettings.') !== 0) {
            $this->addFieldError('key', $this->modx->lexicon('crosscontextssettings.systemsetting_key_err_nv'));
        }
    }
}

return 'CrossContextsSettingsSystemSettingsUpdateProcessor';
