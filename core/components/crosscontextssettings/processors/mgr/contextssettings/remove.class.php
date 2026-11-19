<?php
/**
 * Remove contexts setting
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

use TreehillStudio\CrossContextsSettings\Processors\ObjectProcessor;

class CrossContextsSettingsSettingsRemoveProcessor extends ObjectProcessor
{
    public $languageTopics = ['crosscontextssettings:default', 'setting'];
    public $permission = 'settings';
    public $objectType = 'setting';
    public $primaryKeyField = 'key';

    /**
     * {@inheritDoc}
     * @return bool|string
     */
    public function initialize()
    {
        $primaryKey = $this->getProperty($this->primaryKeyField, false);
        if (empty($primaryKey)) {
            return $this->modx->lexicon('setting_err_ns');
        }
        return true;
    }

    /**
     * {@inheritDoc}
     * @return array|string
     */
    public function process()
    {
        $props = $this->getProperties();
        $contexts = [];

        // Only remove the setting in the contexts managed (and shown) by CrossContextsSettings
        $contextSettings = $this->modx->getCollection('modContextSetting', [
            'key' => $this->getProperty($this->primaryKeyField),
            'context_key:!=' => 'mgr',
            'context_key:IN' => $this->crosscontextssettings->getOption('contexts_keys') ?: [''],
        ]);
        if (!$contextSettings) {
            return $this->failure($this->modx->lexicon('setting_err_nf'));
        }
        foreach ($contextSettings as $setting) {
            $result = $this->modx->runProcessor('context/setting/remove', [
                'context_key' => $setting->get('context_key'),
                'key' => $props['key'],
            ]);
            if ($result->isError()) {
                $response = $result->getAllErrors();
                $message = $response[0] ?? $this->modx->lexicon('setting_err_remove');
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, $message, '', 'CrossContextsSettings');
                $this->refreshContexts($contexts);
                return $this->failure($message);
            }
            $contexts[$setting->get('context_key')] = 1;
        }

        $this->refreshContexts($contexts);

        return $this->success();
    }
}

return 'CrossContextsSettingsSettingsRemoveProcessor';
